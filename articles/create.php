<?php
require '../config/database.php';
require '../includes/header.php';
require '../config/csrf.php';
check_csrf();

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

    // Ако няма грешки -> записваме
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

        // Статия
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
}
?>

<h2>Add Article</h2>

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
        <input type="text" name="title" class="form-control" placeholder="Article Title" required>
    </div>
    <div class="mb-3">
        <input type="text" name="author_name" class="form-control" placeholder="Author Name" required>
    </div>
    <div class="mb-3">
        <input type="text" name="publication_title" class="form-control" placeholder="Publication Title" required>
    </div>
    <button class="btn btn-primary">Add Article</button>
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
</form>

<?php require '../includes/footer.php'; ?>