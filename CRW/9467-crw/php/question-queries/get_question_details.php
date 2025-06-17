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

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['question_id'])) {
    try {
        $questionId = $_GET['question_id'];
        
        // Get question details
        $stmt = $conn->prepare("SELECT * FROM questions WHERE id = ?");
        $stmt->bind_param("i", $questionId);
        $stmt->execute();
        $question = $stmt->get_result()->fetch_assoc();
        
        if (!$question) {
            throw new Exception("Question not found");
        }
        
        // Get answers/choices
        $choiceStmt = $conn->prepare("SELECT * FROM choices WHERE question_id = ?");
        $choiceStmt->bind_param("i", $questionId);
        $choiceStmt->execute();
        $answers = $choiceStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        echo json_encode([
            'success' => true,
            'question' => $question,
            'answers' => $answers
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
        if (isset($choiceStmt)) {
            $choiceStmt->close();
        }
        if (isset($conn)) {
            $conn->close();
        }
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request'
    ]);
}
?>