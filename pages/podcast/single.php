<?php

/**
 * Podcast Episode detail page.
 *
 * Loads a `podcasts` row by slug (core/router.php resolves /podcast/{slug}
 * here and sets $GLOBALS['podcast_slug']). Structure/CSS classes mirror
 * pages/success-stories/single.php (.ss-detail-hero, .ss-breadcrumb,
 * .hero-features/.ssd-checklist-wrap checklist component, the
 * "More Success Stories" card-grid pattern) per the project's design
 * requirement that Podcast look like another Success Stories page.
 */

$pdo = db();
$slug = $GLOBALS['podcast_slug'] ?? '';
$episode = ($slug !== '' && $pdo) ? get_podcast($pdo, $slug) : null;

if (!$episode || $episode['status'] !== 'published') {
    http_response_code(404);
    require PAGES_DIR . '/404.php';
    return;
}

/**
 * Decode a JSON repeater column into an array, tolerating null/invalid
 * JSON, and drop any row explicitly marked inactive. Mirrors ss_json() in
 * pages/success-stories/single.php.
 */
function pd_json(?string $raw): array
{
    if (!$raw) {
        return [];
    }
    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return [];
    }
    return array_values(array_filter($decoded, static function ($row) {
        return !is_array($row) || !array_key_exists('active', $row) || (bool) $row['active'];
    }));
}

$active_page = 'resources';

$pd = podcast_card_data($episode);
$key_takeaways = array_column(pd_json($episode['key_takeaways_json']), 'text');
$highlights = array_column(pd_json($episode['highlights_json']), 'text');

// --- Watch More Episodes: 4 most recent published, excluding this one ---
$related_episodes = array_slice(
    get_podcasts($pdo, ['status' => 'published', 'exclude_id' => (int) $episode['id']]),
    0,
    4
);
$related_episodes = array_map('podcast_card_data', $related_episodes);

// --- SEO / breadcrumb ---
$page_title = $episode['meta_title'] !== '' ? $episode['meta_title'] : $episode['title'] . ' - ' . SITE_NAME;
$page_description = $episode['meta_description'] !== '' ? $episode['meta_description'] : $episode['short_description'];
$page_keywords = $episode['meta_keywords'];
$page_og_image = media_url($episode['og_image'] !== '' ? $episode['og_image'] : ($episode['thumbnail_override'] ?: ''));
$canonical = $episode['canonical'] !== '' ? $episode['canonical'] : (defined('SITE_URL') ? rtrim(SITE_URL, '/') : '') . '/podcast/' . $episode['slug'];
$page_schema_json = $episode['schema_json'];
$page_robots = $episode['robots'];
?>

