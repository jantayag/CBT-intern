<?php
include('../db.php');

function getAssessmentPerformance($class_code) {
    global $conn;
    $sql = "SELECT a.id, a.title, 
                   COALESCE(AVG(ar.score), 0) AS average_score 
            FROM assessments a
            INNER JOIN topic_assessments ta ON a.id = ta.assessment_id
            INNER JOIN topics t ON ta.topic_id = t.id
            LEFT JOIN assessment_results ar ON a.id = ar.assessment_id
            WHERE t.class_code = ?
            GROUP BY a.id";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $class_code);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getQuestionPerformance($class_code) { 
    global $conn; 
    $sql = "SELECT q.id, 
                   q.question_text,  
                   SUM(CASE WHEN sr.is_correct = 1 THEN 1 ELSE 0 END) AS correct_count, 
                   COUNT(sr.id) AS total_attempts,
                   COUNT(DISTINCT sr.student_id) AS unique_students,
                   (SELECT COUNT(DISTINCT sc.student_id) 
                    FROM student_class sc 
                    WHERE sc.class_code = ?) AS total_class_students
            FROM questions q 
            INNER JOIN assessment_questions aq ON q.id = aq.question_id 
            INNER JOIN assessments a ON aq.assessment_id = a.id 
            INNER JOIN topic_assessments ta ON a.id = ta.assessment_id 
            INNER JOIN topics t ON ta.topic_id = t.id 
            LEFT JOIN student_responses sr ON q.id = sr.question_id AND sr.assessment_id = a.id 
            WHERE t.class_code = ? 
            GROUP BY q.id"; 
    $stmt = $conn->prepare($sql); 
    $stmt->bind_param("ss", $class_code, $class_code); 
    $stmt->execute(); 
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC); 
}

function getTopicEngagement($class_code) {
    global $conn;
    $sql = "SELECT t.topic, 
                   COUNT(ta.assessment_id) AS assessment_count
            FROM topics t
            LEFT JOIN topic_assessments ta ON t.id = ta.topic_id
            WHERE t.class_code = ?
            GROUP BY t.id";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $class_code);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getStudentParticipation($class_code) {
    global $conn;
    $sql = "SELECT COUNT(DISTINCT sc.student_id) AS student_count
            FROM student_class sc
            WHERE sc.class_code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $class_code);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['student_count'];
}

function getAssessmentParticipation($class_code) {
    global $conn;
    $sql = "SELECT 
                CASE 
                    WHEN participation_rate = 100 THEN 'Full Participation'
                    WHEN participation_rate >= 75 THEN '75-99%'
                    WHEN participation_rate >= 50 THEN '50-74%'
                    WHEN participation_rate >= 25 THEN '25-49%'
                    ELSE 'Below 25%'
                END as participation_level,
                COUNT(*) as assessment_count
            FROM (
                SELECT 
                    a.id,
                    (COUNT(DISTINCT ar.student_id) * 100.0 / 
                    (SELECT COUNT(*) FROM student_class WHERE class_code = ?)) as participation_rate
                FROM assessments a
                INNER JOIN topic_assessments ta ON a.id = ta.assessment_id
                INNER JOIN topics t ON ta.topic_id = t.id
                LEFT JOIN assessment_results ar ON a.id = ar.assessment_id
                WHERE t.class_code = ?
                GROUP BY a.id
            ) participation_data
            GROUP BY participation_level
            ORDER BY participation_level DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $class_code, $class_code);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getStudentPerformanceDistribution($class_code) {
    global $conn;
    $sql = "SELECT 
    CASE 
        WHEN average_score >= 90 THEN 'Excellent (90-100%)'
        WHEN average_score >= 80 THEN 'Very Good (80-89%)'
        WHEN average_score >= 70 THEN 'Good (70-79%)'
        WHEN average_score >= 60 THEN 'Fair (60-69%)'
        ELSE 'Needs Improvement (<60%)'
    END as performance_level,
    COUNT(*) as student_count
        FROM (
            SELECT 
                sc.student_id,
                COALESCE(AVG(ar.score) / assessment_total_scores.total_score * 100, 0) as average_score  
            FROM student_class sc
            LEFT JOIN assessment_results ar ON sc.student_id = ar.student_id
            INNER JOIN assessments a ON ar.assessment_id = a.id
            INNER JOIN topic_assessments ta ON a.id = ta.assessment_id
            INNER JOIN topics t ON ta.topic_id = t.id
            INNER JOIN (
                SELECT aq.assessment_id, SUM(q.points) AS total_score
                FROM assessment_questions aq
                INNER JOIN questions q ON aq.question_id = q.id
                GROUP BY aq.assessment_id
            ) AS assessment_total_scores ON a.id = assessment_total_scores.assessment_id
            WHERE sc.class_code = ? AND t.class_code = ?
            GROUP BY sc.student_id
        ) student_averages
        GROUP BY 
            CASE 
                WHEN average_score >= 90 THEN 'Excellent (90-100%)'
                WHEN average_score >= 80 THEN 'Very Good (80-89%)'
                WHEN average_score >= 70 THEN 'Good (70-79%)'
                WHEN average_score >= 60 THEN 'Fair (60-69%)'
                ELSE 'Needs Improvement (<60%)'
            END
        ORDER BY 
            CASE 
                WHEN average_score >= 90 THEN 1
                WHEN average_score >= 80 THEN 2
                WHEN average_score >= 70 THEN 3
                WHEN average_score >= 60 THEN 4
                ELSE 5
            END;
        ";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $class_code, $class_code);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

if (!isset($_GET['class_code'])) { 
    http_response_code(400); 
    echo json_encode(['error' => 'Class code is required.']); 
    exit(); 
} 

$class_code = $_GET['class_code']; 

$data = [ 
    'assessmentPerformance' => getAssessmentPerformance($class_code), 
    'questionPerformance' => getQuestionPerformance($class_code), 
    'topicEngagement' => getTopicEngagement($class_code), 
    'studentParticipation' => getStudentParticipation($class_code),
    'assessmentParticipation' => getAssessmentParticipation($class_code),
    'performanceDistribution' => getStudentPerformanceDistribution($class_code)
];

header('Content-Type: application/json'); 
echo json_encode($data); 
?>
