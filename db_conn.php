<?php

$sname = "127.0.0.1";
$username = "root";
$password = "";
$db_name = "petshop_db";
$port = 3308;

try {
    $conn = new PDO(
        "mysql:host={$sname};port={$port};dbname={$db_name};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die("Kết nối thất bại!");
}
