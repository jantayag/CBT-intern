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

function validateQuestionData($data) {
    $errors = [];
    
    if (empty($data['question_text'])) {
        $errors[] = 'Question text is required';
    }
    if (empty($data['difficulty'])) {
        $errors[] = 'Difficulty is required';
    }
    if (empty($data['points'])) {
        $errors[] = 'Points are required';
    }
    if (empty($data['type'])) {
        $errors[] = 'Question type is required';
    }
    
    return $errors;
}

function addSingleQuestion($conn, $data) {
    $errors = validateQuestionData($data);
    if (!empty($errors)) {
        return [
            'success' => false,
            'message' => 'Validation errors: ' . implode(', ', $errors)
        ];
    }

    try {
        $conn->begin_transaction();

        $nextQuestionId = getNextQuestionId($conn);
        $questionText = $data['question_text'];
        $difficulty = $data['difficulty'];
        $points = $data['points'];
        $type = $data['type'];

        $stmt = $conn->prepare("INSERT INTO `questions` (`id`, `question_text`, `difficulty`, `points`, `type`) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issis", $nextQuestionId, $questionText, $difficulty, $points, $type);
        
        if (!$stmt->execute()) {
            throw new Exception("Question insert failed: " . $stmt->error);
        }

        $nextChoiceId = getNextChoiceId($conn);
        
        switch($type) {
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
                if (isset($data['answer'])) {
                    $choiceStmt = $conn->prepare("INSERT INTO `choices` (`id`, `text`, `question_id`, `is_answer`) VALUES (?, ?, ?, ?)");
                    $isAnswer = 'Y';
                    $answerText = $data['answer'];
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

    if(isset($_FILES['csv-upload']) && $_FILES['csv-upload']['size'] > 0) {
        try {
            $file = fopen($_FILES['csv-upload']['tmp_name'], 'r');
            $headers = fgetcsv($file);
            
            $questions = [];
            $fileContent = [];
            
            while(($line = fgetcsv($file)) !== FALSE) {
                if(count($line) < 5) continue;
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

            foreach($fileContent as $line) {
                $questionData = [
                    'question_text' => $line[0],
                    'difficulty' => $line[1],
                    'points' => $line[2],
                    'type' => $line[3]
                ];

                switch($line[3]) {
                    case 'mc':
                        $questionData['correct_choice'] = $line[4];
                        $questionData['choices'] = array_slice($line, 5);
                        break;
                        
                    case 'alternate-response':
                        $questionData['answer'] = $line[4];
                        break;
                        
                    case 'identification':
                        $questionData['answer'] = $line[4];
                        break;
                }
                
                $result = addSingleQuestion($conn, $questionData);
                $response['results'][] = $result;
                
                if($result['success']) {
                    $response['success'] = true;
                }
            }
            
            fclose($file);
            $successCount = count(array_filter($response['results'], function($r) { return $r['success']; }));
            $totalCount = count($response['results']);
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
        $response = addSingleQuestion($conn, $questionData);
    }
    
    echo json_encode($response);
    exit;
}
?>