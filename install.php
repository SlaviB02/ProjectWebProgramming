<?php
require 'config/database.php';

$pdo->exec("
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS authors (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS publications (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS articles (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255),
    author_id INT REFERENCES authors(id),
    publication_id INT REFERENCES publications(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
");

$password = password_hash("admin123", PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO users (username,password) VALUES ('admin',:pass) ON CONFLICT DO NOTHING");
$stmt->execute(['pass'=>$password]);