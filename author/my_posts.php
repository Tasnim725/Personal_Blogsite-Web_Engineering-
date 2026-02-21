<?php
require_once '../includes/db.php';
requireRole(['author','admin']);
$pageTitle = 'My Posts';
$inSubdir = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid = (int)($_POST['post_id'] ?? 0);
    $uid = $_SESSION['user_id'];
    if ($_POST['action'] === 'delete') {
        mysqli_query($conn, "DELETE FROM posts WHERE id=$pid AND author_id=$uid");
        $msg_success = "Post deleted.";
    }
}

$uid   = $_SESSION['user_id'];
$posts = mysqli_query($conn, "SELECT * FROM posts WHERE author_id=$uid ORDER BY created_at DESC");
?>
<?php include '../includes/header.php'; ?>

<div class="container my-4">
    <div class="d-flex align-items-center gap-3 mb-4">
        <div class="page-header flex-grow-1 mb-0">
            <h3 class="mb-0"><i class="bi bi-file-text"></i> My Posts</h3>
        </div>
        <a href="new_post.php" class="btn btn-primary"><i class="bi bi-plus"></i> New Post</a>
    </div>

    <?php if (isset($_GET['submitted'])): ?>
        <div class="alert alert-success"><i class="bi bi-check-circle"></i> Post submitted! Waiting for admin approval.</div>
    <?php endif; ?>
    <?php if (isset($msg_success)): ?>
        <div class="alert alert-success"><?= $msg_success ?></div>
    <?php endif; ?>

    <div class="table-wrapper">
        <table class="table table-hover mb-0">
            <thead><tr><th>Title</th><th>Approval</th><th>Visibility</th><th>Date</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if (mysqli_num_rows($posts) === 0): ?>
                <tr><td colspan="5" class="text-center py-4 text-muted">No posts yet. <a href="new_post.php">Write one!</a></td></tr>
            <?php else: ?>
                <?php while ($p = mysqli_fetch_assoc($posts)):
                    $ap = $p['approval_status'] ?? 'pending';
                ?>
                <tr>
                    <td class="fw-semibold"><?= htmlspecialchars(substr($p['title'],0,45)) ?><?= strlen($p['title'])>45?'...':'' ?></td>
                    <td><span class="badge-<?= $ap ?>"><?= ucfirst($ap) ?></span>
                        <?php if ($ap==='rejected' && !empty($p['admin_note'])): ?>
                            <br><small class="text-muted"><?= htmlspecialchars($p['admin_note']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td><span class="badge-<?= $p['status']==='published'?'approved':'draft' ?>"><?= ucfirst($p['status']) ?></span></td>
                    <td><?= date('M d, Y', strtotime($p['created_at'])) ?></td>
                    <td>
                        <?php if ($p['status']==='published' && $ap==='approved'): ?>
                            <a href="../post.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary" target="_blank">View</a>
                        <?php endif; ?>
                        <a href="edit_post.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Delete this post?')">
                            <input type="hidden" name="post_id" value="<?= $p['id'] ?>">
                            <input type="hidden" name="action" value="delete">
                            <button class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
