<?php
require_once '../includes/db.php';
requireRole(['author','admin']);
$pageTitle = 'Edit Post';
$inSubdir = true;

$id  = (int)($_GET['id'] ?? 0);
$uid = $_SESSION['user_id'];
$result = mysqli_query($conn, "SELECT * FROM posts WHERE id=$id AND author_id=$uid");
$post   = mysqli_fetch_assoc($result);
if (!$post) { header("Location: my_posts.php"); exit(); }

$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $content     = trim($_POST['content'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    if (!$title || !$content) {
        $error = 'Title and content are required.';
    } else {
        $t        = mysqli_real_escape_string($conn, $title);
        $c        = mysqli_real_escape_string($conn, $content);
        $approval = (getRole() === 'admin') ? 'approved' : 'pending';
        $status   = (getRole() === 'admin') ? 'published' : 'draft';
        $cid      = $category_id ? $category_id : 'NULL';
        mysqli_query($conn, "UPDATE posts SET title='$t',content='$c',category_id=$cid,status='$status',approval_status='$approval',admin_note=NULL WHERE id=$id AND author_id=$uid");
        header("Location: my_posts.php?submitted=1");
        exit();
    }
}
?>
<?php include '../includes/header.php'; ?>

<div class="container my-4">
    <div class="page-header"><h3 class="mb-0"><i class="bi bi-pencil"></i> Edit Post</h3></div>

    <?php if (getRole() === 'author'): ?>
    <div class="pending-notice mb-3"><i class="bi bi-info-circle"></i> Saving will re-submit the post for <strong>admin approval</strong>.</div>
    <?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

    <div class="card"><div class="card-body p-4">
        <form method="POST">
            <div class="mb-3">
                <label class="form-label fw-semibold">Post Title</label>
                <input type="text" name="title" class="form-control form-control-lg" required value="<?= htmlspecialchars($_POST['title'] ?? $post['title']) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Category</label>
                <select name="category_id" class="form-select">
                    <option value="">— Select a category —</option>
                    <?php
                    mysqli_data_seek($categories, 0);
                    $sel_cat = $_POST['category_id'] ?? $post['category_id'];
                    while ($cat = mysqli_fetch_assoc($categories)):
                    ?>
                    <option value="<?= $cat['id'] ?>" <?= $sel_cat == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">Content</label>
                <textarea name="content" class="form-control" rows="14" required><?= htmlspecialchars($_POST['content'] ?? $post['content']) ?></textarea>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i> Resubmit for Approval</button>
                <a href="my_posts.php" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div></div>
</div>

<?php include '../includes/footer.php'; ?>
