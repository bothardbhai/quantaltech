<?php

/**
 * Public blog single-post page. Slug is in $blog_slug (set by router).
 *
 * Per-post SEO overrides flow through $page_seo so seo-head.php picks them up.
 * Falls back to title/excerpt when meta_title / meta_description are blank.
 */
$slug = $GLOBALS['blog_slug'] ?? '';
$active_page = 'blog';

$post = null;
$pdo = db();

if ($pdo && $slug !== '') {
    try {
        $stmt = $pdo->prepare(
            "SELECT p.*, u.display_name AS author_name
             FROM posts p
             LEFT JOIN users u ON u.id = p.author_id
             WHERE p.slug = :slug AND p.status = 'published'
                   AND (p.published_at IS NULL OR p.published_at <= NOW())
             LIMIT 1"
        );
        $stmt->execute([':slug' => $slug]);
        $post = $stmt->fetch();
    } catch (PDOException $e) {
        $post = null;
    }
}

if (!$post) {
    http_response_code(404);
    $page_title = 'Post Not Found - Quantal AI';
    ?>
    <section class="page-banner" style="padding:120px 0;text-align:center;">
        <div class="container">
            <h1>Post not found</h1>
            <p>The blog post you’re looking for does not exist or has not been published yet.</p>
            <p><a href="/blog" class="theme-btn">Back to blog</a></p>
        </div>
    </section>
    <?php
    return;
}

// FAQs for this post (if any)
$faqs = [];
if ($pdo) {
    try {
        $stmt = $pdo->prepare('SELECT question, answer FROM post_faqs WHERE post_id = :pid ORDER BY sort_order ASC, id ASC');
        $stmt->execute([':pid' => $post['id']]);
        $faqs = $stmt->fetchAll();
    } catch (PDOException $e) {
        $faqs = [];
    }
}

$toc = [];

if (!empty($post['body_html'])) {
    libxml_use_internal_errors(true);

    $dom = new DOMDocument();
    $dom->loadHTML('<?xml encoding="utf-8" ?>' . $post['body_html']);

    $xpath = new DOMXPath($dom);
    $headings = $xpath->query('//h1 | //h2');

    foreach ($headings as $heading) {
        $text = trim($heading->textContent);

        if ($text === '') {
            continue;
        }

        // Generate ID if not already present
        $id = $heading->getAttribute('id');

        if (empty($id)) {
            $id = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $text), '-'));
            $heading->setAttribute('id', $id);
            $heading->setAttribute('style', 'scroll-margin-top:120px;');
        }

        $toc[] = [
            'title' => $text,
            'id' => $id,
            'tag' => strtolower($heading->nodeName),
        ];
    }

    // Save updated HTML with IDs
    $body = $dom->getElementsByTagName('body')->item(0);

    $updatedHtml = '';

    foreach ($body->childNodes as $child) {
        $updatedHtml .= $dom->saveHTML($child);
    }

    $post['body_html'] = $updatedHtml;

    libxml_clear_errors();
}

// Wire per-post SEO into $page_seo so seo-head.php uses it
$page_title = $post['meta_title'] !== '' ? $post['meta_title'] : $post['title'] . ' - ' . SITE_NAME;
$page_seo['title'] = $page_title;
$page_seo['meta_description'] = $post['meta_description'] !== '' ? $post['meta_description'] : $post['excerpt'];
$page_seo['meta_keywords'] = $post['meta_keywords'];
$page_seo['og_image'] = $post['og_image'] !== '' ? $post['og_image'] : $post['featured_image'];
$page_seo['canonical'] = (defined('SITE_URL') ? SITE_URL : '') . '/blog/' . $post['slug'];
$page_seo['schema_json'] = $post['schema_json'];
?>

<!-- Page Banner -->
<section class="page-banner" style="padding:100px 0 60px;background:#1d2327;color:#fff;">
    <div class="container">
        <h1 style="color:#fff;font-size:36px;margin:25px 0 14px;line-height:1.2;"><?= e($post['title']) ?></h1>
        <p style="opacity:0.75;margin:0;font-size:14px;">
            <a href="<?= url('/') ?>" style="color:#72aee6;">Home</a> &nbsp;/&nbsp;
            <a href="<?= url('/blog') ?>" style="color:#72aee6;">Blog</a> &nbsp;/&nbsp;
            <span><?= e($post['title']) ?></span>
        </p>
    </div>
