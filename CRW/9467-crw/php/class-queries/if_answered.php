<?php
include('../db.php');

$user_id = $_GET['user_id'];
$assessment_id = $_GET['assessment_id'];
$topic_id = $_GET['topic_id'];

$answered_sql = $conn->prepare("SELECT COUNT(*) AS answered_count FROM student_responses WHERE student_id = ? AND assessment_id = ? AND topic_id = ?");
$answered_sql->bind_param('iii', $user_id, $assessment_id, $topic_id);
$answered_sql->execute();
$answered_result = $answered_sql->get_result();
$answered_row = $answered_result->fetch_assoc();
$already_answered = $answered_row['answered_count'] > 0 ? true : false;

$published_sql = $conn->prepare("SELECT is_published FROM topic_assessments WHERE assessment_id = ? AND topic_id = ?");
$published_sql->bind_param('ii', $assessment_id, $topic_id);
$published_sql->execute();
$published_result = $published_sql->get_result();
$published_row = $published_result->fetch_assoc();
$is_published = $published_row['is_published'] ? true : false;

header('Content-Type: application/json');
echo json_encode([
    "already_answered" => $already_answered,
    "is_published" => $is_published
]);
?>
