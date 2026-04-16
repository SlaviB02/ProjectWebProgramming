<?php
require 'includes/header.php';

// Външен REST API (Open Library)
$url = "https://openlibrary.org/subjects/science.json?limit=6";

// Вземаме данните от API
$response = file_get_contents($url);

// декодираме JSON → PHP масив
$data = json_decode($response, true);

$books = $data['works'] ?? [];
?>

<div class="container">
    <h2>📚 External API - Science Books</h2>

    <div class="row">
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
                            Published work from Open Library API
                        </p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require 'includes/footer.php'; ?>