<?php
include('../db.php');

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);
    $conn->begin_transaction();

    try {
        $delete_user = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($delete_user);
        $stmt->bind_param("i",$user_id);
        $stmt->execute();

        if($stmt->affected_rows > 0) {
            $conn->commit();
            $response = [
                'success' => true,
                'message' => 'User deleted successfully.'
            ];
        } else {
            throw new Exception('User not found or could not be deleted.');
        }
    } catch (Exception $e) {
        $conn->rollback();
        $response = [
            'success' => false,
            'message' => 'Error deleting user: '.$e->getMessage()
        ];
    }
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
}
?>