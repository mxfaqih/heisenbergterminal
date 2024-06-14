<?php
session_start();
include '../../config/database.php';

// Check if the user is an author
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'author') {
    header("Location: ../../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$stmt = $pdo->prepare("SELECT username, email FROM users WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch author data
$stmt = $pdo->prepare("SELECT * FROM authors WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$author = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle bio update
if (isset($_POST['update_bio'])) {
    $bio = $_POST['bio'];
    $stmt = $pdo->prepare("UPDATE authors SET bio = :bio WHERE user_id = :user_id");
    $stmt->bindParam(':bio', $bio);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    header("Location: dashboard.php");
    exit();
}

// Fetch articles written by the author
$stmt = $pdo->prepare("SELECT * FROM news WHERE author_id = :author_id");
$stmt->bindParam(':author_id', $author['author_id']);
$stmt->execute();
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle article creation
if (isset($_POST['create_article'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $category_id = $_POST['category_id'];
    $status = $_POST['status'];

    // Handle file upload
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['cover_image']['tmp_name'];
        $file_name = $_FILES['cover_image']['name'];
        $destination = '../../uploads/' . $file_name;

        if (move_uploaded_file($file_tmp_path, $destination)) {
            // Insert file record
            $stmt = $pdo->prepare("INSERT INTO files (user_id, file_path) VALUES (:user_id, :file_path)");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':file_path', $file_name);
            $stmt->execute();
            $cover_image_id = $pdo->lastInsertId();
        }
    }

    // Insert news record
    $stmt = $pdo->prepare("INSERT INTO news (author_id, category_id, title, content, status, cover_image_id) VALUES (:author_id, :category_id, :title, :content, :status, :cover_image_id)");
    $stmt->bindParam(':author_id', $author['author_id']);
    $stmt->bindParam(':category_id', $category_id);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':content', $content);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':cover_image_id', $cover_image_id);
    $stmt->execute();

    header("Location: dashboard.php");
    exit();
}
?>
