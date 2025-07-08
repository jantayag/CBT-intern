<?php
include('php/db.php');

function getAssessmentQuestions($assessment_id) {
    global $conn;
    
    $sql = "SELECT q.id, q.question_text, q.difficulty, q.points, q.type, q.image_path 
            FROM assessment_questions aq 
            INNER JOIN questions q ON aq.question_id = q.id 
            WHERE aq.assessment_id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $assessment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getChoices($question_id, $question_type) {
    global $conn;
    
    if ($question_type === 'identification') {
        return [];
    }
    
    $sql = "SELECT c.id, c.text, c.is_answer
            FROM choices c 
            WHERE c.question_id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $question_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getAssessmentDetails($assessment_id) {
    global $conn;
    
    $sql = "SELECT title FROM assessments WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $assessment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

?>