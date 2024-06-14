<?php
session_start();
include '../../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'author') {
    header("Location: ../../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$article_id = $_GET['id'];

// Fetch article data to ensure the author has the right to delete it
$stmt = $pdo->prepare("SELECT * FROM news WHERE news_id = :news_id AND author_id = :author_id");
$stmt->bindParam(':news_id', $article_id);
$stmt->bindParam(':author_id', $user_id);
$stmt->execute();
$article = $stmt->fetch(PDO::FETCH_ASSOC);

// if (!$article) {
//     // If the article does not exist or the user is not the author
//     header("Location: ../../index.php");
//     exit();
// }

try {
    // Start a transaction
    $pdo->beginTransaction();

    // Delete associated comments
    $stmt = $pdo->prepare("DELETE FROM comments WHERE news_id = :news_id");
    $stmt->bindParam(':news_id', $article_id);
    $stmt->execute();

    // Delete associated ratings
    $stmt = $pdo->prepare("DELETE FROM ratings WHERE news_id = :news_id");
    $stmt->bindParam(':news_id', $article_id);
    $stmt->execute();

    // Delete associated tags
    $stmt = $pdo->prepare("DELETE FROM news_tags WHERE news_id = :news_id");
    $stmt->bindParam(':news_id', $article_id);
    $stmt->execute();

    // Delete associated files
    $stmt = $pdo->prepare("DELETE FROM files WHERE news_id = :news_id");
    $stmt->bindParam(':news_id', $article_id);
    $stmt->execute();

    // Finally, delete the article
    $stmt = $pdo->prepare("DELETE FROM news WHERE news_id = :news_id");
    $stmt->bindParam(':news_id', $article_id);
    $stmt->execute();

    // Commit the transaction
    $pdo->commit();

    header("Location: /views/author/dashboard.php");
    exit();
} catch (Exception $e) {
    // Rollback the transaction in case of an error
    $pdo->rollBack();
    echo "Failed to delete article: " . $e->getMessage();
}
?>
