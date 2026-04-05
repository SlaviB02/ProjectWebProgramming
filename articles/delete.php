<?php
require '../config/database.php';

$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $pdo->prepare("DELETE FROM articles WHERE id=:id");
    $stmt->execute(['id'=>$id]);
}

header('Location: ../index.php');
exit;