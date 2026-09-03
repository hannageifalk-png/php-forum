<?php

$host = getenv('DB_HOST');
$dbname = 'community_forum';
$user = getenv('DB_USER');
$password = getenv('DB_PASSWORD');

$dsn = "mysql:host=$host;dbname=$dbname";

$pdo = new PDO($dsn, $user, $password);

try {
    $pdo = new PDO($dsn, $user, $password);
} 
catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    exit;
}

?>