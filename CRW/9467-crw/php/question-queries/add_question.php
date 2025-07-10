<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');

try {
    require_once '../db.php';
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $e->getMessage()
    ]);
    exit;
}

function checkDuplicateQuestions($conn, $questions) {
    $duplicates = [];
    $stmt = $conn->prepare("SELECT question_text FROM questions WHERE question_text = ?");

    foreach ($questions as $question) {
        $stmt->bind_param("s", $question);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $duplicates[] = $question;
        }
    }

    return $duplicates;
}

function getNextQuestionId($conn) {
    $result = $conn->query("SELECT MAX(id) as max_id FROM questions");
    $row = $result->fetch_assoc();
    return ($row['max_id'] === null) ? 1 : $row['max_id'] + 1;
}

function getNextChoiceId($conn) {
    $result = $conn->query("SELECT MAX(id) as max_id FROM choices");
    $row = $result->fetch_assoc();
    return ($row['max_id'] === null) ? 1 : $row['max_id'] + 1;
}

function validateQuestionData($data, $isCsv = false) {
    if ($isCsv) return [];

    $errors = [];
    if (empty($data['question_text'])) {
        $errors[] = 'Question text is required';
    }
    if (empty($data['difficulty'])) {
        $errors[] = 'Difficulty is required';
    }
    if (empty($data['type'])) {
        $errors[] = 'Question type is required';
    }

    if (!empty($data['type'])) {
        switch ($data['type']) {
            case 'mc':
                if (empty($data['choices']) || !is_array($data['choices']) || count($data['choices']) < 2) {
                    $errors[] = 'At least 2 choices are required for multiple-choice questions';
                }
                if (!isset($data['correct_choice']) || !is_numeric($data['correct_choice'])) {
                    $errors[] = 'Correct choice must be selected';
                } elseif (!isset($data['choices'][$data['correct_choice']])) {
                    $errors[] = 'Correct choice index is out of range';
                }
                break;
            case 'alternate-response':
                if (empty($data['answer']) || !in_array($data['answer'], ['True', 'False'])) {
                    $errors[] = 'Answer must be True or False for alternate-response questions';
                }
                break;
            case 'identification':
                if (empty($data['identificationAnswer'])) {
                    $errors[] = 'Identification answer is required';
                }
                break;
            default:
                $errors[] = 'Unsupported question type';
        }
    }

    return $errors;
}

