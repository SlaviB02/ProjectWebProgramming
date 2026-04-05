<?php
require '../includes/auth.php';
requireLogin();
require '../config/database.php';

$id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM articles WHERE id=:id");
$stmt->execute(['id'=>$id]);
$article = $stmt->fetch();

if($_POST){
    $stmt = $pdo->prepare("UPDATE articles SET title=:t WHERE id=:id");
    $stmt->execute([
        't'=>$_POST['title'],
        'id'=>$id
    ]);
    header("Location: ../index.php");
}
?>

<form method="post">
<input name="title" value="<?= htmlspecialchars($article['title']) ?>">
<button>Update</button>
</form>
