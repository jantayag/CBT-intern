<?php
include('../db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assessment_id'])) {
    $assessment_id = intval($_POST['assessment_id']);
    $conn->begin_transaction();
    
    try {
        $delete_assessment_questions = "DELETE FROM assessment_questions WHERE assessment_id = ?";
        $stmt1 = $conn->prepare($delete_assessment_questions);
        $stmt1->bind_param("i", $assessment_id);
        $stmt1->execute();

        $delete_assessment = "DELETE FROM assessments WHERE id = ?";
        $stmt2 = $conn->prepare($delete_assessment);
        $stmt2->bind_param("i", $assessment_id);
        $stmt2->execute();

        $delete_topic_assessment = "DELETE FROM topic_assessments WHERE assessment_id = ?";
        $stmt3 = $conn->prepare($delete_topic_assessment);
        $stmt3->bind_param("i", $assessment_id);
        $stmt3->execute();

        $delete_student_response = "DELETE FROM student_responses WHERE assessment_id = ?";
        $stmt4 = $conn->prepare($delete_student_response);
        $stmt4->bind_param("i", $assessment_id);
        $stmt4->execute();

        $delete_assessment_result = "DELETE FROM assessment_results WHERE assessment_id = ?";
        $stmt5 = $conn->prepare($delete_assessment_result);
        $stmt5->bind_param("i", $assessment_id);
        $stmt5->execute();


        if ($stmt2->affected_rows > 0) {
            $conn->commit();
            $response = [
                'success' => true,
                'message' => 'Assessment deleted successfully.'
            ];
        } else {
            throw new Exception('Assessment not found or could not be deleted.');
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