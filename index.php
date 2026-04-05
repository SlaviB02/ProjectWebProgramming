<?php
require 'config/database.php';
require 'includes/header.php';
?>
<h2>Articles</h2>
<div class="row">
<?php foreach($articles as $a): ?>
    <div class="col-md-4 mb-3">
        <div class="card h-100 shadow-sm">
            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($a['title']) ?></h5>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="articles/edit.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                    <a href="articles/delete.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-danger">Delete</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>
<?php require 'includes/footer.php'; ?>