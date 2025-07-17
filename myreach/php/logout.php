<?php
session_start();
require_once 'db.php';

function logAction($conn, $userId, $action, $details = '') {
    $stmt = $conn->prepare("INSERT INTO logs (user_id, action, details) VALUES (?, ?, ?)");
    $stmt->bind_param('iss', $userId, $action, $details);
    $stmt->execute();
}

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];

    $updateStmt = $conn->prepare("UPDATE users SET is_logged_in = 0 WHERE id = ?");
    $updateStmt->bind_param("i", $userId);
    $updateStmt->execute();

    if (strtolower($_SESSION['user_type']) === 'student') {
        logAction($conn, $userId, 'Logout', 'Student logged out');
    }
}


$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"], $params["secure"], $params["httponly"]
    );
}

session_unset();
session_destroy();

header("Location: ../index.php");
exit();
?>
