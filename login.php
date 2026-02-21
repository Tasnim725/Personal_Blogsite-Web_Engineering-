<?php
require_once 'includes/db.php';
$pageTitle = 'Login - BlogSite';

if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $safe = mysqli_real_escape_string($conn, $username);
        $result = mysqli_query($conn, "SELECT * FROM users WHERE username='$safe' OR email='$safe'");
        $user = mysqli_fetch_assoc($result);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            if ($user['role'] === 'admin') {
                header("Location: admin/dashboard.php");
            } elseif ($user['role'] === 'author') {
                header("Location: author/my_posts.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $error = 'Invalid username or password.';
        }
    } else {
        $error = 'Please fill in all fields.';
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="auth-card">
        <h2><i class="bi bi-box-arrow-in-right"></i> Login</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><i class="bi bi-exclamation-circle"></i> <?= $error ?></div>
        <?php endif; ?>
        
        <?php if (isset($_GET['registered'])): ?>
            <div class="alert alert-success"><i class="bi bi-check-circle"></i> Account created! Please login.</div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label fw-semibold">Username or Email</label>
                <input type="text" name="username" class="form-control" placeholder="Enter username or email" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-box-arrow-in-right"></i> Login</button>
        </form>
        
        <hr>
        <p class="text-center mb-0">Don't have an account? <a href="register.php">Register here</a></p>
        
        <div class="mt-3 p-3 bg-light rounded small">
            <strong>Demo Accounts:</strong><br>
            Admin: admin / admin123<br>
            (Register to create Author/Viewer accounts)
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
