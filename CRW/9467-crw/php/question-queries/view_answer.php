<?php
header('Content-Type: application/json');
require_once '../db.php';
try {
    if (isset($_GET['question_id'])) {
        $question_id = intval($_GET['question_id']);
        
        $stmt = $conn->prepare("
            SELECT q.type, q.question_text, c.text, c.is_answer 
            FROM questions q 
            LEFT JOIN choices c ON q.id = c.question_id 
            WHERE q.id = ?
        ");
        
        $stmt->bind_param("i", $question_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $answers = [];
        $questionType = '';
        $questionText = '';
        
        while ($row = $result->fetch_assoc()) {
            $questionType = $row['type'];
            $questionText = htmlspecialchars($row['question_text']);
            if ($row['text'] !== null) {
                $answers[] = [
                    'text' => htmlspecialchars($row['text']),
                    'is_answer' => $row['is_answer']
                ];
            }
        }
        
        $response = [
            'success' => true,
            'type' => $questionType,
            'question_text' => $questionText,
            'answers' => $answers
        ];
        
        echo json_encode($response);
        exit; 
    } else {
        throw new Exception('Question ID not provided');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}