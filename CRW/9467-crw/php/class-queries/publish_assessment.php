<?php
include('../db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assessment_id']) && isset($_POST['topic_id'])) {
    $assessment_id = intval($_POST['assessment_id']);
    $topic_id = intval($_POST['topic_id']);

    $checkEval = "SELECT evaluation_start, evaluation_end FROM topic_assessments WHERE topic_id = ? AND assessment_id = ?";
    $stmtCheck = $conn->prepare($checkEval);
    $stmtCheck->bind_param('ii', $topic_id, $assessment_id);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();
    
    if ($row = $resultCheck->fetch_assoc()) {
        if (!$row['evaluation_start'] || !$row['evaluation_end']) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Cannot publish. Evaluation start or end date is not set.'
            ]);
            exit;
        }
    }

    $conn->begin_transaction();
    
    try {
        $sql = "UPDATE topic_assessments SET is_published = 1 WHERE topic_id = ? AND assessment_id = ?"; 
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $topic_id, $assessment_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $conn->commit();
            $response = [
                'success' => true,
                'message' => 'Assessment published successfully.',
                'published' => true
            ];
        } else {
            throw new Exception('Assessment not found or could not be published.');
        }
    } catch (Exception $e) {
        $conn->rollback();
        $response = [
            'success' => false,
            'message' => 'Error publishing assessment: ' . $e->getMessage()
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