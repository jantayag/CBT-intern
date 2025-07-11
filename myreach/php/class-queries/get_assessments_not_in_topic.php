<?php
include('../db.php');
function getAssessmentsNotInTopic($topic_id) {
    global $conn;

    $sql = "SELECT a.id, a.title, a.date_created
            FROM assessments a
            WHERE a.id NOT IN (
                SELECT ta.assessment_id 
                FROM topic_assessments ta 
                WHERE ta.topic_id = ?
            )";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $topic_id);
$stmt->execute();
$result = $stmt->get_result();

$assessments = [];
while ($row = $result->fetch_assoc()) {
$assessments[] = $row;
}

$stmt->close();
    
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'assessments' => $assessments
]);

}

$topic_id = isset($_GET['topic_id']) ? $_GET['topic_id'] : '';
getAssessmentsNotInTopic($topic_id);
?>