<?php
require '../../config/database.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $loginEmail = $_POST['loginEmail'];
    $loginPassword = $_POST['loginPassword'];

    try {
        $query = "SELECT * FROM users WHERE (username = :loginEmail OR email = :loginEmail)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':loginEmail', $loginEmail);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($loginPassword, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];

            $query = "SELECT * FROM user_roles WHERE user_id = :user_id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':user_id', $user['user_id']);
            $stmt->execute();
        
            $role = $stmt->fetch(PDO::FETCH_ASSOC);
            $_SESSION['role'] = $role['role'];
            
            header("Location: ../../index.php");
        } else {
            echo "Invalid username or password.";
        }
    } catch (PDOException $exception) {
        echo "Login error: " . $exception->getMessage();
    }
}
?>
