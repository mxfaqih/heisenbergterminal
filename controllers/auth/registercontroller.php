<?php
require '../../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['registerUsername'];
    $email = $_POST['registerEmail'];
    $password = password_hash($_POST['registerPassword'], PASSWORD_BCRYPT);
    $role = $_POST['registerRole'];

    try {
        $query = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->execute();
        
        $user_id = $pdo->lastInsertId();
        
        $roleQuery = "INSERT INTO user_roles (user_id, role) VALUES (:user_id, :role)";
        $roleStmt = $pdo->prepare($roleQuery);
        $roleStmt->bindParam(':user_id', $user_id);
        $roleStmt->bindParam(':role', $role);
        $roleStmt->execute();

        if ($role == 'author') {
            $authorQuery = "INSERT INTO authors (user_id, bio) VALUES (:user_id, '')";
            $authorStmt = $pdo->prepare($authorQuery);
            $authorStmt->bindParam(':user_id', $user_id);
            $authorStmt->execute();
        }
        
        header("Location: /views/auth.php");
    } catch (PDOException $exception) {
        echo "Registration error: " . $exception->getMessage();
    }
}
?>
