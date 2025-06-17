<?php
include('../db.php');

$assessment_id = $_GET['assessment_id'] ?? null;
$topic_id = $_GET['topic_id'] ?? null;

if (!$assessment_id || !$topic_id) {
    echo json_encode(['error' => 'Missing assessment or topic ID']);
    exit;
}

$sql = "SELECT evaluation_end FROM topic_assessments 
        WHERE assessment_id = ? AND topic_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $assessment_id, $topic_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $end_time = strtotime($row['evaluation_end']);
    $current_time = time();
    $time_remaining = max(0, $end_time - $current_time);
    
    echo json_encode([
        'time_remaining' => $time_remaining,
        'evaluation_end' => $row['evaluation_end']
    ]);
} else {
    echo json_encode(['error' => 'No assessment time found']);
}
$stmt->close();
$conn->close();
?>