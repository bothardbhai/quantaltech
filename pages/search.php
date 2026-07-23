<?php
/**
 * Full search results page — the /search destination the header search
 * form posts to. Serves as the no-JS fallback for the live search
 * dropdown (assets/js/live-search.js), which intercepts the same form for
 * an in-page experience when JS is available.
 */

require_once CORE_DIR . '/search.php';

$raw_query = is_string($_GET['q'] ?? null) ? $_GET['q'] : '';

$page_title  = $raw_query !== '' ? 'Search results for "' . $raw_query . '" - Quantal AI' : 'Search - Quantal AI';
$active_page = '';

$search = search_site(db(), $raw_query, 20);
$query  = $search['query'];
$results = $search['results'];
?>

<!-- Page Banner -->
<section class="page-banner news-banner" style="padding:120px 0 80px;background:#1d2327;color:#fff;text-align:center;">
    <div class="container">
        <h1 style="color:#fff;font-size:36px;margin:0 0 14px;line-height:1.2;">Search</h1>
        <p style="opacity:0.75;margin:0;font-size:14px;">
            <a href="<?= url('/') ?>" style="color:#72aee6;">Home</a> &nbsp;/&nbsp;
            <span>Search</span>
        </p>
    </div>
</section>

<section class="news-wrapper section-padding">
    <div class="container">
        <form method="get" action="<?= url('/search') ?>" style="max-width:520px;margin:0 auto 40px;display:flex;gap:10px;">
            <input type="search" name="q" value="<?= attr($query) ?>" placeholder="Search..." required
                   style="flex:1;padding:14px 20px;border:1px solid #ddd;border-radius:6px;">
            <button type="submit" class="theme-btn">Search</button>
        </form>

        <?php if ($query === ''): ?>
            <div style="text-align:center;padding:40px 20px;">
                <p class="text-muted">Enter a search term above to find pages, services, blog posts, success stories, and podcasts.</p>
            </div>
        <?php elseif (empty($results)): ?>
            <div style="text-align:center;padding:40px 20px;">
                <h3>No results found.</h3>
                <p class="text-muted">Try a different or more general search term.</p>
            </div>
        <?php else: ?>
            <p class="text-muted" style="text-align:center;margin-bottom:30px;">
                <?= count($results) ?> result<?= count($results) === 1 ? '' : 's' ?> for &ldquo;<?= e($query) ?>&rdquo;
            </p>
            <div class="row g-4">
                <?php foreach ($results as $result): ?>
                    <div class="col-lg-6">
                        <div class="news-items wow fadeInUp">
                            <div class="news-content" style="padding:20px 0;">
                                <span class="badge" style="display:inline-block;background:#2fe7d9;color:#000;padding:4px 10px;border-radius:4px;font-size:11px;font-weight:600;margin-bottom:10px;">
                                    <?= e($result['type_label']) ?>
                                </span>
                                <h4 class="title"><a href="<?= attr($result['url']) ?>"><?= e($result['title']) ?></a></h4>
                                <?php if ($result['excerpt'] !== ''): ?>
                                    <p class="text"><?= e($result['excerpt']) ?></p>
                                <?php endif; ?>
                                <a href="<?= attr($result['url']) ?>" class="read-more">View <i class="fa-regular fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
