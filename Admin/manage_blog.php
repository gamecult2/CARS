<?php
$page_title = 'Manage Blog Posts';
require_once 'partials/header.php';

// Handle delete request
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    if (is_admin_logged_in()) { // Only admins can delete
        delete_entity($pdo, 'blog_posts', (int)$_GET['id']);
        redirect('manage_blog.php?status=deleted');
    }
}

$posts = get_all_blog_posts($pdo);
?>

<a href="edit_blog.php" class="btn" style="margin-bottom: 20px;">Add New Post</a>

<div class="table-container">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Author ID</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($posts)): ?>
                <tr><td colspan="4" style="text-align:center;">No blog posts found.</td></tr>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <tr>
                        <td><?= _e($post['title']) ?></td>
                        <td><?= _e($post['author_id']) ?></td>
                        <td><?= date("F j, Y", strtotime($post['created_at'])) ?></td>
                        <td class="actions">
                            <a href="edit_blog.php?id=<?= $post['id'] ?>" class="btn btn-sm">Edit</a>
                            <?php if (is_admin_logged_in()): ?>
                                <a href="manage_blog.php?action=delete&id=<?= $post['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this post?');">Delete</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'partials/footer.php'; ?>
