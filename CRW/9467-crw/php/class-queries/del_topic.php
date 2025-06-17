<?php
include('../db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $topicId = isset($_POST['topic_id']) ? (int)$_POST['topic_id'] : 0;

    if ($topicId <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid topic ID provided.'
        ]);
        exit;
    }

    try {
        $conn->begin_transaction();

        $sql = "DELETE FROM topic_assessments WHERE topic_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $topicId);
        $stmt->execute();

        $sql = "DELETE FROM topics WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $topicId);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $conn->commit();
            echo json_encode([
                'success' => true,
                'message' => 'Topic deleted successfully.'
            ]);
        } else {
            throw new Exception('Topic not found or already deleted.');
        }

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'message' => 'Error deleting topic: ' . $e->getMessage()
        ]);
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
}
?>