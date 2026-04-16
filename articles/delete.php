<?php
require '../config/database.php';
require '../includes/header.php';
require '../config/csrf.php';

check_csrf();

$id = $_POST['id'] ?? null;

if (!$id) {
    die("No ID");
}

$stmt = $pdo->prepare("DELETE FROM articles WHERE id = :id");
$stmt->execute(['id' => $id]);

header('Location: ../index.php');
exit;