<?php
include('../db.php');

function getStudentsNotInClass($classCode) {
    global $conn;

    $sql = "SELECT u.id, u.first_name, u.last_name, u.email, u.user_type
            FROM users u
            WHERE u.user_type = 'Student' AND u.id NOT IN (
                SELECT student_id
                FROM student_class
                WHERE class_code = ?
            )
            ORDER BY u.last_name, u.first_name";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $classCode);
    $stmt->execute();
    $result = $stmt->get_result();

    $students = [];
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }

    $stmt->close();
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'students' => $students
    ]);
}

$classCode = isset($_GET['class_code']) ? $_GET['class_code'] : '';
getStudentsNotInClass($classCode);
?>