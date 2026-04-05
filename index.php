<?php
require 'config/database.php';
require 'includes/header.php';

// ====== Search ======
$search = $_GET['search'] ?? '';

// ====== Pagination settings ======
$perPage = 6; // articles per page
$page = max(1, intval($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

// ====== Count total articles for pagination ======
$countSql = "
    SELECT COUNT(*) 
    FROM articles a
    LEFT JOIN authors au ON a.author_id = au.id
    LEFT JOIN publications p ON a.publication_id = p.id
";
$params = [];
if ($search) {
    $countSql .= " WHERE a.title ILIKE :search OR au.name ILIKE :search OR p.title ILIKE :search";
    $params['search'] = "%$search%";
}
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalArticles = $countStmt->fetchColumn();
$totalPages = ceil($totalArticles / $perPage);

// ====== Fetch articles for current page ======
$sql = "
    SELECT a.id, a.title, a.created_at, 
           au.name AS author_name, 
           p.title AS publication_title
    FROM articles a
    LEFT JOIN authors au ON a.author_id = au.id
    LEFT JOIN publications p ON a.publication_id = p.id
";
if ($search) {
    $sql .= " WHERE a.title ILIKE :search OR au.name ILIKE :search OR p.title ILIKE :search";
}
$sql .= " ORDER BY a.created_at DESC LIMIT $perPage OFFSET $offset";

$stmt = $pdo->prepare($sql);
if ($search) {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
$stmt->execute();
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ====== Fetch favorite article separately ======
$favoriteId = $_COOKIE['favorite_article'] ?? null;
$favorite = null;

if ($favoriteId) {
    $favStmt = $pdo->prepare("
        SELECT a.id, a.title, au.name AS author_name, p.title AS publication_title
        FROM articles a
        LEFT JOIN authors au ON a.author_id = au.id
        LEFT JOIN publications p ON a.publication_id = p.id
        WHERE a.id = :id
        LIMIT 1
    ");
    $favStmt->execute(['id' => $favoriteId]);
    $favorite = $favStmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!-- ====== Search Form ====== -->
<form method="get" class="mb-4">
    <div class="input-group">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control" placeholder="Search articles, authors or publications...">
        <button class="btn btn-primary">Search</button>
    </div>
</form>

<!-- ====== Favorite Article Block ====== -->
<?php if ($favorite): ?>
    <div class="alert alert-success shadow p-4 mb-4" style="font-size:1.1rem; border-left: 5px solid #28a745;">
        <h4 class="mb-2">⭐ Favorite Article:</h4>
        <strong><?= htmlspecialchars($favorite['title']) ?></strong><br>
        <em>Author: <?= htmlspecialchars($favorite['author_name'] ?? 'N/A') ?></em><br>
        <em>Publication: <?= htmlspecialchars($favorite['publication_title'] ?? 'N/A') ?></em>
    </div>
<?php endif; ?>

<h2>Articles</h2>
<div class="row">
<?php foreach ($articles as $a): ?>
    <div class="col-md-4 mb-3">
        <div class="card h-100 shadow-sm position-relative <?= $a['id'] == $favoriteId ? 'border-success bg-light' : '' ?>">
            <?php if ($a['id'] == $favoriteId): ?>
                <span class="badge bg-success position-absolute top-0 end-0 m-2">⭐ Favorite</span>
            <?php endif; ?>
            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($a['title']) ?></h5>
                <p class="card-text">
                    Author: <?= htmlspecialchars($a['author_name'] ?? 'N/A') ?><br>
                    Publication: <?= htmlspecialchars($a['publication_title'] ?? 'N/A') ?>
                </p>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="articles/edit.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                    <a href="articles/delete.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-danger">Delete</a>
                <?php endif; ?>
                <a href="articles/favorite.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-success">Set as Favorite</a>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>

<!-- ====== Pagination Links ====== -->
<nav aria-label="Page navigation">
    <ul class="pagination justify-content-center mt-4">
        <?php if ($page > 1): ?>
            <li class="page-item">
                <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $page-1 ?>">Previous</a>
            </li>
        <?php endif; ?>

        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <li class="page-item <?= $p == $page ? 'active' : '' ?>">
                <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $p ?>"><?= $p ?></a>
            </li>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <li class="page-item">
                <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $page+1 ?>">Next</a>
            </li>
        <?php endif; ?>
    </ul>
</nav>

<?php require 'includes/footer.php'; ?>