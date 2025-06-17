<?php
include('../db.php');
function getNextAssessmentId($conn) {
    $result = $conn->query("SELECT MAX(id) as max_id FROM assessments");
    $row = $result->fetch_assoc();
    return ($row['max_id'] === null) ? 1 : $row['max_id'] + 1;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title =  htmlspecialchars($_POST['title']);
    $date_created = date('Y-m-d');
    $assessment_id = getNextAssessmentId($conn);

    $sql = "INSERT INTO assessments (id, title, date_created)
            VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $assessment_id, $title, $date_created);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'assessment_id' => $assessment_id,
            'message' => 'Assessment created successfully! Click "View" button to add questions.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error creating assessment: ' . $stmt->error
        ]);
    }
    exit;
}
?>
