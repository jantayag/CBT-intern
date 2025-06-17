<?php
include('../db.php');


function getNextTopicId($conn) {
    $result = $conn->query("SELECT MAX(id) as max_id FROM topics");
    $row = $result->fetch_assoc();
    return ($row['max_id'] === null) ? 1 : $row['max_id'] + 1;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $classCode = $_POST['class_code'];
    $topic = $_POST['topic'];

    try {
        $conn->begin_transaction();

        $nextTopicId = getNextTopicId($conn); 

        $sql = "INSERT INTO topics (id, topic, class_code) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $nextTopicId, $topic, $classCode);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $conn->commit();
            echo json_encode([
                'success' => true, 
                'message' => 'Topic added successfully.',
                'topic_id' => $nextTopicId
            ]);
        } else {
            throw new Exception('Failed to add topic.');
        }

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode([
            'success' => false, 
            'message' => 'Error adding topic: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid request method.'
    ]);
}
?>