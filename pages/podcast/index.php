<?php

/**
 * Podcast — main listing page.
 *
 * Structure/CSS classes deliberately mirror pages/success-stories/index.php
 * (.podcast-page wrapper reusing .ss-hero and .ss-featured-card building
 * blocks, decor-glow, section-title, etc.) per the project's
 * "Podcast should look like another Success Stories" design requirement.
 * Featured episode and All Episodes grid are pulled from the `podcasts`
 * table (see admin/podcasts.php). Main-page SEO comes from the existing
 * Pages & SEO Master (admin/pages.php), keyed on this page's path — same
 * pattern as success-stories/index.php.
 */

$pdo = db();
$page_title = !empty($page_seo['title']) ? $page_seo['title'] : 'AI Podcast for Business Leaders - Quantal AI';
$active_page = 'resources';

// --- Featured episode: admin featured=1, else most recent published ---
$pd_featured = null;
if ($pdo) {
    $featured_rows = get_podcasts($pdo, ['status' => 'published', 'featured_only' => true]);
    if (empty($featured_rows)) {
        $featured_rows = get_podcasts($pdo, ['status' => 'published']);
    }
    if (!empty($featured_rows)) {
        $pd_featured = podcast_card_data($featured_rows[0]);
    }
}

// --- All Episodes grid ---
$pd_episodes = [];
if ($pdo) {
    foreach (get_podcasts($pdo, ['status' => 'published']) as $pd_row) {
        $pd_episodes[] = podcast_card_data($pd_row);
    }
}

// Optional Spotify link — spec asks to add it "if available"; no URL was
// supplied, so the button only renders once this constant is defined
// (e.g. in config), never a guessed/fabricated URL.
$pd_spotify_url = defined('PODCAST_SPOTIFY_URL') ? PODCAST_SPOTIFY_URL : '';
?>

