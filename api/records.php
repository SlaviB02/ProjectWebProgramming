<?php
require '../config/database.php';

$token = "MY_SECURE_TOKEN";
$headers = getallheaders();

if(($headers['Authorization'] ?? '') !== "Bearer $token"){
    http_response_code(401);
    exit;
}

if($_SERVER['REQUEST_METHOD']==='GET'){
$stmt=$pdo->query("SELECT * FROM articles");
echo json_encode($stmt->fetchAll());
}

if($_SERVER['REQUEST_METHOD']==='POST'){
$data=json_decode(file_get_contents("php://input"),true);
$stmt=$pdo->prepare("INSERT INTO articles(title) VALUES(:t)");
$stmt->execute(['t'=>$data['title']]);
echo json_encode(['status'=>'ok']);
}