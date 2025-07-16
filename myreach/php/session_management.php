<?php
session_start();

require_once __DIR__ . '/db.php'; 

function logAction($conn, $userId, $action, $details = '') {
    $stmt = $conn->prepare("INSERT INTO logs (user_id, action, details) VALUES (?, ?, ?)");
    $stmt->bind_param('iss', $userId, $action, $details);
    $stmt->execute();
}

$timeoutDuration = 300;

if (!isset($_SESSION['user_id'])) {
    header("Location: /CBT/myreach/index.php");
    exit();
}

if (isset($_SESSION['last_activity'])) {
    $elapsedTime = time() - $_SESSION['last_activity'];
    if ($elapsedTime > $timeoutDuration) {
        if (isset($_SESSION['user_id']) && strtolower($_SESSION['user_type']) === 'student') {
            logAction($conn, $_SESSION['user_id'], 'Logout', 'Session timed out due to inactivity');
        }

        session_unset();
        session_destroy();
        header("Location: /CBT/myreach/index.php");
        exit();
    }
}

$_SESSION['last_activity'] = time();
?>
