<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_type'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'User not authenticated'
    ]);
    exit();
}

echo json_encode([
    'success' => true,
    'user_type' => $_SESSION['user_type']
]);