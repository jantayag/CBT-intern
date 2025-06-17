<?php
include('../db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id'])) {
    $student_id = intval($_POST['student_id']);

    $conn->begin_transaction();

    try {
        $delete_student_class = $conn->prepare("DELETE FROM student_class WHERE student_id = ?");
        $delete_student_class->bind_param("i", $student_id);
        $delete_student_class->execute();
        $delete_student_class->close();

        $conn->commit();

        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Student removed from class successfully']);
    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error removing student from class: ' . $e->getMessage()]);
    }

    $conn->close();
} else {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>