<?php
require '../config/database.php';
require '../includes/header.php';
require '../config/csrf.php';
check_csrf();

$id = $_GET['id'] ?? null;
if (!$id) { 
    header('Location: ../index.php'); 
    exit; 
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title']);
    $authorName = trim($_POST['author_name']);
    $publicationTitle = trim($_POST['publication_title']);

    // ===== VALIDATION =====
    if (empty($title)) {
        $errors[] = "Title is required";
    } elseif (strlen($title) < 3) {
        $errors[] = "Title must be at least 3 characters";
    }

    if (empty($authorName)) {
        $errors[] = "Author name is required";
    }

    if (empty($publicationTitle)) {
        $errors[] = "Publication title is required";
    }

    // ===== IF NO ERRORS =====
    if (empty($errors)) {

        // Автор
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

        // Публикация
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

        // Update
        $stmt = $pdo->prepare("
            UPDATE articles 
            SET title=:title, author_id=:author, publication_id=:pub 
            WHERE id=:id
        ");
        $stmt->execute([
            'title' => $title,
            'author' => $authorId,
            'pub' => $publicationId,
            'id' => $id
        ]);

        header('Location: ../index.php');
        exit;
    } else {
        http_response_code(400); // за курсовата
    }
}

// ===== Load article =====
$stmt = $pdo->prepare("
    SELECT a.id, a.title, au.name AS author_name, p.title AS publication_title
    FROM articles a
    LEFT JOIN authors au ON a.author_id = au.id
    LEFT JOIN publications p ON a.publication_id = p.id
    WHERE a.id = :id
");
$stmt->execute(['id' => $id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    echo "<div class='alert alert-danger'>Article not found</div>";
    require '../includes/footer.php';
    exit;
}
?>

<h2>Edit Article</h2>

<!-- ERROR MESSAGES -->
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $e): ?>
            <div><?= htmlspecialchars($e) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="post">
    <div class="mb-3">
        <input type="text" name="title" class="form-control"
               value="<?= htmlspecialchars($_POST['title'] ?? $article['title']) ?>" required>
    </div>

    <div class="mb-3">
        <input type="text" name="author_name" class="form-control"
               value="<?= htmlspecialchars($_POST['author_name'] ?? $article['author_name']) ?>"
               placeholder="Author Name" required>
    </div>

    <div class="mb-3">
        <input type="text" name="publication_title" class="form-control"
               value="<?= htmlspecialchars($_POST['publication_title'] ?? $article['publication_title']) ?>"
               placeholder="Publication Title" required>
    </div>
    <button class="btn btn-primary">Save</button>
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
</form>

<?php require '../includes/footer.php'; ?>