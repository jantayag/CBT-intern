<?php
session_start(); 
include('../db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['class_id'])) {
    $class_id = intval($_POST['class_id']);
    $faculty_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("SELECT id FROM classes WHERE id = ? AND faculty_id = ?");
        $stmt->bind_param("ii", $class_id, $faculty_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Class not found or unauthorized']);
            exit();
        }

        $delete_stmt = $conn->prepare("DELETE FROM classes WHERE id = ? AND faculty_id = ?");
        $delete_stmt->bind_param("ii", $class_id, $faculty_id);
        $delete_stmt->execute();

        $conn->commit();

        echo json_encode(['success' => true, 'message' => 'Class deleted successfully']);
    } catch (Exception $e) {
        $conn->rollback();
        error_log('Error in del_class.php: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error deleting class: ' . $e->getMessage()]);
    } finally {
        if (isset($stmt)) $stmt->close();
        if (isset($delete_stmt)) $delete_stmt->close();
        $conn->close();
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}
