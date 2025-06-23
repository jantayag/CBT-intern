<?php
include('../db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $topic_id = isset($_POST['topic_id']) ? intval($_POST['topic_id']) : 0;
    $assessment_id = isset($_POST['assessment_id']) ? intval($_POST['assessment_id']) : 0;

    if ($topic_id > 0 && $assessment_id > 0) {
        $sql = "DELETE FROM topic_assessments WHERE topic_id = ? AND assessment_id = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param('ii', $topic_id, $assessment_id);
            
            if ($stmt->execute()) {
                $response = [
                    'success' => true,
                    'message' => 'Assessment removed successfully'
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Failed to remove assessment: ' . $conn->error
                ];
            }
            
            $stmt->close();
        } else {
            $response = [
                'success' => false,
                'message' => 'Failed to prepare query: ' . $conn->error
            ];
        }
    } else {
        $response = [
            'success' => false,
            'message' => 'Invalid topic or assessment ID'
        ];
    }
} else {
    $response = [
        'success' => false,
        'message' => 'Invalid request method'
    ];
}

header('Content-Type: application/json');
echo json_encode($response);
?>