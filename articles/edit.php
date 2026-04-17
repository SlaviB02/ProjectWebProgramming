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

          if (
        empty($_POST['request_nonce']) ||
        empty($_SESSION['form_nonce']) ||
        $_POST['request_nonce'] !== $_SESSION['form_nonce']
    ) {
        die("Replay attack detected: invalid nonce");
    }

    // ===== VALIDATION =====
   if (empty($title)) {
        $errors[] = "Title is required";
    } elseif (strlen($title) < 3 || strlen($title)>255) {
        $errors[] = "Title must me in constraints 3-255 length";
    }


    if (empty($authorName)) {
        $errors[] = "Author name is required";
    }elseif (strlen($authorName) < 3 || strlen($authorName)>255) {
        $errors[] = "Author name must me in constraints 3-255 length";
    } elseif (!preg_match('/^[^0-9]+$/', $authorName)) {
    $errors[]="Author name must not have numbers.";
    }

    if (empty($publicationTitle)) {
        $errors[] = "Publication title is required";
    }elseif (strlen($publicationTitle) < 3 || strlen($publicationTitle)>255) {
        $errors[] = "Publication title must me in constraints 3-255 length";
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

    
        unset($_SESSION['form_nonce']);
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
    <input type="hidden" name="request_nonce" value="<?= $_SESSION['form_nonce'] ?>">
</form>

<?php require '../includes/footer.php'; ?>