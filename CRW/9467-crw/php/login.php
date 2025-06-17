<?php
session_start();
session_regenerate_id(true);

require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    try {
        $stmt = $conn->prepare("SELECT id, password, email, first_name, last_name, user_type 
                               FROM users 
                               WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (strlen($user['password']) === 60) {
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['first_name'] = $user['first_name'];
                    $_SESSION['last_name'] = $user['last_name'];
                    $_SESSION['user_type'] = $user['user_type'];
                    $_SESSION['last_activity'] = time();

                    header("Location: ../classes.php");
                    exit();
                } else {
                    echo "<script>
                            alert('Invalid credentials.');
                            window.location.href='../index.php';
                          </script>";
                }
            } else {
                if ($password === $user['password']) {
                    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                    $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $updateStmt->bind_param("si", $hashedPassword, $user['id']);
                    $updateStmt->execute();

                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['first_name'] = $user['first_name'];
                    $_SESSION['last_name'] = $user['last_name'];
                    $_SESSION['user_type'] = $user['user_type'];
                    $_SESSION['last_activity'] = time();

                    header("Location: ../classes.php");
                    exit();
                } else {
                    echo "<script>
                            alert('Invalid credentials.');
                            window.location.href='../index.php';
                          </script>";
                }
            }
        } else {
            echo "<script>
                    alert('Invalid credentials.');
                    window.location.href='../index.php';
                  </script>";
        }

    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        echo "<script>
                alert('An error occurred. Please try again later.');
                window.location.href='../index.php';
              </script>";
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: ../index.php");
    exit();
}
?>
