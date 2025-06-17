<?php
include('../db.php');
header('Content-Type: application/json');

if (isset($_GET['assessment_id'])) {
    $assessment_id = intval($_GET['assessment_id']);
    $stmt = $conn->prepare("SELECT id, title FROM assessments WHERE id = ?");
    $stmt->bind_param("i", $assessment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $assessment = $result->fetch_assoc();
        echo json_encode(['success' => true, 'assessment' => $assessment]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Assessment not found.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>
