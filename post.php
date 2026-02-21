<?php
require_once 'includes/db.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header("Location: index.php"); exit(); }

$result = mysqli_query($conn, "SELECT p.*, u.username, c.name AS cat_name, c.slug AS cat_slug
    FROM posts p
    JOIN users u ON p.author_id = u.id
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.id=$id AND p.status='published' AND (p.approval_status='approved' OR p.approval_status IS NULL)");
$post = mysqli_fetch_assoc($result);
if (!$post) { header("Location: index.php"); exit(); }

$pageTitle = htmlspecialchars($post['title']) . ' - BlogSite';
?>
<?php include 'includes/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body p-4 p-md-5">
                    <?php if (!empty($post['cat_name'])): ?>
                    <a href="index.php?category=<?= urlencode($post['cat_slug']) ?>" class="cat-badge mb-3 d-inline-block">
                        <i class="bi bi-tag"></i> <?= htmlspecialchars($post['cat_name']) ?>
                    </a>
                    <?php endif; ?>

                    <h1 class="mb-3 fw-bold"><?= htmlspecialchars($post['title']) ?></h1>

                    <div class="author-info mb-4">
                        <i class="bi bi-person-circle"></i> By <strong><?= htmlspecialchars($post['username']) ?></strong>
                        &nbsp;·&nbsp;
                        <i class="bi bi-calendar3"></i> <?= date('F d, Y', strtotime($post['created_at'])) ?>
                    </div>

                    <hr>
                    <div class="post-content">
                        <?= nl2br(htmlspecialchars($post['content'])) ?>
                    </div>
                    <hr>

                    <?php if (isLoggedIn() && ($_SESSION['user_id'] == $post['author_id'] || getRole() === 'admin')): ?>
                    <a href="<?= getRole()==='admin' ? 'admin/edit_post.php?id=' : 'author/edit_post.php?id=' ?><?= $post['id'] ?>" class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil"></i> Edit Post
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <a href="index.php<?= !empty($post['cat_slug']) ? '?category='.urlencode($post['cat_slug']) : '' ?>" class="btn btn-outline-secondary mt-3">
                <i class="bi bi-arrow-left"></i> <?= !empty($post['cat_name']) ? 'More in '.htmlspecialchars($post['cat_name']) : 'Back to Posts' ?>
            </a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
