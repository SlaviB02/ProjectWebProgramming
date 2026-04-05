<?php
$id = $_GET['id'] ?? null;

if ($id) {
    // Cookie за 30 дни
    setcookie('favorite_article', $id, time() + 60*60*24*30, '/');
}

// Връщане обратно към index.php
header('Location: ../index.php');
exit;