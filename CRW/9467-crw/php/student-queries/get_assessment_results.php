<?php
$student_id = $_SESSION['user_id'];
if (!isset($student_id)) {
    header("Location: index.php");
    exit();
}
include('php/student-queries/get_student_assessment_details.php');
$topic_id = $_GET['topic_id'] ?? 1;
$assessment_id = $_GET['assessment_id'] ?? 1;
$assessment = getAssessmentDetails($assessment_id);
if (!$assessment) {
    die("Assessment not found.");
}
$questions = getAssessmentQuestions($assessment_id);

function getIdentificationAns($question_id) {
    global $conn;
    
    $sql = "SELECT text FROM choices WHERE question_id = ? AND is_answer = 'Y'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $question_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $row = $result->fetch_assoc();
    return $row ? $row['text'] : '';
}

function getStudentResponses($student_id, $assessment_id) {
    global $conn;
    $sql = "SELECT question_id, answer_text, is_correct 
            FROM student_responses 
            WHERE student_id = ? AND assessment_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $student_id, $assessment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $responses = [];
    while ($row = $result->fetch_assoc()) {
        $responses[$row['question_id']] = [
            'answer_text' => $row['answer_text'],
            'is_correct' => $row['is_correct']
        ];
    }
    return $responses;
}

function getStudentScore($student_id, $assessment_id) {
    global $conn;
    $sql = "SELECT score FROM assessment_results 
            WHERE student_id = ? AND assessment_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $student_id, $assessment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    return $row ? $row['score'] : 0; // return 0 or null if not found
}


function getTotalScore($assessment_id) {
    global $conn;
    $sql = "SELECT SUM(q.points) as total_points 
            FROM assessment_questions aq 
            JOIN questions q ON aq.question_id = q.id 
            WHERE aq.assessment_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $assessment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc()['total_points'];
}

$student_responses = getStudentResponses($student_id, $assessment_id);
$score = 0;
foreach ($questions as $q) {
    $qid = $q['id'];
    $is_correct = $student_responses[$qid]['is_correct'] ?? 0;
    if ((int)$is_correct === 1) {
        $points = getQuestionPoints($qid, $assessment_id);
        $score += (int)$points;
    }
}
$total_score = getTotalScore($assessment_id);
?>