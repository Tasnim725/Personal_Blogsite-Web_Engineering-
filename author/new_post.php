<?php
require_once '../includes/db.php';
requireRole(['author','admin']);
$pageTitle = 'New Post';
$inSubdir = true;

$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title      = trim($_POST['title'] ?? '');
    $content    = trim($_POST['content'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0) ?: 'NULL';
    if (!$title || !$content) {
        $error = 'Title and content are required.';
    } else {
        $t        = mysqli_real_escape_string($conn, $title);
        $c        = mysqli_real_escape_string($conn, $content);
        $uid      = $_SESSION['user_id'];
        $approval = (getRole() === 'admin') ? 'approved' : 'pending';
        $status   = (getRole() === 'admin') ? 'published' : 'draft';
        $cid      = $category_id === 'NULL' ? 'NULL' : (int)$category_id;
        mysqli_query($conn, "INSERT INTO posts (title,content,author_id,category_id,status,approval_status) VALUES ('$t','$c',$uid,$cid,'$status','$approval')");
        header("Location: my_posts.php?submitted=1");
        exit();
    }
}
?>
<?php include '../includes/header.php'; ?>

<div class="container my-4">
    <div class="page-header"><h3 class="mb-0"><i class="bi bi-plus-circle"></i> Write New Post</h3></div>

    <?php if (getRole() === 'author'): ?>
    <div class="pending-notice mb-3"><i class="bi bi-info-circle"></i> Your post will be sent for <strong>admin approval</strong> before going live.</div>
    <?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

    <div class="card"><div class="card-body p-4">
        <form method="POST">
            <div class="mb-3">
                <label class="form-label fw-semibold">Post Title</label>
                <input type="text" name="title" class="form-control form-control-lg" placeholder="Enter an engaging title..." required value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Category</label>
                <select name="category_id" class="form-select">
                    <option value="">— Select a category —</option>
                    <?php
                    mysqli_data_seek($categories, 0);
                    while ($cat = mysqli_fetch_assoc($categories)):
                    ?>
                    <option value="<?= $cat['id'] ?>" <?= ($_POST['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">Content</label>
                <textarea name="content" class="form-control" rows="14" placeholder="Write your blog post here..." required><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i> Submit for Approval</button>
                <a href="my_posts.php" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div></div>
</div>

<?php include '../includes/footer.php'; ?>
