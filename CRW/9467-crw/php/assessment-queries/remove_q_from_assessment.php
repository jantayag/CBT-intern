<?php
include('../db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assessment_id']) && isset($_POST['question_id'])) {
    $assessment_id = intval($_POST['assessment_id']);
    $question_id = intval($_POST['question_id']);
    $conn->begin_transaction();
    
    try {
        $delete_assessment_questions = "DELETE FROM assessment_questions WHERE assessment_id = ? AND question_id = ?";
        $stmt1 = $conn->prepare($delete_assessment_questions);
        $stmt1->bind_param("ii", $assessment_id, $question_id);
        $stmt1->execute();

        if ($stmt1->affected_rows > 0) {
            $conn->commit();
            $response = [
                'success' => true,
                'message' => 'Question removed successfully.'
            ];
        } else {
            throw new Exception('Question not found or could not be deleted.');
        }

    } catch (Exception $e) {
        $conn->rollback();
        $response = [
            'success' => false,
            'message' => 'Error deleting assessment: ' . $e->getMessage()
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
}
?>