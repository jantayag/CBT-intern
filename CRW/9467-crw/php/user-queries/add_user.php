<?php
include('../db.php');
header('Content-Type: application/json');

function getNextUserId($conn) {
    $result = $conn->query("SELECT MAX(id) as max_id FROM users");
    $row = $result->fetch_assoc();
    return ($row['max_id'] === null) ? 1 : $row['max_id'] + 1;
}

function addSingleUser($conn, $userData) {
    $email = filter_var($userData['email'], FILTER_SANITIZE_EMAIL);
    $emailCheckSql = "SELECT id FROM users WHERE email = ?";
    $emailStmt = $conn->prepare($emailCheckSql);
    $emailStmt->bind_param('s', $email);
    $emailStmt->execute();
    $emailCheckResult = $emailStmt->get_result();

    if ($emailCheckResult->num_rows > 0) {
        return [
            'success' => false,
            'message' => "Email $email is already in use."
        ];
    }

    $userId = getNextUserId($conn);
    $hashedPassword = password_hash($userData['password'], PASSWORD_BCRYPT);
    $firstName = htmlspecialchars(trim($userData['first_name']));
    $lastName = htmlspecialchars(trim($userData['last_name']));
    $userType = 'student';  // Fixed user type

    $sql = "INSERT INTO users (id, password, email, first_name, last_name, user_type) VALUES (?,?,?,?,?,?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('isssss', 
        $userId,
        $hashedPassword,  
        $email,
        $firstName,
        $lastName,
        $userType
    );

    if ($stmt->execute()) {
        return [
            'success' => true,
            'user_id' => $userId,
            'message' => "User $email added successfully"
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Error creating user: ' . $stmt->error
        ];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => '', 'results' => []];
    
    if (isset($_FILES['csv-upload']) && $_FILES['csv-upload']['size'] > 0) {
        $file = fopen($_FILES['csv-upload']['tmp_name'], 'r');
        $headers = fgetcsv($file); // Skip the header row

        while (($line = fgetcsv($file)) !== FALSE) {
            if (count($line) >= 5) {
                $idNumber = trim($line[1]);    // ID Number
                $fullName = trim($line[2]);    // Name
                $email = trim($line[4]);       // EMAIL

                $nameParts = preg_split('/\s+/', $fullName);
                $firstName = $nameParts[0];
                $lastName = end($nameParts);

                $userData = [
                    'password' => $idNumber,
                    'email' => $email,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'user_type' => 'student'
                ];

                $result = addSingleUser($conn, $userData);
                $response['results'][] = $result;

                if ($result['success']) {
                    $response['success'] = true;
                }
            }
        }
        fclose($file);
        $response['message'] = "User accounts processed from CSV.";
    } else {
        $result = addSingleUser($conn, $_POST);
        $response = $result;
    }

    echo json_encode($response);
    exit;
}
?>
