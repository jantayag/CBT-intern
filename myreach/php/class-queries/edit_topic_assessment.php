<?php
include('../db.php');

header('Content-Type: application/json');

try {
    $assessment_id = $_POST['assessment_id'] ?? null;
    $topic_id = $_POST['topic_id'] ?? null;
    
    $evaluation_start = $_POST['evaluation_start'] === '' ? null : $_POST['evaluation_start'];
    $evaluation_end = $_POST['evaluation_end'] === '' ? null : $_POST['evaluation_end'];
    $can_view = $_POST['can_view'] === 'True' ? 1 : 0;

    $sql = "UPDATE topic_assessments 
            SET evaluation_start = ?, evaluation_end = ?, can_view = ? 
            WHERE assessment_id = ? AND topic_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssiii', $evaluation_start, $evaluation_end, $can_view, $assessment_id, $topic_id);
    
    $result = $stmt->execute();

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update assessment']);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>
