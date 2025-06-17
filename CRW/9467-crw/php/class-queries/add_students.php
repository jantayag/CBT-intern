<?php
include('../db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $classCode = $_POST['class_code'];
    $studentIds = [];

    if (isset($_POST['email'])) {
        $email = $_POST['email'];
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND user_type = 'Student'");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $studentIds[] = $user['id'];
        } else {
            echo json_encode(['success' => false, 'message' => 'Student not found with this email.']);
            exit;
        }
    }

    if (isset($_FILES['csv_file'])) {
        $file = $_FILES['csv_file'];

        $fileType = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (strtolower($fileType) !== 'csv') {
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Please upload a CSV file.']);
            exit;
        }

        $csvFile = fopen($file['tmp_name'], 'r');
        $headers = fgetcsv($csvFile);
  
        $emailColumnIndex = null;
        foreach ($headers as $index => $header) {
            if (stripos($header, 'email') !== false) {
                $emailColumnIndex = $index;
                break;
            }
        }

        if ($emailColumnIndex === null) {
            echo json_encode(['success' => false, 'message' => 'No email column found in CSV.']);
            exit;
        }

        while (($row = fgetcsv($csvFile)) !== false) {
            $email = $row[$emailColumnIndex];
            
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND user_type = 'Student'");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $studentIds[] = $user['id'];
            }
        }
        fclose($csvFile);
    }

    try {
        $conn->begin_transaction();

        foreach ($studentIds as $studentId) {
            $sql = "INSERT IGNORE INTO student_class (student_id, class_code) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $studentId, $classCode);
            $stmt->execute();
        }

        $conn->commit();

        echo json_encode([
            'success' => true, 
            'message' => 'Students have been added to the class successfully.'
        ]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Error adding students: ' . $e->getMessage()]);
    }
}
?>
