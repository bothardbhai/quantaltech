<?php

/**
 * Public blog index - paginated listing of published posts.
 *
 * Uses theme card markup. The original theme template lives at
 * /pages/blog/_grid_reference.php for visual reference.
 */
$page_title = 'Blog - Quantal AI';
$active_page = 'blog';

$pdo = db();
$posts_per_page = 9;
$current_page = max(1, (int) ($_GET['page'] ?? 1));
$offset = ($current_page - 1) * $posts_per_page;

$posts = [];
$total = 0;

if ($pdo) {
    try {
        $total = (int) $pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'published'")->fetchColumn();
        $stmt = $pdo->prepare(
            "SELECT id, slug, title, excerpt, featured_image, featured_alt, published_at
             FROM posts
             WHERE status = 'published' AND (published_at IS NULL OR published_at <= NOW())
             ORDER BY published_at DESC, id DESC
             LIMIT :lim OFFSET :off"
        );
        $stmt->bindValue(':lim', $posts_per_page, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $posts = $stmt->fetchAll();
    } catch (PDOException $e) {
        // tables may not exist yet
    }
}

$total_pages = max(1, (int) ceil($total / $posts_per_page));
?>

<!-- Page Banner -->
<section class="page-banner news-banner" style="padding:120px 0 80px;background:#1d2327;color:#fff;text-align:center;">
    <div class="container">
        <h1 style="color:#fff;font-size:42px;margin:25px 0 12px;">Blog</h1>
        <p style="opacity:0.7;margin:0;">
            <a href="<?= url('/') ?>" style="color:#72aee6;">Home</a> &nbsp;/&nbsp; Blog
        </p>
    </div>
</section>

<section class="news-wrapper section-padding">
    <div class="container">
        <?php if (empty($posts)): ?>
            <div style="text-align:center;padding:80px 20px;">
                <h3>No posts published yet</h3>
                <p class="text-muted">Check back soon.</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($posts as $post): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="news-items wow fadeInUp" data-wow-delay=".3s">
                            <div class="news-image blog-image">
                                <a href="<?= url('/blog/' . $post['slug']) ?>">
                                    <?php if (!empty($post['featured_image'])): ?>
                                        <img
                                            width="100%"
                                            src="<?= url($post['featured_image']) ?>"
                                            alt="<?= attr($post['featured_alt'] ?: $post['title']) ?>">
                                    <?php else: ?>
                                        <img
                                            src="<?= asset('images/home-1/news/news-1.jpg') ?>"
                                            alt="<?= attr($post['title']) ?>">
                                    <?php endif; ?>
                                </a>
                            </div>
                            <div class="news-content">
                                <ul class="post-list">
                                    <li>
                                        <i class="fa-light fa-calendar-days"></i>
                                        <?php if ($post['published_at']): ?>
                                            <?= e(date('F j, Y', strtotime((string) $post['published_at']))) ?>
                                        <?php endif; ?>
                                    </li>
                                </ul>
                                <h3 class="blog-title">
                                    <a href="<?= url('/blog/' . $post['slug']) ?>">
                                        <?= e($post['title']) ?>
                                    </a>
                                </h3>
                                <?php if ($post['excerpt']): ?>
                                    <p class="blog-excerpt">
                                        <?= e($post['excerpt']) ?>
                                    </p>
                                <?php endif; ?>
                                <a href="<?= url('/blog/' . $post['slug']) ?>" class="theme-btn-2">
                                    Read More <i class="fa-regular fa-arrow-up-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($total_pages > 1): ?>
                <div class="pagination-area" style="text-align:center;margin-top:50px;">
                    <ul style="list-style:none;padding:0;display:inline-flex;gap:8px;">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li>
                                <a href="?page=<?= $i ?>"
                                   style="display:inline-block;padding:8px 14px;border:1px solid #ddd;border-radius:4px;<?= $i === $current_page ? 'background:#1d2327;color:#fff;border-color:#1d2327;' : '' ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
