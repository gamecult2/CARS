<?php
require_once 'functions.php';
$page_title = 'Blog';
$posts = get_all_blog_posts($pdo);
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
        <div class="page-header">
            <h1>Our Blog</h1>
            <p>News, tips, and insights from our team.</p>
        </div>

        <div class="blog-layout">
            <?php if (empty($posts)): ?>
                <p>No blog posts have been published yet. Check back soon!</p>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <div class="post-card">
                        <h2><?= _e($post['title']) ?></h2>
                        <div class="post-meta">
                            <span>Published on <?= date("F j, Y", strtotime($post['created_at'])) ?></span>
                        </div>
                        <p class="post-excerpt"><?= _e(substr(strip_tags($post['content']), 0, 200)) ?>...</p>
                        <a href="post.php?id=<?= $post['id'] ?>" class="btn">Read More</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <?php require 'partials/footer.php'; ?>
</body>
</html>
