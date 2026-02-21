<?php
require_once '../includes/db.php';
requireRole('admin');
$pageTitle = 'Edit Post - Admin';
$inSubdir = true;

$pending_count = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM posts WHERE approval_status='pending'"))['c'];
$id = (int)($_GET['id'] ?? 0);
$result = mysqli_query($conn,"SELECT * FROM posts WHERE id=$id");
$post = mysqli_fetch_assoc($result);
if (!$post) { header("Location: manage_posts.php"); exit(); }
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $content     = trim($_POST['content'] ?? '');
    $status      = in_array($_POST['status']??'',['published','draft']) ? $_POST['status'] : 'draft';
    $ap          = in_array($_POST['approval_status']??'',['approved','rejected','pending']) ? $_POST['approval_status'] : 'pending';
    $category_id = (int)($_POST['category_id'] ?? 0);
    if (!$title || !$content) { $error = 'Title and content are required.'; }
    else {
        $t   = mysqli_real_escape_string($conn,$title);
        $c   = mysqli_real_escape_string($conn,$content);
        $cid = $category_id ?: 'NULL';
        mysqli_query($conn,"UPDATE posts SET title='$t',content='$c',category_id=$cid,status='$status',approval_status='$ap' WHERE id=$id");
        header("Location: manage_posts.php"); exit();
    }
}
$sel_cat = $_POST['category_id'] ?? $post['category_id'];
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
        <div class="page-header"><h3 class="mb-0"><i class="bi bi-pencil"></i> Edit Post</h3></div>
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
        <div class="card"><div class="card-body p-4">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Post Title</label>
                    <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($_POST['title']??$post['title']) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Category</label>
                    <select name="category_id" class="form-select">
                        <option value="">— Uncategorized —</option>
                        <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                        <option value="<?= $cat['id'] ?>" <?= $sel_cat == $cat['id']?'selected':'' ?>><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Content</label>
                    <textarea name="content" class="form-control" rows="12" required><?= htmlspecialchars($_POST['content']??$post['content']) ?></textarea>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Visibility</label>
                        <select name="status" class="form-select">
                            <option value="draft"     <?= $post['status']==='draft'    ?'selected':'' ?>>Draft</option>
                            <option value="published" <?= $post['status']==='published'?'selected':'' ?>>Published</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Approval Status</label>
                        <select name="approval_status" class="form-select">
                            <option value="pending"  <?= ($post['approval_status']??'')==='pending' ?'selected':'' ?>>Pending</option>
                            <option value="approved" <?= ($post['approval_status']??'')==='approved'?'selected':'' ?>>Approved</option>
                            <option value="rejected" <?= ($post['approval_status']??'')==='rejected'?'selected':'' ?>>Rejected</option>
                        </select>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Update Post</button>
                    <a href="manage_posts.php" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div></div>
    </div>
</div>
</div>
<?php include '../includes/footer.php'; ?>
