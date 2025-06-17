<?php
include('../db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $topic_id = $_POST['topic_id'];
    $assessmentIds = explode(',', $_POST['assessment_ids']);

    try {
        $conn->begin_transaction();

        foreach ($assessmentIds as $assessmentId) {
            $sql = "INSERT INTO topic_assessments (topic_id, assessment_id) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $topic_id, $assessmentId);
            $stmt->execute();
        }

        $conn->commit();

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Assessment/s added to the topic successfully.']);
    } catch (Exception $e) {
        $conn->rollback();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'You did not select an assessment.']);
    }
}
?>