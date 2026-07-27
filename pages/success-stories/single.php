<?php

/**
 * Success Story / Case Study detail page. Slug is in $success_story_slug (set by router).
 *
 * Future database integration ready. Currently shows template.
 */
$slug = $GLOBALS['success_story_slug'] ?? '';
$active_page = 'resources';

$story = null;
$pdo = db();

if ($pdo && $slug !== '') {
    try {
        $stmt = $pdo->prepare(
            "SELECT s.*, u.display_name AS author_name
             FROM success_stories s
             LEFT JOIN users u ON u.id = s.author_id
             WHERE s.slug = :slug AND s.status = 'published'
                   AND (s.published_at IS NULL OR s.published_at <= NOW())
             LIMIT 1"
        );
        $stmt->execute([':slug' => $slug]);
        $story = $stmt->fetch();
    } catch (PDOException $e) {
        $story = null;
    }
}

if (!$story) {
    http_response_code(404);
    $page_title = 'Case Study Not Found - Quantal AI';
    ?>
    <section class="page-banner news-banner" style="padding:120px 0 80px;background:#1d2327;color:#fff;text-align:center;">
        <div class="container">
            <h1>Case Study not found</h1>
            <p>The success story you're looking for does not exist or has not been published yet.</p>
            <p><a href="/success-stories" class="theme-btn">Back to Success Stories</a></p>
        </div>
    </section>
    <?php
    return;
}

// Wire per-story SEO into $page_seo so seo-head.php uses it
$page_title = $story['meta_title'] !== '' ? $story['meta_title'] : $story['title'] . ' - ' . SITE_NAME;
$page_seo['title'] = $page_title;
$page_seo['meta_description'] = $story['meta_description'] !== '' ? $story['meta_description'] : $story['excerpt'];
$page_seo['meta_keywords'] = $story['meta_keywords'];
$page_seo['og_image'] = $story['og_image'] !== '' ? $story['og_image'] : $story['featured_image'];
$page_seo['canonical'] = (defined('SITE_URL') ? SITE_URL : '') . '/success-stories/' . $story['slug'];
$page_seo['schema_json'] = $story['schema_json'];
?>

<!-- Page Banner -->
<section class="page-banner news-banner" style="padding:120px 0 80px;background:#1d2327;color:#fff;text-align:center;">
    <div class="container">
        <h1 style="color:#fff;font-size:36px;margin:0 0 14px;line-height:1.2;"><?= e($story['title']) ?></h1>
        <p style="color:#ccc;margin:0;">
            <span style="margin-right:20px;">
                <i class="fa-light fa-building"></i> <?= e($story['company_name'] ?? 'Client') ?>
            </span>
            <span>
                <i class="fa-light fa-calendar-days"></i>
                <?php if ($story['published_at']): ?>
                    <?= e(date('F j, Y', strtotime((string) $story['published_at']))) ?>
                <?php endif; ?>
            </span>
        </p>
    </div>
</section>

<!-- Case Study Content -->
<section style="padding:80px 0;background:#0a0a0a;">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <?php if ($story['featured_image']): ?>
                    <div style="margin-bottom:40px;border-radius:12px;overflow:hidden;">
                        <img src="<?= attr($story['featured_image']) ?>" alt="<?= attr($story['featured_alt'] ?: $story['title']) ?>" style="width:100%;display:block;">
                    </div>
                <?php endif; ?>
                
                <div class="post-content" style="color:#ccc;line-height:1.8;">
                    <?= $story['body_html'] ?? '<p>Case study details coming soon.</p>' ?>
                </div>
                
                <hr style="border:none;border-top:1px solid rgba(255,255,255,0.1);margin:60px 0;">
                
                <!-- Related CTA -->
                <div style="background:rgba(47,231,217,0.1);padding:40px;border-radius:12px;border-left:4px solid #2fe7d9;">
                    <h3 style="color:#fff;margin:0 0 10px;">Ready to achieve similar results?</h3>
                    <p style="margin:0 0 20px;color:#ccc;">Let's discuss how we can help your organization succeed with AI.</p>
                    <a href="/contact" class="theme-btn" style="display:inline-block;">Get Started</a>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4" style="padding-left:40px;">
                <div style="background:rgba(255,255,255,0.05);padding:30px;border-radius:12px;margin-bottom:30px;">
                    <h4 style="color:#2fe7d9;margin:0 0 20px;text-transform:uppercase;font-size:14px;">Case Study Details</h4>
                    
                    <div style="margin-bottom:20px;border-bottom:1px solid rgba(255,255,255,0.1);padding-bottom:15px;">
                        <p style="color:#999;font-size:12px;margin:0 0 5px;text-transform:uppercase;">Company</p>
                        <p style="color:#fff;margin:0;"><?= e($story['company_name'] ?? 'N/A') ?></p>
                    </div>
                    
                    <div style="margin-bottom:20px;border-bottom:1px solid rgba(255,255,255,0.1);padding-bottom:15px;">
                        <p style="color:#999;font-size:12px;margin:0 0 5px;text-transform:uppercase;">Industry</p>
                        <p style="color:#fff;margin:0;"><?= e($story['industry'] ?? 'N/A') ?></p>
                    </div>
                    
                    <div style="margin-bottom:0;">
                        <p style="color:#999;font-size:12px;margin:0 0 5px;text-transform:uppercase;">Published</p>
                        <p style="color:#fff;margin:0;">
                            <?php if ($story['published_at']): ?>
                                <?= e(date('M j, Y', strtotime((string) $story['published_at']))) ?>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                
                <!-- Back Link -->
                <a href="/success-stories" style="color:#2fe7d9;text-decoration:none;display:inline-flex;align-items:center;gap:8px;">
                    <i class="fa-light fa-arrow-left"></i> Back to Success Stories
                </a>
            </div>
        </div>
    </div>
</section>
