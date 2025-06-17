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

function validateFormData($data) {
    $errors = [];
    
    if (empty($data['question_id'])) {
        $errors[] = 'Question ID is required';
    }
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $errors = validateFormData($_POST);
        if (!empty($errors)) {
            echo json_encode([
                'success' => false,
                'message' => 'Validation errors: ' . implode(', ', $errors)
            ]);
            exit;
        }

        $conn->begin_transaction();

        $questionId = $_POST['question_id'];
        $questionText = $_POST['question_text'];
        $difficulty = $_POST['difficulty'];
        $points = $_POST['points'];
        $type = $_POST['type'];

        // Update the question
        $stmt = $conn->prepare("UPDATE `questions` SET `question_text` = ?, `difficulty` = ?, `points` = ?, `type` = ? WHERE `id` = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("ssisi", $questionText, $difficulty, $points, $type, $questionId);
        
        if (!$stmt->execute()) {
            throw new Exception("Question update failed: " . $stmt->error);
        }

        $deleteStmt = $conn->prepare("DELETE FROM `choices` WHERE `question_id` = ?");
        $deleteStmt->bind_param("i", $questionId);
        $deleteStmt->execute();
        $deleteStmt->close();

        switch($type) {
            case 'mc':
                if (!empty($_POST['choice'])) {
                    $choiceStmt = $conn->prepare("INSERT INTO `choices` (`text`, `question_id`, `is_answer`) VALUES (?, ?, ?)");
                    foreach ($_POST['choice'] as $index => $choiceText) {
                        $isAnswer = ($index == $_POST['correctChoice']) ? 'Y' : 'N';
                        $choiceStmt->bind_param("sis", $choiceText, $questionId, $isAnswer);
                        $choiceStmt->execute();
                    }
                    $choiceStmt->close();
                }
                break;

            case 'alternate-response':
                if (isset($_POST['answer'])) {
                    $choiceStmt = $conn->prepare("INSERT INTO `choices` (`text`, `question_id`, `is_answer`) VALUES (?, ?, ?)");
                    $correctAnswer = $_POST['answer'];
                    $incorrectAnswer = ($correctAnswer === 'True') ? 'False' : 'True';
                    
                    $isAnswer = 'Y';
                    $choiceStmt->bind_param("sis", $correctAnswer, $questionId, $isAnswer);
                    $choiceStmt->execute();
                    
                    $isAnswer = 'N';
                    $choiceStmt->bind_param("sis", $incorrectAnswer, $questionId, $isAnswer);
                    $choiceStmt->execute();
                    
                    $choiceStmt->close();
                }
                break;

            case 'identification':
                if (isset($_POST['identificationAnswer'])) {
                    $choiceStmt = $conn->prepare("INSERT INTO `choices` (`text`, `question_id`, `is_answer`) VALUES (?, ?, 'Y')");
                    $choiceStmt->bind_param("si", $_POST['identificationAnswer'], $questionId);
                    $choiceStmt->execute();
                    $choiceStmt->close();
                }
                break;
        }

        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Question updated successfully',
            'questionId' => $questionId
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
        if (isset($conn)) {
            $conn->close();
        }
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?>