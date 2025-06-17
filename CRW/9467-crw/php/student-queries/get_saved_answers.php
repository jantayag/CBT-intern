<?php
session_start();
include('../db.php');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $student_id = $_SESSION['user_id'] ?? die(json_encode(['success' => false, 'error' => 'Not logged in']));
    $assessment_id = $_GET['assessment_id'] ?? die(json_encode(['success' => false, 'error' => 'No assessment ID']));
    
    $sql = "SELECT answers FROM assessment_drafts 
            WHERE student_id = ? AND assessment_id = ? 
            ORDER BY last_updated DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $student_id, $assessment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode(['success' => true, 'answers' => json_decode($row['answers'], true)]);
    } else {
        echo json_encode(['success' => true, 'answers' => null]);
    }
    exit();
}
?>