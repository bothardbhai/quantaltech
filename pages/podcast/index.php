<?php

/**
 * Podcasts listing page.
 *
 * Future database integration ready for webinar event management.
 */
$page_title = !empty($page_seo['title']) ? $page_seo['title'] : 'Podcasts - Quantal AI';
$active_page = 'resources';

$pdo = db();
$webinars_per_page = 6;
$current_page = max(1, (int) ($_GET['page'] ?? 1));
$offset = ($current_page - 1) * $webinars_per_page;

$webinars = [];
$total = 0;
$filter = $_GET['filter'] ?? 'upcoming';  // upcoming, past, all

// Placeholder: when podcasts table is added, replace this with live query
// For now, showing template structure
if (false && $pdo) {
    try {
        $where = "status = 'published'";
        if ($filter === 'upcoming') {
            $where .= ' AND scheduled_at > NOW()';
        } elseif ($filter === 'past') {
            $where .= ' AND scheduled_at <= NOW()';
        }

        $total = (int) $pdo->query("SELECT COUNT(*) FROM webinars WHERE $where")->fetchColumn();
        $stmt = $pdo->prepare(
            "SELECT id, slug, title, excerpt, featured_image, featured_alt, speaker_name, speaker_title, 
                    scheduled_at, duration_minutes, registration_url, published_at
             FROM webinars
             WHERE $where
             ORDER BY scheduled_at DESC, id DESC
             LIMIT :lim OFFSET :off"
        );
        $stmt->bindValue(':lim', $webinars_per_page, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $webinars = $stmt->fetchAll();
    } catch (PDOException $e) {
        // tables may not exist yet
    }
}

$total_pages = max(1, (int) ceil($total / $webinars_per_page));
?>

<!-- Page Banner -->
<section class="page-banner news-banner" style="padding:120px 0 80px;background:#1d2327;color:#fff;text-align:center;">
    <div class="container">
        <h1 style="color:#fff;font-size:42px;margin:25px 0 12px;">Podcasts</h1>
        <p style="opacity:0.7;margin:0;">
            <a href="/" style="color:#72aee6;">Home</a> &nbsp;/&nbsp; <a href="#" style="color:#72aee6;">Resources</a> &nbsp;/&nbsp; Podcasts
        </p>
    </div>
</section>

<!-- Filters -->
<section style="padding:40px 0;border-bottom:1px solid rgba(255,255,255,0.1);">
    <div class="container">
        <div style="display:flex;gap:20px;justify-content:center;flex-wrap:wrap;">
            <a href="?filter=upcoming" class="theme-btn" style="<?= $filter === 'upcoming' ? 'background:#2fe7d9;color:#000;' : 'background:transparent;border:1px solid #2fe7d9;' ?>">
                Upcoming
            </a>
            <a href="?filter=past" class="theme-btn" style="<?= $filter === 'past' ? 'background:#2fe7d9;color:#000;' : 'background:transparent;border:1px solid #2fe7d9;' ?>">
                Past Podcasts
            </a>
            <a href="?filter=all" class="theme-btn" style="<?= $filter === 'all' ? 'background:#2fe7d9;color:#000;' : 'background:transparent;border:1px solid #2fe7d9;' ?>">
                All
            </a>
        </div>
    </div>
</section>

<section class="news-wrapper section-padding">
    <div class="container">
        <?php if (empty($webinars)): ?>
            <div style="text-align:center;padding:80px 20px;">
                <h3>No Podcasts Available</h3>
                <p class="text-muted">Our webinar schedule is coming soon. Stay tuned for insights on AI, automation, and digital transformation.</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($webinars as $webinar): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="news-items wow fadeInUp" data-wow-delay=".3s">
                            <div class="news-image" style="position:relative;">
                                <?php if ($webinar['featured_image']): ?>
                                    <img src="<?= attr($webinar['featured_image']) ?>" alt="<?= attr($webinar['featured_alt'] ?: $webinar['title']) ?>" style="width:100%;display:block;">
                                <?php else: ?>
                                    <img src="<?= asset('images/home-1/news/news-1.jpg') ?>" alt="<?= attr($webinar['title']) ?>" style="width:100%;display:block;">
                                <?php endif; ?>
                                <span class="badge" style="position:absolute;top:15px;right:15px;background:#2fe7d9;color:#000;padding:8px 12px;border-radius:4px;font-size:12px;font-weight:600;">
                                    WEBINAR
                                </span>
                            </div>
                            <div class="news-content">
                                <ul class="post-list">
                                    <li>
                                        <i class="fa-light fa-calendar-days"></i>
                                        <?php if ($webinar['scheduled_at']): ?>
                                            <?= e(date('M j, Y', strtotime((string) $webinar['scheduled_at']))) ?>
                                        <?php endif; ?>
                                    </li>
                                    <li>
                                        <i class="fa-light fa-clock"></i>
                                        <?= e($webinar['duration_minutes'] ?? 60) ?> min
                                    </li>
                                </ul>
                                <h4 class="title"><a href="#"><?= e($webinar['title']) ?></a></h4>
                                <p class="speaker" style="color:#2fe7d9;font-size:13px;margin:10px 0;">
                                    <?= e($webinar['speaker_name'] ?? 'Speaker') ?>
                                    <?php if ($webinar['speaker_title']): ?>
                                        <br><span style="color:#999;font-size:12px;"><?= e($webinar['speaker_title']) ?></span>
                                    <?php endif; ?>
                                </p>
                                <p class="text"><?= e($webinar['excerpt']) ?></p>
                                <a href="<?= attr($webinar['registration_url'] ?? '#') ?>" class="read-more" target="_blank">Register <i class="fa-regular fa-arrow-right"></i></a>
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
                                    <a href="?page=<?= $i ?>&filter=<?= attr($filter) ?>" class="page-link"><?= $i ?></a>
                                <?php endif; ?>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<!-- Newsletter CTA -->
<section style="padding:80px 0;background:linear-gradient(135deg, rgba(47,231,217,0.1) 0%, rgba(13,13,13,0) 100%);">
    <div class="container">
        <div style="text-align:center;max-width:600px;margin:0 auto;">
            <h2 style="color:#fff;margin:0 0 15px;">Never Miss a Podcast</h2>
            <p style="color:#ccc;margin:0 0 30px;">Subscribe to our newsletter to get updates on upcoming podcasts, AI insights, and industry trends.</p>
            <form action="/newsletter" method="post" style="display:flex;gap:10px;">
                <input type="email" name="email" placeholder="Enter your email" required style="flex:1;padding:14px 20px;background:rgba(255,255,255,0.05);border:1px solid rgba(47,231,217,0.3);border-radius:6px;color:#fff;outline:none;">
                <button type="submit" class="theme-btn" style="background:#2fe7d9;color:#000;padding:14px 30px;border:none;border-radius:6px;font-weight:600;cursor:pointer;">Subscribe</button>
            </form>
        </div>
    </div>
</section>
