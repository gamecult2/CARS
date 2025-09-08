<?php
require_once 'functions.php';

$post_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$post_id) {
    redirect('blog.php');
}

$post = get_blog_post($pdo, $post_id);
if (!$post) {
    redirect('blog.php');
}

$page_title = $post['title'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= _e($page_title) ?> - Car Dealership</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="blog.css">
</head>
<body>
    <?php require 'partials/header.php'; ?>

    <main class="container">
        <div class="post-full-content">
            <div class="page-header">
                <h1><?= _e($post['title']) ?></h1>
                <div class="post-meta">
                    <span>Published on <?= date("F j, Y", strtotime($post['created_at'])) ?></span>
                </div>
            </div>

            <div class="content-body">
                <?= nl2br($post['content']) // Using nl2br to respect line breaks, for a more advanced version a markdown parser would be better ?>
            </div>

            <a href="blog.php" style="margin-top: 30px; display: inline-block;">&larr; Back to Blog</a>
        </div>
    </main>

    <?php require 'partials/footer.php'; ?>
</body>
</html>
