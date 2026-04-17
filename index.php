<?php
declare(strict_types=1);

require 'config/database.php';
require 'repositories/ArticleRepository.php';
require 'includes/header.php';

// =====================
// INIT REPOSITORY
// =====================
$repo = new ArticleRepository($pdo);

// =====================
// INPUTS
// =====================
$search = trim($_GET['search'] ?? '');
$sort   = $_GET['sort'] ?? 'date_desc';

$perPage = 6;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

// =====================
// SORT WHITELIST
// =====================
$sortOptions = [
    'date_desc'   => 'a.created_at DESC',
    'date_asc'    => 'a.created_at ASC',
    'title_asc'   => 'a.title ASC',
    'title_desc'  => 'a.title DESC',
    'author_asc'  => 'au.name ASC',
    'author_desc' => 'au.name DESC'
];

$orderBy = $sortOptions[$sort] ?? $sortOptions['date_desc'];

// =====================
// DATA FROM REPOSITORY
// =====================
$totalArticles = $repo->count($search);
$totalPages = max(1, (int)ceil($totalArticles / $perPage));

$articles = $repo->getAll(
    $search,
    $orderBy,
    $perPage,
    $offset
);

// =====================
// FAVORITE ARTICLE
// =====================
$favorite = null;

if (!empty($_COOKIE['favorite_article'])) {
    $favorite = $repo->findFavorite((int)$_COOKIE['favorite_article']);
}
?>

<!-- ================= SEARCH ================= -->
<form method="get" class="mb-3">
    <input type="text"
           name="search"
           value="<?= htmlspecialchars($search) ?>"
           class="form-control"
           placeholder="Search articles...">
</form>

<!-- ================= SORT ================= -->
<form method="get" class="mb-3">
    <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">

    <select name="sort" class="form-select" onchange="this.form.submit()">
        <option value="date_desc"   <?= $sort==='date_desc'?'selected':'' ?>>Newest</option>
        <option value="date_asc"    <?= $sort==='date_asc'?'selected':'' ?>>Oldest</option>
        <option value="title_asc"   <?= $sort==='title_asc'?'selected':'' ?>>Title A-Z</option>
        <option value="title_desc"  <?= $sort==='title_desc'?'selected':'' ?>>Title Z-A</option>
        <option value="author_asc"  <?= $sort==='author_asc'?'selected':'' ?>>Author A-Z</option>
        <option value="author_desc" <?= $sort==='author_desc'?'selected':'' ?>>Author Z-A</option>
    </select>
</form>

<!-- ================= FAVORITE ================= -->
<?php if ($favorite): ?>
    <div class="alert alert-success mb-4">
        <strong>⭐ Favorite:</strong><br>
        <?= htmlspecialchars($favorite['title']) ?><br>
        Author: <?= htmlspecialchars($favorite['author_name'] ?? 'N/A') ?><br>
        Publication: <?= htmlspecialchars($favorite['publication_title'] ?? 'N/A') ?>
    </div>
<?php endif; ?>

<!-- ================= ARTICLES ================= -->
<h2>Articles</h2>

<div class="row">
<?php foreach ($articles as $a): ?>
    <div class="col-md-4 mb-3">
        <div class="card h-100">

            <div class="card-body">
                <h5><?= htmlspecialchars($a['title']) ?></h5>

                <p>
                    Author: <?= htmlspecialchars($a['author_name'] ?? 'N/A') ?><br>
                    Publication: <?= htmlspecialchars($a['publication_title'] ?? 'N/A') ?>
                </p>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="articles/edit.php?id=<?= $a['id'] ?>" class="btn btn-warning btn-sm">Edit</a>

                    <form method="post" action="articles/delete.php" style="display:inline;">
                        <input type="hidden" name="id" value="<?= $a['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <button class="btn btn-danger btn-sm">Delete</button>
                    </form>
                <?php endif; ?>

                <a href="articles/favorite.php?id=<?= $a['id'] ?>"
                   class="btn btn-success btn-sm">
                    Favorite
                </a>
            </div>

        </div>
    </div>
<?php endforeach; ?>
</div>

<!-- ================= PAGINATION ================= -->
<nav>
    <ul class="pagination justify-content-center mt-4">

        <?php if ($page > 1): ?>
            <li class="page-item">
                <a class="page-link"
                   href="?search=<?= urlencode($search) ?>&sort=<?= $sort ?>&page=<?= $page-1 ?>">
                    Prev
                </a>
            </li>
        <?php endif; ?>

        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <li class="page-item <?= $p == $page ? 'active' : '' ?>">
                <a class="page-link"
                   href="?search=<?= urlencode($search) ?>&sort=<?= $sort ?>&page=<?= $p ?>">
                    <?= $p ?>
                </a>
            </li>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <li class="page-item">
                <a class="page-link"
                   href="?search=<?= urlencode($search) ?>&sort=<?= $sort ?>&page=<?= $page+1 ?>">
                    Next
                </a>
            </li>
        <?php endif; ?>

    </ul>
</nav>

<?php require 'includes/footer.php'; ?>