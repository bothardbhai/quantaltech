<?php

/**
 * Success Stories / Case Studies listing page.
 *
 * Similar structure to blog index. Future database integration ready.
 */
$page_title = 'Success Stories - Quantal AI';
$active_page = 'resources';

$pdo = db();
$stories_per_page = 6;
$current_page = max(1, (int) ($_GET['page'] ?? 1));
$offset = ($current_page - 1) * $stories_per_page;

$stories = [];
$total = 0;

if ($pdo) {
    try {
        $total = (int) $pdo->query("SELECT COUNT(*) FROM success_stories WHERE status = 'published'")->fetchColumn();
        $stmt = $pdo->prepare(
            "SELECT id, slug, title, excerpt, featured_image, featured_alt, company_name, industry, published_at
             FROM success_stories
             WHERE status = 'published' AND (published_at IS NULL OR published_at <= NOW())
             ORDER BY published_at DESC, id DESC
             LIMIT :lim OFFSET :off"
        );
        $stmt->bindValue(':lim', $stories_per_page, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $stories = $stmt->fetchAll();
    } catch (PDOException $e) {
        // tables may not exist yet
    }
}

$total_pages = max(1, (int) ceil($total / $stories_per_page));
?>

<!-- Page Banner -->
<section class="page-banner news-banner" style="padding:120px 0 80px;background:#1d2327;color:#fff;text-align:center;">
    <div class="container">
        <h1 style="color:#fff;font-size:42px;margin:25px 0 12px;">Success Stories</h1>
        <p style="opacity:0.7;margin:0;">
            <a href="/" style="color:#72aee6;">Home</a> &nbsp;/&nbsp; <a href="#" style="color:#72aee6;">Resources</a> &nbsp;/&nbsp; Success Stories
        </p>
    </div>
</section>

<section class="news-wrapper section-padding">
    <div class="container">
        <?php if (empty($stories)): ?>
            <div style="text-align:center;padding:80px 20px;">
                <h3>Success Stories Coming Soon</h3>
                <p class="text-muted">We're showcasing real results from our clients. Check back soon for case studies and success stories.</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($stories as $story): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="news-items wow fadeInUp" data-wow-delay=".3s">
                            <div class="news-image">
                                <a href="/success-stories/<?= attr($story['slug']) ?>">
                                    <?php if ($story['featured_image']): ?>
                                        <img src="<?= attr($story['featured_image']) ?>" alt="<?= attr($story['featured_alt'] ?: $story['title']) ?>">
                                    <?php else: ?>
                                        <img src="<?= asset('images/home-1/news/news-1.jpg') ?>" alt="<?= attr($story['title']) ?>">
                                    <?php endif; ?>
                                </a>
                            </div>
                            <div class="news-content">
                                <ul class="post-list">
                                    <li>
                                        <i class="fa-light fa-building"></i>
                                        <?= e($story['company_name'] ?? 'Case Study') ?>
                                    </li>
                                    <li>
                                        <i class="fa-light fa-tag"></i>
                                        <?= e($story['industry'] ?? 'Industry') ?>
                                    </li>
                                </ul>
                                <h4 class="title"><a href="/success-stories/<?= attr($story['slug']) ?>"><?= e($story['title']) ?></a></h4>
                                <p class="text"><?= e($story['excerpt']) ?></p>
                                <a href="/success-stories/<?= attr($story['slug']) ?>" class="read-more">Read Full Story <i class="fa-regular fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination-wrapper" style="text-align:center;margin-top:60px;">
                    <ul class="pagination">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li>
                                <?php if ($i === $current_page): ?>
                                    <span class="page-link active"><?= $i ?></span>
                                <?php else: ?>
                                    <a href="?page=<?= $i ?>" class="page-link"><?= $i ?></a>
                                <?php endif; ?>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