</section>

<section class="news-details-wrapper section-padding">
    <div class="container">
        <div class="row justify-content-center">

            <!-- Sidebar -->
            <div class="col-xl-4 col-lg-4">
                <div class="service-sidebar">
                    <div class="sidebar-widget service-sidebar-single">
                        <div class="blog-toc-card">
                            <div class="blog-toc-title">
                                <div class="blog-toc-icon">
                                    <i class="fas fa-list-ul"></i>
                                </div>
                                <h4>Table of Contents</h4>
                            </div>

                            <ul class="blog-toc-menu">
                                <?php if (!empty($toc)): ?>
                                    <?php foreach ($toc as $item): ?>
                                        <li class="toc-<?= $item['tag']; ?>">
                                            <a href="#<?= attr($item['id']) ?>">
                                                <?= e($item['title']) ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>

                        <div class="service-details-help">
                            <div class="help-shape-1"></div>
                            <div class="help-shape-2"></div>
                            <h2 class="help-title">Talk to <br> us about <br> your project</h2>
                            <div class="help-icon">
                                <span class="lnr-icon-phone-handset"></span>
                            </div>
                            <div class="help-contact">
                                <p>Need help? Talk to an AI expert</p>
                                <a href="tel:+13158093225">+1 315 809 3225</a>
                            </div>
                        </div>

                        <div class="sidebar-widget service-sidebar-single mt-4">
                            <div class="service-sidebar-single-btn wow fadeInUp" data-wow-delay="0.5s" data-wow-duration="1200m">
                                <a href="<?= url('/contact') ?>" class="theme-btn btn-style-one d-grid">
                                    <span class="btn-title"><span class="fas fa-paper-plane"></span> Schedule a Demo</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-8 col-lg-8">
                <article class="news-details">
                    <ul class="post-list" style="list-style:none;padding:0;display:flex;gap:18px;color:#666;font-size:14px;margin-bottom:20px;">
                        <?php if ($post['published_at']): ?>
                            <li>
                                <i class="fa-light fa-calendar-days"></i>
                                <?= e(date('F j, Y', strtotime((string) $post['published_at']))) ?>
                            </li>
                        <?php endif; ?>
                        <?php if (!empty($post['author_name'])): ?>
                            <li>
                                <i class="fa-light fa-user"></i> <?= e($post['author_name']) ?>
                            </li>
                        <?php endif; ?>
                    </ul>

                    <?php if (!empty($post['featured_image'])): ?>
                        <div class="news-image" style="margin-bottom:30px;">
                            <img src="<?= url($post['featured_image']) ?>" alt="<?= attr($post['featured_alt'] ?: $post['title']) ?>" style="width:100%;border-radius:8px;">
                        </div>
                    <?php endif; ?>

                    <div class="news-content rich-text disc">
                        <?= $post['body_html'] /* sanitized at save-time */ ?>
                    </div>

                    <?php if (!empty($faqs)): ?>
                        <div class="post-faqs" style="margin-top:50px;padding-top:30px;border-top:1px solid #eee;">
                            <h3 style="margin:0 0 20px;">Frequently Asked Questions</h3>
                            <?php foreach ($faqs as $faq): ?>
                                <details style="margin-bottom:12px;border:1px solid #eee;border-radius:6px;padding:14px 18px;">
                                    <summary style="cursor:pointer;font-weight:600;"><?= e($faq['question']) ?></summary>
                                    <div style="margin-top:10px;line-height:1.7;"><?= e($faq['answer']) ?></div>
                                </details>
                            <?php endforeach; ?>
                        </div>
                        <?= jsonld([
                            '@context' => 'https://schema.org',
                            '@type' => 'FAQPage',
                            'mainEntity' => array_map(static fn(array $faq) => [
                                '@type' => 'Question',
                                'name' => $faq['question'],
                                'acceptedAnswer' => [
                                    '@type' => 'Answer',
                                    'text' => $faq['answer'],
                                ],
                            ], $faqs),
                        ]) ?>
                    <?php endif; ?>

                    <div style="margin-top:50px;padding-top:30px;border-top:1px solid #eee;">
                        <a href="<?= url('/blog') ?>" class="theme-btn-2">
                            <i class="fa-regular fa-arrow-left"></i> Back to all posts
                        </a>
                    </div>
                </article>
            </div>
        </div>
    </div>
</section>