function addSingleQuestion($conn, $data, $imagePath = null, $isCsv = false) {
    $errors = validateQuestionData($data, $isCsv);
    if (!empty($errors)) {
        return [
            'success' => false,
            'message' => 'Validation errors: ' . implode(', ', $errors)
        ];
    }

    try {
        $conn->begin_transaction();

        $nextQuestionId = getNextQuestionId($conn);
        $questionText = $data['question_text'] ?? '';
        $difficulty = $data['difficulty'] ?? '';
        $points = 1;
        $type = $data['type'] ?? '';

        $stmt = $conn->prepare("INSERT INTO `questions` (`id`, `question_text`, `difficulty`, `points`, `type`, `image_path`) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ississ", $nextQuestionId, $questionText, $difficulty, $points, $type, $imagePath);

        if (!$stmt->execute()) {
            throw new Exception("Question insert failed: " . $stmt->error);
        }

        $nextChoiceId = getNextChoiceId($conn);
        switch ($type) {
            case 'mc':
                if (!empty($data['choices'])) {
                    $choiceStmt = $conn->prepare("INSERT INTO `choices` (`id`, `text`, `question_id`, `is_answer`) VALUES (?, ?, ?, ?)");
                    foreach ($data['choices'] as $index => $choice) {
                        $isAnswer = ($index == $data['correct_choice']) ? 'Y' : 'N';
                        $choiceStmt->bind_param("isis", $nextChoiceId, $choice, $nextQuestionId, $isAnswer);
                        $choiceStmt->execute();
                        $nextChoiceId++;
                    }
                    $choiceStmt->close();
                }
                break;
            case 'alternate-response':
                if (isset($data['answer'])) {
                    $choiceStmt = $conn->prepare("INSERT INTO `choices` (`id`, `text`, `question_id`, `is_answer`) VALUES (?, ?, ?, ?)");
                    $correctAnswer = $data['answer'];
                    $incorrectAnswer = ($correctAnswer === 'True') ? 'False' : 'True';

                    $isAnswer = 'Y';
                    $choiceStmt->bind_param("isis", $nextChoiceId, $correctAnswer, $nextQuestionId, $isAnswer);
                    $choiceStmt->execute();
                    $nextChoiceId++;

                    $isAnswer = 'N';
                    $choiceStmt->bind_param("isis", $nextChoiceId, $incorrectAnswer, $nextQuestionId, $isAnswer);
                    $choiceStmt->execute();
                    $choiceStmt->close();
                }
                break;
            case 'identification':
                if (isset($data['identificationAnswer'])) {
                    $choiceStmt = $conn->prepare("INSERT INTO `choices` (`id`, `text`, `question_id`, `is_answer`) VALUES (?, ?, ?, ?)");
                    $isAnswer = 'Y';
                    $answerText = $data['identificationAnswer'];
                    $choiceStmt->bind_param("isis", $nextChoiceId, $answerText, $nextQuestionId, $isAnswer);
                    $choiceStmt->execute();
                    $choiceStmt->close();
                }
                break;
        }

        $conn->commit();
        return [
            'success' => true,
            'message' => 'Question added successfully',
            'questionId' => $nextQuestionId
        ];

    } catch (Exception $e) {
        $conn->rollback();
        return [
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ];
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $response = ['success' => false, 'message' => '', 'results' => []];

   $imagePath = null;
if (isset($_FILES['question_image']) && $_FILES['question_image']['error'] === UPLOAD_ERR_OK) {
    $uploadDirRelativeToPhp = '../../../uploads/';  // From /php/question-queries/
    $uploadDirForDb = '../uploads/';

    if (!is_dir($uploadDirRelativeToPhp)) {
        mkdir($uploadDirRelativeToPhp, 0777, true);
    }

    $fileName = basename($_FILES['question_image']['name']);
    $extension = pathinfo($fileName, PATHINFO_EXTENSION);

    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array(strtolower($extension), $allowed)) {
        throw new Exception("Invalid image type. Allowed: JPG, JPEG, PNG, GIF.");
    }

    $destinationPath = $uploadDirRelativeToPhp . $fileName;

    if (!move_uploaded_file($_FILES['question_image']['tmp_name'], $destinationPath)) {
        throw new Exception("Image upload failed.");
    }

    $imagePath = $uploadDirForDb . $fileName; // This gets stored in DB
}
    if (isset($_FILES['csv-upload']) && $_FILES['csv-upload']['size'] > 0) {
        try {
            $file = fopen($_FILES['csv-upload']['tmp_name'], 'r');
            $headers = fgetcsv($file);
            $questions = [];
            $fileContent = [];
            while (($line = fgetcsv($file)) !== false) {
                if (count($line) < 5) continue;
                $questions[] = $line[0];
                $fileContent[] = $line;
            }
            if (!isset($_POST['force_upload']) || $_POST['force_upload'] !== 'true') {
                $duplicates = checkDuplicateQuestions($conn, $questions);
                if (!empty($duplicates)) {
                    echo json_encode([
                        'success' => false,
                        'duplicate' => true,
                        'message' => 'It seems that the file has similar questions already in the pool, would you like to proceed?',
                        'duplicates' => $duplicates
                    ]);
                    fclose($file);
                    exit;
                }
            }
            foreach ($fileContent as $line) {
                $questionData = [
                    'question_text' => $line[0],
                    'difficulty' => $line[1],
                    'points' => $line[2],
                    'type' => $line[3]
                ];
                $imagePath = isset($line[5]) ? $line[5] : null;
                switch ($line[3]) {
                    case 'mc':
                        $questionData['correct_choice'] = intval($line[4]);
                        $questionData['choices'] = array_slice($line, 6);
                        break;
                    case 'alternate-response':
                        $questionData['answer'] = $line[4];
                        break;
                    case 'identification':
                        $questionData['identificationAnswer'] = $line[4];
                        break;
                }
                $result = addSingleQuestion($conn, $questionData, $imagePath, true);
                $response['results'][] = $result;
                if ($result['success']) {
                    $response['success'] = true;
                }
            }
            fclose($file);
            $response['message'] = "Successfully added questions";
        } catch (Exception $e) {
            $response['message'] = 'Error processing CSV: ' . $e->getMessage();
        }
    } else {
        $questionData = $_POST;
        if (isset($_POST['choice'])) {
            $questionData['choices'] = $_POST['choice'];
            $questionData['correct_choice'] = $_POST['correctChoice'];
        }
        $response = addSingleQuestion($conn, $questionData, $imagePath);
    }
    echo json_encode($response);
    exit;
}
