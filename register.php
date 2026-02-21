<?php
require_once 'includes/db.php';
$pageTitle = 'Register - BlogSite';

if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'viewer';

    if (!in_array($role, ['author', 'viewer'])) $role = 'viewer';

    if (!$username || !$email || !$password || !$confirm) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $safe_user = mysqli_real_escape_string($conn, $username);
        $safe_email = mysqli_real_escape_string($conn, $email);
        $check = mysqli_query($conn, "SELECT id FROM users WHERE username='$safe_user' OR email='$safe_email'");
        
        if (mysqli_num_rows($check) > 0) {
            $error = 'Username or email already taken.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            mysqli_query($conn, "INSERT INTO users (username, email, password, role) VALUES ('$safe_user', '$safe_email', '$hashed', '$role')");
            header("Location: login.php?registered=1");
            exit();
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="auth-card">
        <h2><i class="bi bi-person-plus"></i> Register</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><i class="bi bi-exclamation-circle"></i> <?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label fw-semibold">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Choose a username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" name="email" class="form-control" placeholder="Enter your email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Min 6 characters" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" placeholder="Repeat password" required>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">Register as</label>
                <select name="role" class="form-select">
                    <option value="viewer">Viewer (Read blogs)</option>
                    <option value="author">Author (Write blogs)</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-person-check"></i> Create Account</button>
        </form>
        
        <hr>
        <p class="text-center mb-0">Already have an account? <a href="login.php">Login here</a></p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
