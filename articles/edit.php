<?php
require '../config/database.php';
require '../includes/header.php';

$id = $_GET['id'] ?? null;
if (!$id) { header('Location: ../index.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $authorName = trim($_POST['author_name']);
    $publicationTitle = trim($_POST['publication_title']);

    // Проверка и добавяне на автора
    $stmt = $pdo->prepare("SELECT id FROM authors WHERE name = :name");
    $stmt->execute(['name' => $authorName]);
    $author = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$author) {
        $stmt = $pdo->prepare("INSERT INTO authors (name) VALUES (:name) RETURNING id");
        $stmt->execute(['name' => $authorName]);
        $authorId = $stmt->fetchColumn();
    } else {
        $authorId = $author['id'];
    }

    // Проверка и добавяне на публикацията
    $stmt = $pdo->prepare("SELECT id FROM publications WHERE title = :title");
    $stmt->execute(['title' => $publicationTitle]);
    $publication = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$publication) {
        $stmt = $pdo->prepare("INSERT INTO publications (title) VALUES (:title) RETURNING id");
        $stmt->execute(['title' => $publicationTitle]);
        $publicationId = $stmt->fetchColumn();
    } else {
        $publicationId = $publication['id'];
    }

    // Актуализиране на статията
    $stmt = $pdo->prepare("
        UPDATE articles SET title=:title, author_id=:author, publication_id=:pub WHERE id=:id
    ");
    $stmt->execute([
        'title' => $title,
        'author' => $authorId,
        'pub' => $publicationId,
        'id' => $id
    ]);

    header('Location: ../index.php');
    exit;
}

// Вземане на данни за статията
$article = $pdo->prepare("
    SELECT a.id, a.title, au.name AS author_name, p.title AS publication_title
    FROM articles a
    LEFT JOIN authors au ON a.author_id = au.id
    LEFT JOIN publications p ON a.publication_id = p.id
    WHERE a.id = :id
");
$article->execute(['id' => $id]);
$article = $article->fetch(PDO::FETCH_ASSOC);
?>

<h2>Edit Article</h2>
<form method="post">
    <div class="mb-3">
        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($article['title']) ?>" required>
    </div>
    <div class="mb-3">
        <input type="text" name="author_name" class="form-control" value="<?= htmlspecialchars($article['author_name']) ?>" placeholder="Author Name" required>
    </div>
    <div class="mb-3">
        <input type="text" name="publication_title" class="form-control" value="<?= htmlspecialchars($article['publication_title']) ?>" placeholder="Publication Title" required>
    </div>
    <button class="btn btn-primary">Save</button>
</form>

<?php require '../includes/footer.php'; ?>