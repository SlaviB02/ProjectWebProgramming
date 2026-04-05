<?php
require '../config/database.php';
require '../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = $_POST['title'];
    $authorName = trim($_POST['author_name']);
    $publicationTitle = trim($_POST['publication_title']);

    // 1️⃣ Проверка и добавяне на автора
    $stmt = $pdo->prepare("SELECT id FROM authors WHERE name = :name");
    $stmt->execute(['name' => $authorName]);
    $author = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$author) {
        // добавяне на нов автор
        $stmt = $pdo->prepare("INSERT INTO authors (name) VALUES (:name) RETURNING id");
        $stmt->execute(['name' => $authorName]);
        $authorId = $stmt->fetchColumn();
    } else {
        $authorId = $author['id'];
    }

    // 2️⃣ Проверка и добавяне на публикацията
    $stmt = $pdo->prepare("SELECT id FROM publications WHERE title = :title");
    $stmt->execute(['title' => $publicationTitle]);
    $publication = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$publication) {
        // добавяне на нова публикация
        $stmt = $pdo->prepare("INSERT INTO publications (title) VALUES (:title) RETURNING id");
        $stmt->execute(['title' => $publicationTitle]);
        $publicationId = $stmt->fetchColumn();
    } else {
        $publicationId = $publication['id'];
    }

    // 3️⃣ Добавяне на статията
    $stmt = $pdo->prepare("
        INSERT INTO articles (title, author_id, publication_id) 
        VALUES (:title, :author_id, :pub_id)
    ");
    $stmt->execute([
        'title' => $title,
        'author_id' => $authorId,
        'pub_id' => $publicationId
    ]);

    header('Location: ../index.php');
    exit;
}
?>

<h2>Add Article</h2>
<form method="post">
    <div class="mb-3">
        <input type="text" name="title" class="form-control" placeholder="Article Title" required>
    </div>
    <div class="mb-3">
        <input type="text" name="author_name" class="form-control" placeholder="Author Name" required>
    </div>
    <div class="mb-3">
        <input type="text" name="publication_title" class="form-control" placeholder="Publication Title" required>
    </div>
    <button class="btn btn-primary">Add Article</button>
</form>

<?php require '../includes/footer.php'; ?>