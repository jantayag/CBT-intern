<?php
include('../db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $assessment_id = $_POST['assessment_id'] ?? die(json_encode(['success' => false, 'error' => 'No assessment ID']));
    $topic_id = $_POST['topic_id'] ?? die(json_encode(['success' => false, 'error' => 'No topic ID']));
    
    $sql = "SELECT COUNT(*) as active_count 
            FROM assessment_drafts ad 
            WHERE ad.assessment_id = ? 
            AND ad.last_updated >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
            AND EXISTS (
                SELECT 1 
                FROM topic_assessments ta 
                WHERE ta.assessment_id = ad.assessment_id 
                AND ta.topic_id = ?
                AND ta.is_published = 1
            )";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $assessment_id, $topic_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'has_active_users' => $row['active_count'] > 0
    ]);
    exit();
}
?>