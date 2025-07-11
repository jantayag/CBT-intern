<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');

try {
    require_once '../db.php';

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
        throw new Exception('User not authenticated');
    }

    if ($_SESSION['user_type'] !== 'Faculty' && $_SESSION['user_type'] !== 'Admin') {
        throw new Exception('Unauthorized access. Only faculty members can edit classes.');
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        validatePostData();

        $class_id = intval($_POST['class_id']);
        $class_code = trim($_POST['class_code']);
        $program_id = intval($_POST['program']);
        $semester = intval($_POST['type']);
        $start_year = intval($_POST['start_year']);
        $end_year = intval($_POST['end_year']);
        $faculty_id = $_SESSION['user_id'];

        if ($end_year < $start_year) {
            throw new Exception('End year must be greater than or equal to start year');
        }

        $academic_year = $start_year . '-' . $end_year;

        $conn->begin_transaction();

        checkClassOwnership($conn, $class_id, $faculty_id);
        checkUniqueClassCode($conn, $class_code, $class_id);
        updateClass($conn, $class_code, $program_id, $semester, $academic_year, $class_id, $faculty_id);

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Class updated successfully']);
    } else if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
        $class_id = intval($_GET['id']);
        getClassData($conn, $class_id);
    } else {
        throw new Exception('Invalid request method');
    }
} catch (Exception $e) {
    if (isset($conn) && $conn->connect_errno === 0) {
        $conn->rollback();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($conn) && $conn->connect_errno === 0) {
        $conn->close();
    }
}

function validatePostData() {
    $required_fields = ['class_id', 'class_code', 'program', 'type', 'start_year', 'end_year'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("$field is required");
        }
    }
}

function checkClassOwnership($conn, $class_id, $faculty_id) {
    $check_stmt = $conn->prepare("SELECT faculty_id FROM classes WHERE id = ?");
    $check_stmt->bind_param("i", $class_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Class not found');
    }

    $class_data = $result->fetch_assoc();
    if ($class_data['faculty_id'] !== $faculty_id && $_SESSION['user_type'] !== 'Admin') {
        throw new Exception('Unauthorized to edit this class');
    }

    $check_stmt->close();
}

function checkUniqueClassCode($conn, $class_code, $class_id) {
    $code_check_stmt = $conn->prepare("SELECT id FROM classes WHERE class_code = ? AND id != ?");
    $code_check_stmt->bind_param("si", $class_code, $class_id);
    $code_check_stmt->execute();

    if ($code_check_stmt->get_result()->num_rows > 0) {
        throw new Exception('Class code already exists');
    }
    $code_check_stmt->close();
}

function updateClass($conn, $class_code, $program_id, $semester, $academic_year, $class_id, $faculty_id) {
    $update_stmt = $conn->prepare("UPDATE classes SET class_code = ?, program_id = ?, sem = ?, AY = ? WHERE id = ? AND faculty_id = ?");
    if (!$update_stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $update_stmt->bind_param("siisii", $class_code, $program_id, $semester, $academic_year, $class_id, $faculty_id);

    if (!$update_stmt->execute()) {
        throw new Exception("Class update failed: " . $update_stmt->error);
    }

    if ($update_stmt->affected_rows === 0) {
        throw new Exception("No changes were made to the class");
    }

    $update_stmt->close();
}

function getClassData($conn, $class_id) {
    $faculty_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("
        SELECT c.*, p.id as program_id, p.name as program_name 
        FROM classes c 
        LEFT JOIN programs p ON c.program_id = p.id 
        WHERE c.id = ? AND (c.faculty_id = ? OR ? = 'Admin')
    ");

    $stmt->bind_param("iis", $class_id, $faculty_id, $_SESSION['user_type']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Class not found or unauthorized access');
    }

    $class_data = $result->fetch_assoc();
    $ay_parts = explode('-', $class_data['AY']);
    $class_data['start_year'] = $ay_parts[0];
    $class_data['end_year'] = $ay_parts[1];

    echo json_encode(['success' => true, 'data' => $class_data]);
}
?>
