<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'blog_db');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$conn) die("DB connection failed: " . mysqli_connect_error());

// Create admin account on first run if it doesn't exist
if (mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM users WHERE username='admin'"))['c'] == 0) {
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $h    = mysqli_real_escape_string($conn, $hash);
    mysqli_query($conn, "INSERT INTO users (username,email,password,role) VALUES ('admin','admin@blog.com','$h','admin')");
}

session_start();

function isLoggedIn() { return isset($_SESSION['user_id']); }
function getRole()    { return $_SESSION['role'] ?? ''; }

function requireLogin() {
    if (!isLoggedIn()) { header("Location: " . (isset($GLOBALS['inSubdir']) ? '../' : '') . "login.php"); exit(); }
}

function requireRole($roles) {
    requireLogin();
    if (!in_array(getRole(), (array)$roles)) { header("Location: " . (isset($GLOBALS['inSubdir']) ? '../' : '') . "index.php?error=unauthorized"); exit(); }
}
