<?php
include('../db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assessment_id'])) {
    $assessment_id = intval($_POST['assessment_id']);
    $topic_id = intval($_POST['topic_id']);

    $conn->begin_transaction();
    
    try {
        $sql = "UPDATE topic_assessments SET is_published = 0 WHERE topic_id = ? AND assessment_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $topic_id, $assessment_id);  
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $conn->commit();
            $response = [
                'success' => true,
                'message' => 'Assessment unpublished successfully.',
                'published' => false
            ];
        } else {
            throw new Exception('Assessment not found or could not be unpublished.');
        }
    } catch (Exception $e) {
        $conn->rollback();
        $response = [
            'success' => false,
            'message' => 'Error unpublishing assessment: ' . $e->getMessage()
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