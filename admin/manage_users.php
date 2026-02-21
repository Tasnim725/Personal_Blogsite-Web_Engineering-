<?php
require_once '../includes/db.php';
requireRole('admin');
$pageTitle = 'Manage Users';
$inSubdir = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid  = (int)($_POST['user_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if ($uid === $_SESSION['user_id']) { $msg_error = "Cannot modify your own account."; }
    elseif ($action === 'delete') { mysqli_query($conn,"DELETE FROM users WHERE id=$uid"); $msg_success = "User deleted."; }
    elseif ($action === 'change_role') {
        $role = $_POST['role'] ?? 'viewer';
        if (in_array($role,['admin','author','viewer'])) { mysqli_query($conn,"UPDATE users SET role='$role' WHERE id=$uid"); $msg_success = "Role updated."; }
    }
}
$pending_count = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM posts WHERE approval_status='pending'"))['c'];
$users = mysqli_query($conn,"SELECT * FROM users ORDER BY created_at DESC");
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
            <a href="manage_users.php" class="nav-link active"><i class="bi bi-people me-2"></i>Users</a>
            <a href="manage_posts.php" class="nav-link"><i class="bi bi-file-text me-2"></i>All Posts</a>
            <a href="new_post.php"     class="nav-link"><i class="bi bi-plus-circle me-2"></i>New Post</a>
            <hr>
            <a href="../index.php"     class="nav-link"><i class="bi bi-house me-2"></i>View Site</a>
        </div>
    </div>
    <div class="col-md-9">
        <div class="page-header"><h3 class="mb-0"><i class="bi bi-people"></i> Manage Users</h3></div>
        <?php if (isset($msg_success)): ?><div class="alert alert-success"><?= $msg_success ?></div><?php endif; ?>
        <?php if (isset($msg_error)):   ?><div class="alert alert-danger"><?= $msg_error ?></div><?php endif; ?>
        <div class="table-wrapper">
            <table class="table table-hover mb-0">
                <thead><tr><th>Username</th><th>Email</th><th>Role</th><th>Joined</th><th>Actions</th></tr></thead>
                <tbody>
                <?php while ($u = mysqli_fetch_assoc($users)): ?>
                <tr>
                    <td class="fw-semibold"><?= htmlspecialchars($u['username']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><span class="badge-role badge-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
                    <td><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                    <td>
                        <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <input type="hidden" name="action" value="change_role">
                            <select name="role" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                                <option value="viewer" <?= $u['role']==='viewer'?'selected':'' ?>>Viewer</option>
                                <option value="author" <?= $u['role']==='author'?'selected':'' ?>>Author</option>
                                <option value="admin"  <?= $u['role']==='admin' ?'selected':'' ?>>Admin</option>
                            </select>
                        </form>
                        <form method="POST" class="d-inline ms-1" onsubmit="return confirm('Delete user?')">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <input type="hidden" name="action" value="delete">
                            <button class="btn btn-danger btn-sm">Delete</button>
                        </form>
                        <?php else: ?><span class="text-muted small">(You)</span><?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
<?php include '../includes/footer.php'; ?>
