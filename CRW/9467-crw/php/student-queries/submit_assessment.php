<?php
include('../db.php');

function getNextId($table, $id_column) {
    global $conn;
    $sql = "SELECT MAX($id_column) AS max_id FROM $table";
    $result = $conn->query($sql);

    if ($result) {
        $row = $result->fetch_assoc();
        return $row['max_id'] ? $row['max_id'] + 1 : 1; 
    }
    return 1; 
}

function answerAssessment($student_id, $assessment_id, $topic_id, $question_id, $answer_text, $is_correct) {
    global $conn;
    $next_id = getNextId('student_responses', 'id');
    $sql = "INSERT INTO student_responses (id, student_id, assessment_id, topic_id, question_id, answer_text, is_correct) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiiiss", $next_id, $student_id, $assessment_id, $topic_id, $question_id, $answer_text, $is_correct);
    
    return $stmt->execute();
}

function checkAnswer($question_id, $answer_text, $question_type) {
    global $conn;
    
    if ($question_type === 'identification') {
        $sql = "SELECT text FROM choices WHERE question_id = ? AND is_answer = 'Y'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $question_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $submitted_answer = strval(trim($answer_text));
        $correct_answer = strval(trim($row['text']));
        return strtolower($submitted_answer) === strtolower($correct_answer);
    } 
    else if ($question_type === 'alternate-response') {
        $sql = "SELECT text FROM choices WHERE question_id = ? AND is_answer = 'Y'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $question_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $answer_text === $row['text'];
    }
    else if ($question_type === 'mc') {
        $sql = "SELECT is_answer FROM choices WHERE id = ? AND question_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $answer_text, $question_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['is_answer'] === 'Y';
    }
    
    return false;
}

function computeAssessment($student_id, $assessment_id, $topic_id, $answers) {
    global $conn;
    $sql = "SELECT q.id, q.points, q.type 
            FROM assessment_questions aq 
            INNER JOIN questions q ON aq.question_id = q.id 
            WHERE aq.assessment_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $assessment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $questions = $result->fetch_all(MYSQLI_ASSOC);
    
    $total_score = 0;
    
    foreach ($questions as $question) {
        $question_id = $question['id'];
        if (isset($answers[$question_id])) {
            $answer_text = $answers[$question_id];
            if ($question['type'] === 'identification') {
                $answer_text = strval(trim($answer_text));
            }
            
            $is_correct = checkAnswer($question_id, $answer_text, $question['type']);
            answerAssessment($student_id, $assessment_id, $topic_id, $question_id, $answer_text, $is_correct);
            
            if ($is_correct) {
                $total_score += $question['points'];
            }
        }
    }
    
    $next_id = getNextId('assessment_results', 'id');
    $sql = "INSERT INTO assessment_results (id, student_id, assessment_id, topic_id, score) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiii", $next_id, $student_id, $assessment_id, $topic_id, $total_score);
    $stmt->execute();
    
    return $total_score;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    $student_id = $_SESSION['user_id'] ?? die("Student not logged in");
    $assessment_id = $_POST['assessment_id'] ?? die("Assessment ID not provided");
    $topic_id = $_POST['topic_id'] ?? die("Topic ID not provided");
    $answers = $_POST['answers'] ?? [];
    
    $score = computeAssessment($student_id, $assessment_id, $topic_id, $answers);
    
    header("Location: ../../topic_assessments.php?topic_id=$topic_id");
    exit();
}
?>