<?php
require_once '../includes/db.php';
requireRole('admin');
$pageTitle = 'Manage Categories';
$inSubdir = true;

$msg_success = $msg_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        if ($name) {
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
            $n = mysqli_real_escape_string($conn, $name);
            $s = mysqli_real_escape_string($conn, $slug);
            if (mysqli_query($conn, "INSERT INTO categories (name,slug) VALUES ('$n','$s')"))
                $msg_success = "Category \"$name\" added!";
            else
                $msg_error = "Error: " . mysqli_error($conn) . " (name may already exist)";
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['cat_id'] ?? 0);
        // Set posts in this category to uncategorized
        mysqli_query($conn, "UPDATE posts SET category_id=NULL WHERE category_id=$id");
        mysqli_query($conn, "DELETE FROM categories WHERE id=$id");
        $msg_success = "Category deleted. Posts set to uncategorized.";
    } elseif ($action === 'rename') {
        $id   = (int)($_POST['cat_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        if ($name && $id) {
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
            $n = mysqli_real_escape_string($conn, $name);
            $s = mysqli_real_escape_string($conn, $slug);
            mysqli_query($conn, "UPDATE categories SET name='$n',slug='$s' WHERE id=$id");
            $msg_success = "Category renamed.";
        }
    }
}

$pending_count = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM posts WHERE approval_status='pending'"))['c'];
$categories = mysqli_query($conn, "SELECT c.*, COUNT(p.id) AS post_count FROM categories c LEFT JOIN posts p ON p.category_id=c.id GROUP BY c.id ORDER BY c.name ASC");
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
            <a href="categories.php"   class="nav-link active"><i class="bi bi-tags me-2"></i>Categories</a>
            <a href="manage_users.php" class="nav-link"><i class="bi bi-people me-2"></i>Users</a>
            <a href="manage_posts.php" class="nav-link"><i class="bi bi-file-text me-2"></i>All Posts</a>
            <a href="new_post.php"     class="nav-link"><i class="bi bi-plus-circle me-2"></i>New Post</a>
            <hr>
            <a href="../index.php"     class="nav-link"><i class="bi bi-house me-2"></i>View Site</a>
        </div>
    </div>
    <div class="col-md-9">
        <div class="page-header d-flex justify-content-between align-items-center">
            <h3 class="mb-0"><i class="bi bi-tags"></i> Manage Categories</h3>
        </div>

        <?php if ($msg_success): ?><div class="alert alert-success"><?= $msg_success ?></div><?php endif; ?>
        <?php if ($msg_error):   ?><div class="alert alert-danger"><?= $msg_error ?></div><?php endif; ?>

        <!-- Add new category -->
        <div class="card mb-4">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="bi bi-plus-circle"></i> Add New Category</h6>
                <form method="POST" class="d-flex gap-2">
                    <input type="hidden" name="action" value="add">
                    <input type="text" name="name" class="form-control" placeholder="Category name (e.g. Technology)" required>
                    <button class="btn btn-primary px-4"><i class="bi bi-plus"></i> Add</button>
                </form>
            </div>
        </div>

        <!-- Category list -->
        <div class="table-wrapper">
            <table class="table table-hover mb-0">
                <thead><tr><th>Name</th><th>Slug</th><th>Posts</th><th>Actions</th></tr></thead>
                <tbody>
                <?php if (mysqli_num_rows($categories) === 0): ?>
                    <tr><td colspan="4" class="text-center py-4 text-muted">No categories yet.</td></tr>
                <?php else: ?>
                <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                <tr>
                    <td class="fw-semibold"><?= htmlspecialchars($cat['name']) ?></td>
                    <td><code><?= htmlspecialchars($cat['slug']) ?></code></td>
                    <td><span class="cat-count"><?= $cat['post_count'] ?></span></td>
                    <td>
                        <!-- Rename inline -->
                        <button class="btn btn-warning btn-sm" data-bs-toggle="collapse" data-bs-target="#rename-<?= $cat['id'] ?>">Rename</button>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Delete category? Posts will become uncategorized.')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="cat_id"  value="<?= $cat['id'] ?>">
                            <button class="btn btn-danger btn-sm">Delete</button>
                        </form>
                        <!-- Rename form -->
                        <div class="collapse mt-2" id="rename-<?= $cat['id'] ?>">
                            <form method="POST" class="d-flex gap-2">
                                <input type="hidden" name="action" value="rename">
                                <input type="hidden" name="cat_id"  value="<?= $cat['id'] ?>">
                                <input type="text" name="name" class="form-control form-control-sm" value="<?= htmlspecialchars($cat['name']) ?>" required>
                                <button class="btn btn-success btn-sm">Save</button>
                            </form>
                        </div>
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