<div class="podcast-page podcast-detail-page">

    <!-- ============== 1. BREADCRUMB + EPISODE HERO ============== -->
    <section class="ss-detail-hero">
        <div class="decor-glow decor-glow--left decor-glow--top" aria-hidden="true"></div>
        <div class="container">

            <div class="row align-items-center g-4 mt-2">
                <div class="col-lg-7">
                    <p class="ss-breadcrumb">
                        <a href="<?= url('/') ?>">Home</a>
                        <span class="sep">/</span>
                        <a href="<?= url('/podcast') ?>">Podcast</a>
                        <span class="sep">/</span>
                        <span><?= e($pd['title']) ?></span>
                    </p>

                    <h1><?= e($pd['title']) ?></h1>

                    <div class="pd-detail-meta">
                        <span class="pd-detail-guest">
                            <i class="fa-light fa-user"></i>
                            <?= e($pd['guest_name']) ?><?php if (!empty($pd['guest_designation'])): ?>,
                                <?= e($pd['guest_designation']) ?><?php endif; ?>
                        </span>
                        <?php if (!empty($pd['publish_date'])): ?>
                            <span class="pd-detail-date"><i class="fa-light fa-calendar-days"></i>
                                <?= e($pd['publish_date']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="service-btns">
                        <a href="https://www.youtube.com/@QuantaltechAI" target="_blank" rel="noopener"
                            class="theme-btn btn-style-one me-3">
                            <span class="btn-title"><i class="fa-brands fa-youtube"></i> Subscribe on YouTube</span>
                        </a>
                    </div>
                </div>
                <div class="col-lg-5">
                    <!-- Same hero visual as the Podcast Main Page
                         (pages/podcast/index.php) — reuses .ss-hero-visual /
                         .pd-hero-mic as-is via the shared .podcast-page
                         wrapper class, so both hero images stay in sync.
                         Placeholder mic graphic — swap for a real photo
                         asset under assets/images/quantal/podcast/ once
                         supplied. -->
                    <div class="ss-hero-visual pd-hero-visual">
                        <svg viewBox="0 0 200 200" class="pd-hero-mic" aria-hidden="true">
                            <rect x="85" y="20" width="30" height="80" rx="15" fill="currentColor" />
                            <path d="M60 90 a40 40 0 0 0 80 0" stroke="currentColor" stroke-width="8" fill="none"
                                stroke-linecap="round" />
                            <line x1="100" y1="130" x2="100" y2="160" stroke="currentColor" stroke-width="8"
                                stroke-linecap="round" />
                            <line x1="70" y1="160" x2="130" y2="160" stroke="currentColor" stroke-width="8"
                                stroke-linecap="round" />
                        </svg>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- ============== 2. EMBEDDED YOUTUBE VIDEO ============== -->
    <?php if (!empty($pd['video_id'])): ?>
        <section class="pb-100">
            <div class="container">
                <div class="pd-video-embed">
                    <iframe src="https://www.youtube-nocookie.com/embed/<?= attr($pd['video_id']) ?>?rel=0"
                        title="<?= attr($pd['title']) ?>" loading="lazy"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen></iframe>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- ============== 3. KEY TAKEAWAYS ============== -->
    <?php if (!empty($key_takeaways)): ?>
        <section class="pb-100 section-bg-3 dark-bg pd-takeaways-section">
            <div class="decor-glow decor-glow--left decor-glow--bottom" aria-hidden="true"></div>
            <div class="line-shape d-none d-xl-block" aria-hidden="true">
                <img src="<?= asset('images/home-1/skills/line-shape.png') ?>" alt="">
            </div>
            <div class="container">
                <div class="section-title text-center mb-70">
                    <div class="sub-title">
                        <svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M6.81319 14.6759C6.83947 14.8971 7.16053 14.8971 7.18681 14.6759L7.40705 12.8197C7.69143 10.4229 9.58112 8.53323 11.9779 8.24884L13.834 8.0286C14.0553 8.00233 14.0553 7.68127 13.834 7.65499L11.9779 7.43475C9.58112 7.15036 7.69143 5.26068 7.40705 2.86391L7.18681 1.00776C7.16053 0.786476 6.83947 0.786476 6.81319 1.00776L6.59296 2.86391C6.30857 5.26068 4.41888 7.15036 2.02209 7.43475L0.165943 7.65499C-0.0553144 7.68127 -0.0553144 8.00233 0.165943 8.0286L2.02209 8.24884C4.41888 8.53323 6.30857 10.4229 6.59296 12.8197L6.81319 14.6759Z"
                                fill="currentColor" />
                        </svg>
                        <span>What You'll Learn</span>
                    </div>
                    <h2 class="title split-text split-in-right">Key Takeaways</h2>
                </div>

                <div class="hero-features ssd-checklist-wrap">
                    <?php foreach ($key_takeaways as $i => $item): ?>
                        <div class="feature-item wow fadeInUp" data-wow-delay="<?= 0.1 + $i * 0.1 ?>s">
                            <div class="icon"><i class="fas fa-check"></i></div>
                            <span><?= e($item) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- ============== 4. EPISODE DESCRIPTION ============== -->
    <?php if (!empty($episode['description_html']) || !empty($episode['short_description'])): ?>
        <section class="pb-100 section-padding">
            <div class="container">
                <div class="row g-4">
                    <div class="col-lg-12">
                        <h2>Episode Description</h2>
                        <?php if (!empty($episode['short_description'])): ?>
                            <p class="wow fadeInUp" style="color:#c7c7c7;line-height:1.9;margin-bottom:18px;font-size:17px;">
                                <?= e($episode['short_description']) ?>
                            </p>
                        <?php endif; ?>
                        <?php if (!empty($episode['description_html'])): ?>
                            <div class="wow fadeInUp" style="color:#c7c7c7;line-height:1.9;"><?= $episode['description_html'] ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- ============== 5. EPISODE HIGHLIGHTS ============== -->
    <?php if (!empty($highlights)): ?>
        <section class="pb-100 dark-bg pd-highlights-section">
            <div class="decor-glow decor-glow--right decor-glow--top" aria-hidden="true"></div>
            <div class="light-bg d-none d-xl-block" aria-hidden="true">
                <img src="<?= asset('images/home-1/skills/light-bg.png') ?>" alt="">
            </div>
            <div class="object-shape tm-gsap-animate-circle d-none d-xl-block" aria-hidden="true">
                <img src="<?= asset('images/home-1/skills/object-shape.png') ?>" alt="">
            </div>
            <div class="container">
                <div class="section-title text-center mb-70">
                    <div class="sub-title">
                        <svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M6.81319 14.6759C6.83947 14.8971 7.16053 14.8971 7.18681 14.6759L7.40705 12.8197C7.69143 10.4229 9.58112 8.53323 11.9779 8.24884L13.834 8.0286C14.0553 8.00233 14.0553 7.68127 13.834 7.65499L11.9779 7.43475C9.58112 7.15036 7.69143 5.26068 7.40705 2.86391L7.18681 1.00776C7.16053 0.786476 6.83947 0.786476 6.81319 1.00776L6.59296 2.86391C6.30857 5.26068 4.41888 7.15036 2.02209 7.43475L0.165943 7.65499C-0.0553144 7.68127 -0.0553144 8.00233 0.165943 8.0286L2.02209 8.24884C4.41888 8.53323 6.30857 10.4229 6.59296 12.8197L6.81319 14.6759Z"
                                fill="currentColor" />
                        </svg>
                        <span>Inside This Episode</span>
                    </div>
                    <h2 class="title split-text split-in-right">Episode Highlights</h2>
                </div>

                <div class="hero-features ssd-checklist-wrap">
                    <?php foreach ($highlights as $i => $item): ?>
                        <div class="feature-item wow fadeInUp" data-wow-delay="<?= 0.1 + $i * 0.1 ?>s">
                            <div class="icon"><i class="fas fa-star"></i></div>
                            <span><?= e($item) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- ============== 6. WATCH MORE EPISODES ============== -->
    <?php if (!empty($related_episodes)): ?>
        <section class="case-wrapper case-one section-padding section-bg-2">

            <div class="shape">
                <img src="<?= asset('images/home-1/case/shape-01.webp') ?>" alt="" class="shape-1 tm-gsap-animate-circle">
                <div class="light-shape"></div>
            </div>

            <div class="auto-container">
                <div class="row g-4">

                    <div class="col-xxl-5 col-lg-6">
                        <div class="left-content">
                            <div class="section-title pb-3 pb-xl-5">
                                <div class="sub-title">
                                    <svg width="14" height="15" viewBox="0 0 14 15" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M6.81319 14.6759C6.83947 14.8971 7.16053 14.8971 7.18681 14.6759L7.40705 12.8197C7.69143 10.4229 9.58112 8.53323 11.9779 8.24884L13.834 8.0286C14.0553 8.00233 14.0553 7.68127 13.834 7.65499L11.9779 7.43475C9.58112 7.15036 7.69143 5.26068 7.40705 2.86391L7.18681 1.00776C7.16053 0.786476 6.83947 0.786476 6.81319 1.00776L6.59296 2.86391C6.30857 5.26068 4.41888 7.15036 2.02209 7.43475L0.165943 7.65499C-0.0553144 7.68127 -0.0553144 8.00233 0.165943 8.0286L2.02209 8.24884C4.41888 8.53323 6.30857 10.4229 6.59296 12.8197L6.81319 14.6759Z"
                                            fill="currentColor" />
                                    </svg>
                                    <span>Keep Listening</span>
                                </div>
                                <h2 class="title split-text split-in-right">
                                    Watch More
                                    <span>Episodes</span>
                                </h2>
                            </div>
                            <a class="theme-btn-main mb-5 mb-xl-0 wow fadeInUp" data-wow-delay=".3s"
                                href="<?= url('/podcast') ?>">
                                <span class="theme-btn-arrow-left"><i class="far fa-long-arrow-right"></i></span>
                                <span class="theme-btn">View All Episodes</span>
                                <span class="theme-btn-arrow-right"><i class="far fa-long-arrow-right"></i></span>
                            </a>
                        </div>
                    </div>

                    <div class="col-xxl-7">
                        <div class="row design-choose-item-wrap">
                            <?php
                            $more_pd_slot_classes = [
                                'pd-card design-choose-item-1',
                                'pd-card style-2 design-choose-item-2',
                                'pd-card style-3 design-choose-item-1',
                                'pd-card style-2 style-3 design-choose-item-2',
                            ];
                            foreach ($related_episodes as $i => $related_episode):
                                $related_col = $i === 0 ? 'col-xxl-7 col-lg-6' : 'col-xxl-5 col-lg-6';
                                $slot_class = $more_pd_slot_classes[$i % count($more_pd_slot_classes)];
                                ?>
                                <div class="<?= $related_col ?>">
                                    <?php
                                    // partials/podcast-card.php expects $episode
                                    $episode = $related_episode;
                                    include PARTIALS_DIR . '/podcast-card.php';
                                    ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- ============== 7. FINAL CTA ============== -->
    <?php include PARTIALS_DIR . '/podcast-cta.php'; ?>

</div>

<?php include PARTIALS_DIR . '/youtube-modal.php'; ?>