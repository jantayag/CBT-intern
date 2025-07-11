<?php
session_start(); 
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');

function getNextClassId($conn) {
    $result = $conn->query("SELECT MAX(id) as max_id FROM classes");
    $row = $result->fetch_assoc();
    return ($row['max_id'] === null) ? 1 : $row['max_id'] + 1;
}

try {
    require_once '../db.php';
    
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
        throw new Exception('User not authenticated');
    }

    if ($_SESSION['user_type'] !== 'Faculty' && $_SESSION['user_type'] !== 'Admin') {
        throw new Exception('Unauthorized access. Only faculty members can create classes.');
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $required_fields = ['class_code', 'program', 'type', 'start_year', 'end_year'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("$field is required");
            }
        }

        $class_code = trim($_POST['class_code']);
        $program_id = intval($_POST['program']);
        $semester = intval($_POST['type']);
        $start_year = intval($_POST['start_year']);
        $end_year = intval($_POST['end_year']);
        $faculty_id = $_SESSION['user_id']; 
        
        if (!$faculty_id) {
            throw new Exception('Invalid faculty ID');
        }
        
        if ($end_year < $start_year) {
            throw new Exception('End year must be greater than or equal to start year');
        }

        $academic_year = $start_year . '-' . $end_year;

        $conn->begin_transaction();

        // Check if the class code already exists
        $check_stmt = $conn->prepare("SELECT id FROM classes WHERE class_code = ?");
        $check_stmt->bind_param("s", $class_code);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            throw new Exception('Class code already exists');
        }
        $check_stmt->close();

        // Get the next class ID
        $next_class_id = getNextClassId($conn);

        // Insert the new class
        $stmt = $conn->prepare("INSERT INTO classes (id, class_code, program_id, sem, faculty_id, AY) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("isiiss", $next_class_id, $class_code, $program_id, $semester, $faculty_id, $academic_year);
        
        if (!$stmt->execute()) {
            throw new Exception("Class creation failed: " . $stmt->error);
        }

        $stmt->close();

        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Class created successfully',
            'class_id' => $next_class_id,
            'debug' => [
                'faculty_id' => $faculty_id,
                'user_type' => $_SESSION['user_type']
            ]
        ]);
        
    } else {
        throw new Exception('Invalid request method');
    }

} catch (Exception $e) {
    if (isset($conn) && $conn->connect_errno === 0) {
        $conn->rollback();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'session_data' => $_SESSION
        ]
    ]);
} finally {
    if (isset($conn) && $conn->connect_errno === 0) {
        $conn->close();
    }
}
?>
