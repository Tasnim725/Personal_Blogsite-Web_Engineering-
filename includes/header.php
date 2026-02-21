<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'BlogSite' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= isset($inSubdir) ? '../' : '' ?>css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="<?= isset($inSubdir) ? '../' : '' ?>index.php">
            <i class="bi bi-pencil-square"></i> BlogSite
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?= isset($inSubdir) ? '../' : '' ?>index.php"><i class="bi bi-house"></i> Home</a>
                </li>
                <?php if (isLoggedIn()): ?>
                    <?php if (getRole() === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= isset($inSubdir) ? '../' : '' ?>admin/dashboard.php"><i class="bi bi-speedometer2"></i> Admin</a>
                        </li>
                    <?php endif; ?>
                    <?php if (getRole() === 'author' || getRole() === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= isset($inSubdir) ? '../' : '' ?>author/new_post.php"><i class="bi bi-plus-circle"></i> New Post</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= isset($inSubdir) ? '../' : '' ?>author/my_posts.php"><i class="bi bi-file-text"></i> My Posts</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['username']) ?>
                            <span class="badge badge-<?= getRole() ?> ms-1"><?= ucfirst(getRole()) ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= isset($inSubdir) ? '../' : '' ?>logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= isset($inSubdir) ? '../' : '' ?>login.php"><i class="bi bi-box-arrow-in-right"></i> Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= isset($inSubdir) ? '../' : '' ?>register.php"><i class="bi bi-person-plus"></i> Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
