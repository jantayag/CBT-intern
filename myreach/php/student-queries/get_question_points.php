<?php
include('php/db.php');

function getQuestionPoints($question_id, $assessment_id) {
    global $conn;
    
    $sql = "SELECT q.points 
            FROM assessment_questions aq 
            JOIN questions q ON aq.question_id = q.id 
            WHERE aq.assessment_id = ? AND q.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $assessment_id, $question_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $row = $result->fetch_assoc();
    return $row ? $row['points'] : 0;
}
?>