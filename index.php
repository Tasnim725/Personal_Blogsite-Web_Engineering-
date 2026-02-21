<?php
require_once 'includes/db.php';
$pageTitle = 'BlogSite - Home';

$search   = $_GET['search']   ?? '';
$cat_slug = $_GET['category'] ?? '';

// Build WHERE
$where = "WHERE p.status='published' AND (p.approval_status='approved' OR p.approval_status IS NULL)";
if ($search) {
    $s = mysqli_real_escape_string($conn, $search);
    $where .= " AND (p.title LIKE '%$s%' OR p.content LIKE '%$s%')";
}
$cat_id   = null;
$cat_name = '';
if ($cat_slug) {
    $sl  = mysqli_real_escape_string($conn, $cat_slug);
    $cat = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM categories WHERE slug='$sl'"));
    if ($cat) {
        $cat_id   = $cat['id'];
        $cat_name = $cat['name'];
        $where   .= " AND p.category_id=$cat_id";
    }
}

$posts = mysqli_query($conn, "SELECT p.*, u.username, c.name AS cat_name, c.slug AS cat_slug
    FROM posts p
    JOIN users u ON p.author_id = u.id
    LEFT JOIN categories c ON p.category_id = c.id
    $where ORDER BY p.created_at DESC");

// All categories with post counts
$categories = mysqli_query($conn, "SELECT c.*, COUNT(p.id) AS post_count
    FROM categories c
    LEFT JOIN posts p ON p.category_id=c.id AND p.status='published' AND (p.approval_status='approved' OR p.approval_status IS NULL)
    GROUP BY c.id ORDER BY c.name ASC");
?>
<?php include 'includes/header.php'; ?>

<!-- Hero -->
<div class="hero">
    <div class="container text-center">
        <h1 class="fw-bold"><i class="bi bi-journal-text"></i> Welcome to BlogSite</h1>
        <p class="lead text-muted">Read and share amazing stories</p>
        <form method="GET" class="d-flex justify-content-center mt-3">
            <?php if ($cat_slug): ?>
                <input type="hidden" name="category" value="<?= htmlspecialchars($cat_slug) ?>">
            <?php endif; ?>
            <div class="input-group" style="max-width:500px;">
                <input type="text" name="search" class="form-control" placeholder="Search posts..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Search</button>
            </div>
        </form>
    </div>
</div>

<div class="container mb-5">
    <?php if (isset($_GET['error']) && $_GET['error'] === 'unauthorized'): ?>
        <div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> You don't have permission to access that page.</div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Sidebar: Categories -->
        <div class="col-lg-3">
            <div class="card" style="position:sticky;top:20px;">
                <div class="card-body p-3">
                    <h6 class="fw-bold mb-3" style="color:#2d6cdf;"><i class="bi bi-tag"></i> Categories</h6>
                    <a href="index.php<?= $search ? '?search='.urlencode($search) : '' ?>"
                       class="d-flex justify-content-between align-items-center text-decoration-none py-2 px-2 rounded mb-1 <?= !$cat_slug ? 'active-cat' : 'cat-link' ?>">
                        <span>All Posts</span>
                    </a>
                    <?php
                    mysqli_data_seek($categories, 0);
                    while ($cat = mysqli_fetch_assoc($categories)):
                        $active = ($cat['slug'] === $cat_slug);
                    ?>
                    <a href="?category=<?= urlencode($cat['slug']) ?><?= $search ? '&search='.urlencode($search) : '' ?>"
                       class="d-flex justify-content-between align-items-center text-decoration-none py-2 px-2 rounded mb-1 <?= $active ? 'active-cat' : 'cat-link' ?>">
                        <span><?= htmlspecialchars($cat['name']) ?></span>
                        <span class="cat-count"><?= $cat['post_count'] ?></span>
                    </a>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <!-- Posts -->
        <div class="col-lg-9">
            <!-- Active filters -->
            <?php if ($cat_name || $search): ?>
            <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                <span class="text-muted small">Showing:</span>
                <?php if ($cat_name): ?>
                    <span class="filter-tag"><i class="bi bi-tag"></i> <?= htmlspecialchars($cat_name) ?>
                        <a href="?<?= $search?'search='.urlencode($search):'' ?>" class="ms-1 text-muted">×</a>
                    </span>
                <?php endif; ?>
                <?php if ($search): ?>
                    <span class="filter-tag"><i class="bi bi-search"></i> "<?= htmlspecialchars($search) ?>"
                        <a href="?<?= $cat_slug?'category='.urlencode($cat_slug):'' ?>" class="ms-1 text-muted">×</a>
                    </span>
                <?php endif; ?>
                <a href="index.php" class="btn btn-sm btn-outline-secondary ms-1">Clear All</a>
            </div>
            <?php endif; ?>

            <?php $num = mysqli_num_rows($posts); ?>
            <p class="text-muted small mb-3"><?= $num ?> post<?= $num!=1?'s':'' ?> found</p>

            <?php if ($num === 0): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-journal-x" style="font-size:3rem;opacity:.3;"></i>
                    <p class="mt-2">No posts found. <a href="index.php">View all posts</a></p>
                </div>
            <?php else: ?>
            <div class="row g-3">
                <?php while ($post = mysqli_fetch_assoc($posts)): ?>
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <?php if (!empty($post['cat_name'])): ?>
                            <a href="?category=<?= urlencode($post['cat_slug']) ?>" class="cat-badge mb-2 d-inline-block">
                                <i class="bi bi-tag"></i> <?= htmlspecialchars($post['cat_name']) ?>
                            </a>
                            <?php else: ?>
                            <span class="cat-badge mb-2 d-inline-block" style="opacity:.4;">Uncategorized</span>
                            <?php endif; ?>
                            <h5 class="card-title">
                                <a href="post.php?id=<?= $post['id'] ?>"><?= htmlspecialchars($post['title']) ?></a>
                            </h5>
                            <p class="card-text text-muted small">
                                <?= htmlspecialchars(substr(strip_tags($post['content']), 0, 110)) ?>...
                            </p>
                        </div>
                        <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="bi bi-person"></i> <?= htmlspecialchars($post['username']) ?>
                                &nbsp;·&nbsp;
                                <?= date('M d, Y', strtotime($post['created_at'])) ?>
                            </small>
                            <a href="post.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-primary">Read →</a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
