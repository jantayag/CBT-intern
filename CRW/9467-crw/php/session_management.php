<?php
session_start();

$timeoutDuration = 300; 

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if (isset($_SESSION['last_activity'])) {
    $elapsedTime = time() - $_SESSION['last_activity'];
    if ($elapsedTime > $timeoutDuration) {
        session_unset();
        session_destroy();
        header("Location: ../9467-crw/index.php");
        exit();
    }
}

$_SESSION['last_activity'] = time();
?>
