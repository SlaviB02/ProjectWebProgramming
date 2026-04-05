<?php
require '../includes/auth.php';
requireLogin();
require '../config/database.php';

if($_POST){
$stmt=$pdo->prepare("INSERT INTO articles(title) VALUES(:t)");
$stmt->execute(['t'=>$_POST['title']]);
header("Location: ../index.php");
}
?>

<form method="post">
<input name="title">
<button>Add</button>
</form>