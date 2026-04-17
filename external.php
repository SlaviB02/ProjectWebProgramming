<?php
require 'includes/header.php';

// ====== Get subject from URL ======
$subject = $_GET['subject'] ?? 'science';

// sanitize (basic safety)
$subject = urlencode($subject);

// ====== External API ======
$url = "https://openlibrary.org/subjects/{$subject}.json?limit=6";

// Fetch data
$response = @file_get_contents($url);
$data = $response ? json_decode($response, true) : [];

$books = $data['works'] ?? [];
?>

<div class="container">
    <h2>📚 External API - <?= htmlspecialchars(ucfirst($_GET['subject'] ?? 'science')) ?> Books</h2>

    <!-- ====== Subject Selector ====== -->
    <form method="get" class="mb-4">
        <div class="input-group">
            <input 
                type="text" 
                name="subject" 
                class="form-control" 
                placeholder="Enter subject (e.g. science, history, fantasy)" 
                value="<?= htmlspecialchars($_GET['subject'] ?? 'science') ?>"
            >
            <button class="btn btn-primary">Search</button>
        </div>
    </form>

    <div class="row">
        <?php if ($books): ?>
            <?php foreach ($books as $book): ?>
                <div class="col-md-4 mb-3">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">
                                <?= htmlspecialchars($book['title'] ?? 'No title') ?>
                            </h5>

                            <p class="card-text">
                                Author: 
                                <?= htmlspecialchars($book['authors'][0]['name'] ?? 'Unknown') ?>
                            </p>

                            <p class="text-muted">
                                Subject: <?= htmlspecialchars($_GET['subject'] ?? 'science') ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No results found.</p>
        <?php endif; ?>
    </div>
</div>

<?php require 'includes/footer.php'; ?>