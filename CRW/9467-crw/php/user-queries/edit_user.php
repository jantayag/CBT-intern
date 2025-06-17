<?php
include('../db.php');
header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn->begin_transaction();

    $userId = $_POST['user_id'];
    $first_name = htmlspecialchars(trim($_POST['first_name']));
    $last_name = htmlspecialchars(trim($_POST['last_name']));
    $user_type = htmlspecialchars(trim($_POST['user_type']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = htmlspecialchars(trim($_POST['password']));

    $stmt = $conn->prepare("UPDATE `users` SET  `password` = ?, `email` = ?, `first_name` = ?, `last_name` = ?, `user_type` = ? WHERE `id` = ?");
    $stmt->bind_param('sssssi',$password, $email, $first_name, $last_name,$user_type, $userId);

    if($stmt->execute()) {
        $conn->commit();
        echo json_encode([
            'success' => true,
            'user_id' => $userId,
            'message' => 'Changes made successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error updating user: '. $stmt->error
        ]);
    }
    exit;
}
?>