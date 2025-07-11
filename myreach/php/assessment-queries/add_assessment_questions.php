<?php
include('../db.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assessment_id = $_POST['assessment_id'];
    $selected_questions = isset($_POST['selected_questions']) ? $_POST['selected_questions'] : [];

    if (empty($selected_questions)) {
        echo json_encode([
            'success' => true,
            'message' => 'Assessment created without adding any questions.'
        ]);
        exit;
    }
    $stmt = $conn->prepare("INSERT INTO assessment_questions (assessment_id, question_id) VALUES (?, ?)");

    foreach ($selected_questions as $question_id) {
        $stmt->bind_param("ii", $assessment_id, $question_id);
        $stmt->execute();
    }

    echo json_encode([
        'success' => true,
        'message' => 'Questions added to the assessment successfully'
    ]);
    exit;
}
?>
