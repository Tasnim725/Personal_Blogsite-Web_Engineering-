<?php
require_once '../includes/db.php';
requireRole('admin');
$pageTitle = 'Admin Dashboard';
$inSubdir = true;

$total_users = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM users"))['c'];
$total_posts = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM posts"))['c'];
$published   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM posts WHERE status='published'"))['c'];
$pending     = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM posts WHERE approval_status='pending'"))['c'];

$recent = mysqli_query($conn,"SELECT p.*,u.username FROM posts p JOIN users u ON p.author_id=u.id ORDER BY p.created_at DESC LIMIT 6");
?>
<?php include '../includes/header.php'; ?>

<div class="container my-4">
<div class="row g-4">
    <div class="col-md-3">
        <div class="admin-sidebar">
            <h6 class="text-muted text-uppercase mb-3 small">Admin Menu</h6>
            <a href="dashboard.php"    class="nav-link active"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
            <a href="approvals.php"    class="nav-link"><i class="bi bi-check2-circle me-2"></i>Approvals
                <?php if ($pending > 0): ?><span class="badge bg-danger ms-1"><?= $pending ?></span><?php endif; ?>
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
        <div class="page-header">
            <h3 class="mb-0"><i class="bi bi-speedometer2"></i> Dashboard</h3>
        </div>

        <?php if ($pending > 0): ?>
        <div class="alert alert-warning mb-3">
            <i class="bi bi-clock"></i> <strong><?= $pending ?> post<?= $pending>1?'s':'' ?></strong> waiting for approval.
            <a href="approvals.php" class="ms-2 btn btn-sm btn-warning">Review Now</a>
        </div>
        <?php endif; ?>

        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3"><div class="stat-card"><div class="number"><?= $total_users ?></div><div class="label"><i class="bi bi-people"></i> Users</div></div></div>
            <div class="col-6 col-md-3"><div class="stat-card"><div class="number"><?= $total_posts ?></div><div class="label"><i class="bi bi-file-text"></i> Total Posts</div></div></div>
            <div class="col-6 col-md-3"><div class="stat-card"><div class="number"><?= $published ?></div><div class="label"><i class="bi bi-globe"></i> Published</div></div></div>
            <div class="col-6 col-md-3"><div class="stat-card"><div class="number" style="color:#e17055"><?= $pending ?></div><div class="label"><i class="bi bi-clock"></i> Pending</div></div></div>
        </div>

        <div class="table-wrapper">
            <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">Recent Posts</h6>
                <a href="manage_posts.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <table class="table table-hover mb-0">
                <thead><tr><th>Title</th><th>Author</th><th>Approval</th><th>Date</th><th></th></tr></thead>
                <tbody>
                <?php while ($p = mysqli_fetch_assoc($recent)):
                    $ap = $p['approval_status'] ?? 'pending'; ?>
                <tr>
                    <td class="fw-semibold"><?= htmlspecialchars(substr($p['title'],0,30)) ?>...</td>
                    <td><?= htmlspecialchars($p['username']) ?></td>
                    <td><span class="badge-<?= $ap ?>"><?= ucfirst($ap) ?></span></td>
                    <td><?= date('M d', strtotime($p['created_at'])) ?></td>
                    <td><?php if ($ap==='pending'): ?><a href="approvals.php" class="btn btn-sm btn-warning">Review</a><?php else: ?><a href="edit_post.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a><?php endif; ?></td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>

<?php include '../includes/footer.php'; ?>
