<?php
require_once '../includes/db.php';
requireRole('admin');
$pageTitle = 'Post Approvals';
$inSubdir = true;

$msg_success = $msg_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid    = (int)($_POST['post_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $note   = mysqli_real_escape_string($conn, trim($_POST['admin_note'] ?? ''));

    if ($action === 'approve') {
        mysqli_query($conn, "UPDATE posts SET approval_status='approved', status='published', admin_note=NULL WHERE id=$pid");
        $msg_success = "Post approved and published!";
    } elseif ($action === 'reject') {
        mysqli_query($conn, "UPDATE posts SET approval_status='rejected', status='draft', admin_note='$note' WHERE id=$pid");
        $msg_success = "Post rejected.";
    }
}

$filter = $_GET['filter'] ?? 'pending';
$allowed = ['pending','approved','rejected','all'];
if (!in_array($filter, $allowed)) $filter = 'pending';

$where = $filter === 'all' ? '' : "WHERE p.approval_status='$filter'";
$posts = mysqli_query($conn, "SELECT p.*,u.username FROM posts p JOIN users u ON p.author_id=u.id $where ORDER BY p.created_at DESC");
$pending_count = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM posts WHERE approval_status='pending'"))['c'];
?>
<?php include '../includes/header.php'; ?>

<div class="container my-4">
<div class="row g-4">
    <div class="col-md-3">
        <div class="admin-sidebar">
            <h6 class="text-muted text-uppercase mb-3 small">Admin Menu</h6>
            <a href="dashboard.php"    class="nav-link"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
            <a href="approvals.php"    class="nav-link active"><i class="bi bi-check2-circle me-2"></i>Approvals
                <?php if ($pending_count > 0): ?><span class="badge bg-danger ms-1"><?= $pending_count ?></span><?php endif; ?>
            </a>
            <a href="categories.php"   class="nav-link"><i class="bi bi-tags me-2"></i>Categories</a>
            <a href="manage_users.php" class="nav-link"><i class="bi bi-people me-2"></i>Users</a>
            <a href="manage_posts.php" class="nav-link"><i class="bi bi-file-text me-2"></i>All Posts</a>
            <a href="new_post.php"     class="nav-link"><i class="bi bi-plus-circle me-2"></i>New Post</a>
            <hr>
            <a href="../index.php"     class="nav-link"><i class="bi bi-house me-2"></i>View Site</a>
        </div>
    </div>
    <div class="col-md-9">
        <div class="page-header d-flex justify-content-between align-items-center">
            <h3 class="mb-0"><i class="bi bi-check2-circle"></i> Post Approvals</h3>
        </div>

        <?php if ($msg_success): ?><div class="alert alert-success"><?= $msg_success ?></div><?php endif; ?>
        <?php if ($msg_error):   ?><div class="alert alert-danger"><?= $msg_error ?></div><?php endif; ?>

        <!-- Filter tabs -->
        <div class="mb-3">
            <a href="?filter=pending"  class="btn btn-sm <?= $filter==='pending' ?'btn-warning':'btn-outline-secondary' ?> me-1">Pending <?php if($pending_count>0) echo "<span class='ms-1'>($pending_count)</span>"; ?></a>
            <a href="?filter=approved" class="btn btn-sm <?= $filter==='approved'?'btn-success':'btn-outline-secondary' ?> me-1">Approved</a>
            <a href="?filter=rejected" class="btn btn-sm <?= $filter==='rejected'?'btn-danger' :'btn-outline-secondary' ?> me-1">Rejected</a>
            <a href="?filter=all"      class="btn btn-sm <?= $filter==='all'     ?'btn-primary':'btn-outline-secondary' ?>">All</a>
        </div>

        <?php if (mysqli_num_rows($posts) === 0): ?>
            <div class="table-wrapper p-5 text-center text-muted">
                <i class="bi bi-inbox" style="font-size:2.5rem;"></i>
                <p class="mt-2">No <?= $filter === 'all' ? '' : $filter ?> posts found.</p>
            </div>
        <?php else: ?>
        <?php while ($p = mysqli_fetch_assoc($posts)):
            $ap = $p['approval_status'] ?? 'pending'; ?>
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h5 class="mb-1 fw-bold"><?= htmlspecialchars($p['title']) ?></h5>
                        <small class="text-muted">
                            <i class="bi bi-person"></i> <?= htmlspecialchars($p['username']) ?>
                            &nbsp;|&nbsp;
                            <i class="bi bi-calendar3"></i> <?= date('M d, Y', strtotime($p['created_at'])) ?>
                        </small>
                    </div>
                    <span class="badge-<?= $ap ?>"><?= ucfirst($ap) ?></span>
                </div>

                <p class="text-muted mb-3" style="font-size:0.95rem;line-height:1.6;">
                    <?= nl2br(htmlspecialchars(substr($p['content'], 0, 300))) ?><?= strlen($p['content'])>300?'...':'' ?>
                </p>

                <?php if (!empty($p['admin_note'])): ?>
                <div class="pending-notice mb-3">
                    <i class="bi bi-chat-left-text"></i> <strong>Admin note:</strong> <?= htmlspecialchars($p['admin_note']) ?>
                </div>
                <?php endif; ?>

                <div class="d-flex gap-2 flex-wrap">
                    <?php if ($ap === 'pending' || $ap === 'rejected'): ?>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="post_id" value="<?= $p['id'] ?>">
                        <input type="hidden" name="action" value="approve">
                        <button class="btn btn-success btn-sm"><i class="bi bi-check-lg"></i> Approve & Publish</button>
                    </form>
                    <?php endif; ?>

                    <?php if ($ap === 'pending' || $ap === 'approved'): ?>
                    <button class="btn btn-danger btn-sm" data-bs-toggle="collapse" data-bs-target="#reject-<?= $p['id'] ?>">
                        <i class="bi bi-x-lg"></i> Reject
                    </button>
                    <?php endif; ?>

                    <a href="edit_post.php?id=<?= $p['id'] ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-pencil"></i> Edit</a>
                </div>

                <!-- Reject form (collapsible) -->
                <div class="collapse mt-3" id="reject-<?= $p['id'] ?>">
                    <form method="POST">
                        <input type="hidden" name="post_id" value="<?= $p['id'] ?>">
                        <input type="hidden" name="action" value="reject">
                        <div class="input-group">
                            <input type="text" name="admin_note" class="form-control" placeholder="Optional: reason for rejection..." value="<?= htmlspecialchars($p['admin_note'] ?? '') ?>">
                            <button class="btn btn-danger"><i class="bi bi-x-lg"></i> Confirm Reject</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>
</div>

<?php include '../includes/footer.php'; ?>
