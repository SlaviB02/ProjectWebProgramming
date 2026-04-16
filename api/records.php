<?php
require '../config/database.php';
require '../config/api.php';

header('Content-Type: application/json');

// ===== TOKEN CHECK =====
$headers = getallheaders();
$token = $headers['Authorization'] ?? '';

if ($token !== API_TOKEN) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// ===== METHOD =====
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {

    // ===== GET ALL ARTICLES =====
    $stmt = $pdo->query("
        SELECT a.id, a.title, 
               au.name AS author, 
               p.title AS publication,
               a.created_at
        FROM articles a
        LEFT JOIN authors au ON a.author_id = au.id
        LEFT JOIN publications p ON a.publication_id = p.id
        ORDER BY a.created_at DESC
    ");

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($data);
}

elseif ($method === 'POST') {

    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON']);
        exit;
    }

    $title = trim($input['title'] ?? '');
    $authorName = trim($input['author'] ?? '');
    $publicationTitle = trim($input['publication'] ?? '');

    // ===== VALIDATION =====
    if (!$title || !$authorName || !$publicationTitle) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing fields']);
        exit;
    }

    // ===== AUTHOR =====
    $stmt = $pdo->prepare("SELECT id FROM authors WHERE name=:name");
    $stmt->execute(['name' => $authorName]);
    $author = $stmt->fetch();

    if (!$author) {
        $stmt = $pdo->prepare("INSERT INTO authors (name) VALUES (:name) RETURNING id");
        $stmt->execute(['name' => $authorName]);
        $authorId = $stmt->fetchColumn();
    } else {
        $authorId = $author['id'];
    }

    // ===== PUBLICATION =====
    $stmt = $pdo->prepare("SELECT id FROM publications WHERE title=:title");
    $stmt->execute(['title' => $publicationTitle]);
    $pub = $stmt->fetch();

    if (!$pub) {
        $stmt = $pdo->prepare("INSERT INTO publications (title) VALUES (:title) RETURNING id");
        $stmt->execute(['title' => $publicationTitle]);
        $publicationId = $stmt->fetchColumn();
    } else {
        $publicationId = $pub['id'];
    }

    // ===== INSERT ARTICLE =====
    $stmt = $pdo->prepare("
        INSERT INTO articles (title, author_id, publication_id)
        VALUES (:title, :author, :pub)
        RETURNING id
    ");

    $stmt->execute([
        'title' => $title,
        'author' => $authorId,
        'pub' => $publicationId
    ]);

    $newId = $stmt->fetchColumn();

    http_response_code(201);
    echo json_encode([
        'message' => 'Article created',
        'id' => $newId
    ]);
}

else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}