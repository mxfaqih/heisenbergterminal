<?php
session_start();
include '../../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'author') {
    http_response_code(403);
    exit('Forbidden');
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    exit('Bad Request');
}

$news_id = $_GET['id'];

$stmt = $pdo->prepare("SELECT t.tag_id, t.tag_name FROM tags t JOIN news_tags nt ON t.tag_id = nt.tag_id WHERE nt.news_id = :news_id");
$stmt->bindParam(':news_id', $news_id);
$stmt->execute();
$tags = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($tags);
