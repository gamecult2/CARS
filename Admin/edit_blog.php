<?php
$page_title = 'Edit Blog Post';
require_once 'partials/header.php';

$post_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$post = ['title' => '', 'content' => '']; // Default values

if ($post_id) {
    $post = get_blog_post($pdo, $post_id);
    if (!$post) {
        redirect('manage_blog.php');
    }
} else {
    $page_title = 'Add New Blog Post';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $author_id = $session_user['id']; // Assumes admin or moderator is logged in

    if (empty($title) || empty($content)) {
        // Handle error
    } else {
        if ($post_id) {
            // Update existing post
            $stmt = $pdo->prepare("UPDATE blog_posts SET title = :title, content = :content WHERE id = :id");
            $stmt->execute([':title' => $title, ':content' => $content, ':id' => $post_id]);
        } else {
            // Create new post
            $stmt = $pdo->prepare("INSERT INTO blog_posts (title, content, author_id) VALUES (:title, :content, :author_id)");
            $stmt->execute([':title' => $title, ':content' => $content, ':author_id' => $author_id]);
        }
        redirect('manage_blog.php?status=success');
    }
}
?>

<form class="admin-form" action="edit_blog.php<?= $post_id ? '?id='.$post_id : '' ?>" method="POST">
    <div class="form-group">
        <label for="title">Title</label>
        <input type="text" id="title" name="title" value="<?= _e($post['title']) ?>" required>
    </div>
    <div class="form-group">
        <label for="content">Content</label>
        <textarea id="content" name="content" rows="10" required><?= _e($post['content']) ?></textarea>
    </div>
    <button type="submit" class="btn"><?= $post_id ? 'Update' : 'Create' ?> Post</button>
</form>

<?php require_once 'partials/footer.php'; ?>
