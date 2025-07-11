<?php
session_start();
include('../db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_SESSION['user_id'] ?? die(json_encode(['success' => false, 'error' => 'Not logged in']));
    $assessment_id = $_POST['assessment_id'] ?? die(json_encode(['success' => false, 'error' => 'No assessment ID']));
    $answers = json_decode($_POST['answers'], true) ?? [];

    $sql = "DELETE FROM assessment_drafts WHERE student_id = ? AND assessment_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $student_id, $assessment_id);
    $stmt->execute();
    
    $sql = "INSERT INTO assessment_drafts (student_id, assessment_id, answers, last_updated) 
            VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $answers_json = json_encode($answers);
    $stmt->bind_param("iis", $student_id, $assessment_id, $answers_json);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to save draft']);
    }
    exit();
}

