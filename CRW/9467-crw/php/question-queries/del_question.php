<?php
include('../db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['question_id'])) {
    $question_id = intval($_POST['question_id']);
    
    $conn->begin_transaction();
    
    try {
        $delete_choices = $conn->prepare("DELETE FROM choices WHERE question_id = ?");
        $delete_choices->bind_param("i", $question_id);
        $delete_choices->execute();
        
        $delete_question = $conn->prepare("DELETE FROM questions WHERE id = ?");
        $delete_question->bind_param("i", $question_id);
        $delete_question->execute();
        
        $conn->commit();
        
        echo json_encode(['success' => true, 'message' => 'Question deleted successfully']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Error deleting question: ' . $e->getMessage()]);
    }
    
    $delete_choices->close();
    $delete_question->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>