<?php
include('../db.php');
header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn->begin_transaction();

    $assessment_id = $_POST['assessment_id'];
    $title = htmlspecialchars(trim($_POST['title']));

    $stmt = $conn->prepare("UPDATE `assessments` SET  `title` = ? WHERE `id` = ?");
    $stmt->bind_param('si',$title, $assessment_id);

    if($stmt->execute()) {
        $conn->commit();
        echo json_encode([
            'success' => true,
            'user_id' => $assessment_id,
            'message' => 'Changes made successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error updating assessment: '. $stmt->error
        ]);
    }
    exit;
}
?>