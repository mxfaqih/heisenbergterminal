<?php

$host = "localhost";
$db_name = "id22302801_db_heisenberg";
$username = "id22302801_root";
$password = "Heisenberg888#";

try {
    // Membuat koneksi PDO
    $pdo = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    
    // Mengatur mode error PDO ke Exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Mengatur set karakter ke UTF-8
    $pdo->exec("set names utf8");
    
} catch (PDOException $exception) {
    // Menangani kesalahan koneksi
    echo "Connection error: " . $exception->getMessage();
    exit();
}
?>