<div class="podcast-page">

    <!-- ============== HERO ============== -->
    <section class="ss-hero">
        <div class="container">

            <div class="ss-hero-left">

                <p class="ss-breadcrumb">
                    <a href="<?= url('/') ?>">Home</a>
                    <span class="sep">/</span>
                    <span>Podcast</span>
                </p>

                <span class="hero-badge">
                    <i class="fa-brands fa-youtube"></i>
                    Quantal AI Podcast
                </span>

                <h1>
                    AI Podcast for <span>Business Leaders</span>
                </h1>

                <p>
                    Welcome to the Quantal AI Podcast where we sit down with CEOs, founders, and operators
                    who are moving beyond AI experimentation and actually deploying AI that delivers real
                    business results. No hype, no theoretical frameworks. Just honest conversations about
                    what it really takes to build and scale AI-powered businesses. A must-listen AI business
                    podcast for leaders who want actionable insights, not empty promises.
                </p>

                <div class="pd-subscribe-row">
                    <a href="https://www.youtube.com/@QuantaltechAI" target="_blank" rel="noopener"
                        class="theme-btn btn-style-one">
                        <span class="btn-title"><i class="fa-brands fa-youtube"></i> Subscribe on YouTube</span>
                    </a>
                    <?php if ($pd_spotify_url !== ''): ?>
                        <a href="<?= attr($pd_spotify_url) ?>" target="_blank" rel="noopener"
                            class="theme-btn btn-style-border">
                            <span class="btn-title"><i class="fa-brands fa-spotify"></i> Listen on Spotify</span>
                        </a>
                    <?php endif; ?>
                </div>

            </div>

            <div class="ss-hero-right">
                <div class="ss-hero-visual pd-hero-visual">
                    <!-- Placeholder mic graphic — swap for a real photo asset
                         under assets/images/quantal/podcast/ once supplied. -->
                    <img src="assets/images/quantal/podcast/podcast-hero.webp" alt="Quantal AI Podcast"
                        class="pd-hero-image">
                </div>
            </div>

        </div>
    </section>

    <!-- ============== FEATURED EPISODE ============== -->
    <?php if (!empty($pd_featured)): ?>
        <section class="ss-featured-section pb-100">
            <div class="container">

                <div class="section-title text-center mb-70">
                    <div class="sub-title">
                        <svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M6.81319 14.6759C6.83947 14.8971 7.16053 14.8971 7.18681 14.6759L7.40705 12.8197C7.69143 10.4229 9.58112 8.53323 11.9779 8.24884L13.834 8.0286C14.0553 8.00233 14.0553 7.68127 13.834 7.65499L11.9779 7.43475C9.58112 7.15036 7.69143 5.26068 7.40705 2.86391L7.18681 1.00776C7.16053 0.786476 6.83947 0.786476 6.81319 1.00776L6.59296 2.86391C6.30857 5.26068 4.41888 7.15036 2.02209 7.43475L0.165943 7.65499C-0.0553144 7.68127 -0.0553144 8.00233 0.165943 8.0286L2.02209 8.24884C4.41888 8.53323 6.30857 10.4229 6.59296 12.8197L6.81319 14.6759Z"
                                fill="currentColor" />
                        </svg>
                        <span>Featured Episode</span>
                    </div>
                    <h2 class="title split-text split-in-right">Our Latest <span>Episode</span></h2>
                </div>

                <div class="ss-featured-card pd-featured-card wow fadeInUp" data-wow-delay=".2s">
                    <div class="row g-0 align-items-center">
                        <div class="col-lg-5 col-md-6 col-12">
                            <div class="ss-featured-image pd-featured-image">
                                <!-- <span class="ss-featured-badge">Featured Episode</span> -->
                                <img src="<?= attr($pd_featured['thumbnail']) ?>" alt="<?= attr($pd_featured['title']) ?>">
                                <?php if (!empty($pd_featured['video_id'])): ?>
                                    <!-- <button type="button" class="pd-play-btn pd-play-btn--lg"
                                        data-video-id="<?= attr($pd_featured['video_id']) ?>"
                                        aria-label="Play episode: <?= attr($pd_featured['title']) ?>">
                                        <i class="fas fa-play"></i>
                                    </button> -->
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-lg-7 col-md-6 col-12">
                            <div class="ss-featured-content">
                                <h3><?= e($pd_featured['title']) ?></h3>
                                <div class="ss-featured-meta">
                                    <span><i
                                            class="fa-light fa-user"></i><?= e($pd_featured['guest_name']) ?><?php if (!empty($pd_featured['guest_designation'])): ?>,
                                            <?= e($pd_featured['guest_designation']) ?>     <?php endif; ?></span>
                                    <?php if (!empty($pd_featured['publish_date'])): ?>
                                        <span><i
                                                class="fa-light fa-calendar-days"></i><?= e($pd_featured['publish_date']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <a href="<?= attr($pd_featured['url']) ?>" class="ss-read-link">
                                    Watch Full Episode <i class="far fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    <?php endif; ?>

    <!-- ============== ALL EPISODES GRID ============== -->
    <section class="ss-projects-section pd-episodes-section pt-100 pb-100 section-bg-3">
        <div class="container">

            <div class="section-title text-center mb-40">
                <div class="sub-title">
                    <svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M6.81319 14.6759C6.83947 14.8971 7.16053 14.8971 7.18681 14.6759L7.40705 12.8197C7.69143 10.4229 9.58112 8.53323 11.9779 8.24884L13.834 8.0286C14.0553 8.00233 14.0553 7.68127 13.834 7.65499L11.9779 7.43475C9.58112 7.15036 7.69143 5.26068 7.40705 2.86391L7.18681 1.00776C7.16053 0.786476 6.83947 0.786476 6.81319 1.00776L6.59296 2.86391C6.30857 5.26068 4.41888 7.15036 2.02209 7.43475L0.165943 7.65499C-0.0553144 7.68127 -0.0553144 8.00233 0.165943 8.0286L2.02209 8.24884C4.41888 8.53323 6.30857 10.4229 6.59296 12.8197L6.81319 14.6759Z"
                            fill="currentColor" />
                    </svg>
                    <span>All Episodes</span>
                </div>
                <h2 class="title split-text split-in-right">Watch All <span>Episodes</span></h2>
            </div>

            <?php if (empty($pd_episodes)): ?>
                <div class="text-center" style="padding:60px 20px;color:#c7c7c7;">
                    <p>No episodes published yet. Check back soon.</p>
                </div>
            <?php else: ?>
                <div class="row g-4 pd-episodes-grid">
                    <?php foreach ($pd_episodes as $i => $episode): ?>
                        <div class="col-lg-3 col-md-6 col-12">
                            <?php
                            $episode_slot = 'pd-card wow fadeInUp';
                            $episode_delay = 0.1 + ($i % 4) * 0.1;
                            ?>
                            <div class="wow fadeInUp" data-wow-delay="<?= $episode_delay ?>s">
                                <?php $slot_class = 'pd-card';
                                include PARTIALS_DIR . '/podcast-card.php'; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
    </section>

    <!-- ============== FINAL CTA ============== -->
    <?php include PARTIALS_DIR . '/podcast-cta.php'; ?>

</div>

<?php include PARTIALS_DIR . '/youtube-modal.php'; ?>