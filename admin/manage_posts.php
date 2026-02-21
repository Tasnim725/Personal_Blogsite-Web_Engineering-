<?php
require_once '../includes/db.php';
requireRole('admin');
$pageTitle = 'Manage Posts';
$inSubdir = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid = (int)($_POST['post_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if ($action === 'delete')  { mysqli_query($conn,"DELETE FROM posts WHERE id=$pid"); $msg_success = "Post deleted."; }
    if ($action === 'approve') { mysqli_query($conn,"UPDATE posts SET approval_status='approved',status='published' WHERE id=$pid"); $msg_success = "Post approved."; }
    if ($action === 'reject')  { mysqli_query($conn,"UPDATE posts SET approval_status='rejected',status='draft' WHERE id=$pid"); $msg_success = "Post rejected."; }
}
$pending_count = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM posts WHERE approval_status='pending'"))['c'];

// Category filter
$cat_filter = (int)($_GET['cat'] ?? 0);
$cat_where  = $cat_filter ? "AND p.category_id=$cat_filter" : '';
$all_cats   = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");

$posts = mysqli_query($conn,"SELECT p.*,u.username,c.name AS cat_name FROM posts p JOIN users u ON p.author_id=u.id LEFT JOIN categories c ON p.category_id=c.id WHERE 1=1 $cat_where ORDER BY p.created_at DESC");
?>
<?php include '../includes/header.php'; ?>
<div class="container my-4">
<div class="row g-4">
    <div class="col-md-3">
        <div class="admin-sidebar">
            <h6 class="text-muted text-uppercase mb-3 small">Admin Menu</h6>
            <a href="dashboard.php"    class="nav-link"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
            <a href="approvals.php"    class="nav-link"><i class="bi bi-check2-circle me-2"></i>Approvals
                <?php if ($pending_count > 0): ?><span class="badge bg-danger ms-1"><?= $pending_count ?></span><?php endif; ?>
            </a>
            <a href="categories.php"   class="nav-link"><i class="bi bi-tags me-2"></i>Categories</a>
            <a href="manage_users.php" class="nav-link"><i class="bi bi-people me-2"></i>Users</a>
            <a href="manage_posts.php" class="nav-link active"><i class="bi bi-file-text me-2"></i>All Posts</a>
            <a href="new_post.php"     class="nav-link"><i class="bi bi-plus-circle me-2"></i>New Post</a>
            <hr>
            <a href="../index.php"     class="nav-link"><i class="bi bi-house me-2"></i>View Site</a>
        </div>
    </div>
    <div class="col-md-9">
        <div class="page-header d-flex justify-content-between align-items-center">
            <h3 class="mb-0"><i class="bi bi-file-text"></i> All Posts</h3>
            <a href="new_post.php" class="btn btn-primary btn-sm"><i class="bi bi-plus"></i> New Post</a>
        </div>
        <?php if (isset($msg_success)): ?><div class="alert alert-success"><?= $msg_success ?></div><?php endif; ?>

        <!-- Category filter -->
        <div class="mb-3 d-flex gap-2 flex-wrap">
            <a href="manage_posts.php" class="btn btn-sm <?= !$cat_filter?'btn-primary':'btn-outline-secondary' ?>">All</a>
            <?php while ($cat = mysqli_fetch_assoc($all_cats)): ?>
            <a href="?cat=<?= $cat['id'] ?>" class="btn btn-sm <?= $cat_filter==$cat['id']?'btn-primary':'btn-outline-secondary' ?>">
                <?= htmlspecialchars($cat['name']) ?>
            </a>
            <?php endwhile; ?>
        </div>

        <div class="table-wrapper">
            <table class="table table-hover mb-0">
                <thead><tr><th>Title</th><th>Author</th><th>Category</th><th>Approval</th><th>Date</th><th>Actions</th></tr></thead>
                <tbody>
                <?php if (mysqli_num_rows($posts) === 0): ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">No posts found.</td></tr>
                <?php else: ?>
                <?php while ($p = mysqli_fetch_assoc($posts)):
                    $ap = $p['approval_status'] ?? 'pending'; ?>
                <tr>
                    <td class="fw-semibold"><?= htmlspecialchars(substr($p['title'],0,28)) ?><?= strlen($p['title'])>28?'...':'' ?></td>
                    <td><?= htmlspecialchars($p['username']) ?></td>
                    <td><?php if ($p['cat_name']): ?><span class="cat-badge"><?= htmlspecialchars($p['cat_name']) ?></span><?php else: ?><span class="text-muted small">—</span><?php endif; ?></td>
                    <td><span class="badge-<?= $ap ?>"><?= ucfirst($ap) ?></span></td>
                    <td><?= date('M d, Y', strtotime($p['created_at'])) ?></td>
                    <td>
                        <a href="edit_post.php?id=<?= $p['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                        <?php if ($ap==='pending'||$ap==='rejected'): ?>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="post_id" value="<?= $p['id'] ?>">
                            <input type="hidden" name="action" value="approve">
                            <button class="btn btn-success btn-sm">Approve</button>
                        </form>
                        <?php endif; ?>
                        <?php if ($ap==='pending'||$ap==='approved'): ?>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="post_id" value="<?= $p['id'] ?>">
                            <input type="hidden" name="action" value="reject">
                            <button class="btn btn-danger btn-sm">Reject</button>
                        </form>
                        <?php endif; ?>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Delete post?')">
                            <input type="hidden" name="post_id" value="<?= $p['id'] ?>">
                            <input type="hidden" name="action" value="delete">
                            <button class="btn btn-outline-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
<?php include '../includes/footer.php'; ?>
