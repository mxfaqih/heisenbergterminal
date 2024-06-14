<?php
session_start();
include '../../config/database.php';

// Check if the user is an author
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'author') {
    header("Location: ../../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch author data
$stmt = $pdo->prepare("SELECT author_id FROM authors WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$author = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$author) {
    // Handle case where user is not an author
    header("Location: ../../index.php");
    exit();
}

if (isset($_POST['update_article'])) {
    $article_id = $_POST['article_id'];
    $title = $_POST['edit_title'];
    $content = $_POST['edit_content'];
    $category_id = $_POST['edit_category_id'];
    $status = $_POST['edit_status'];
    $tags = isset($_POST['tags']) ? $_POST['tags'] : [];

    // Check if cover image is uploaded
    $cover_image_update = "";
    if (isset($_FILES['edit_cover_image']) && $_FILES['edit_cover_image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['edit_cover_image']['tmp_name'];
        $file_name = $_FILES['edit_cover_image']['name'];
        $destination = '../../uploads/' . $file_name;

        if (move_uploaded_file($file_tmp_path, $destination)) {
            $stmt = $pdo->prepare("INSERT INTO files (user_id, file_path) VALUES (:user_id, :file_path)");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':file_path', $file_name);
            $stmt->execute();
            $cover_image_id = $pdo->lastInsertId();
            $cover_image_update = ", cover_image_id = :cover_image_id";
        }
    }

    // Update news record
    $stmt = $pdo->prepare("UPDATE news SET title = :title, content = :content, category_id = :category_id, status = :status $cover_image_update WHERE news_id = :news_id AND author_id = :author_id");
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':content', $content);
    $stmt->bindParam(':category_id', $category_id);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':news_id', $article_id);
    $stmt->bindParam(':author_id', $author['author_id']);
    if (!empty($cover_image_update)) {
        $stmt->bindParam(':cover_image_id', $cover_image_id);
    }
    $stmt->execute();

    // Handle tags
    $stmt = $pdo->prepare("DELETE FROM news_tags WHERE news_id = :news_id");
    $stmt->bindParam(':news_id', $article_id);
    $stmt->execute();

    foreach ($tags as $tag_id) {
        $stmt = $pdo->prepare("INSERT INTO news_tags (news_id, tag_id) VALUES (:news_id, :tag_id)");
        $stmt->bindParam(':news_id', $article_id);
        $stmt->bindParam(':tag_id', $tag_id);
        $stmt->execute();
    }

    header("Location: ../../views/author/dashboard.php");
    exit();
}

// Handle multi-update for article status
if (isset($_POST['article_action']) && $_POST['article_action'] == 'multi_update') {
    if (isset($_POST['article_ids']) && is_array($_POST['article_ids'])) {
        foreach ($_POST['article_ids'] as $article_id) {
            $status = $_POST['status_' . $article_id];
            $stmt = $pdo->prepare("UPDATE news SET status = :status WHERE news_id = :news_id");
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':news_id', $article_id);
            $stmt->execute();
        }
    }
    header("Location: ../../views/author/dashboard.php");
    exit();
}
