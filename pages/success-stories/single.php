<?php

/**
 * Success Story / Case Study detail page.
 *
 * Loads a `success_stories` row by slug (core/router.php resolves
 * /success-stories/{slug} here and sets $GLOBALS['success_story_slug']) and
 * maps it onto the exact variable names the template below already uses,
 * so the markup itself never changes. Every optional section is wrapped in
 * `!empty(...)` so a section with no admin-entered content — including its
 * heading, container and decorative background — is omitted entirely.
 */

$pdo = db();
$slug = $GLOBALS['success_story_slug'] ?? '';
$story = ($slug !== '' && $pdo) ? get_success_story($pdo, $slug) : null;

if (!$story || $story['status'] !== 'published') {
    http_response_code(404);
    require PAGES_DIR . '/404.php';
    return;
}

/**
 * Decode a JSON repeater column into an array, tolerating null/invalid
 * JSON, and drop any row explicitly marked inactive.
 */
function ss_json(?string $raw): array
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

$category_map = [];
foreach (get_success_story_categories($pdo, []) as $sscat) {
    $category_map[(int) $sscat['id']] = $sscat['name'];
}
$category_name = $category_map[(int) ($story['category_id'] ?? 0)] ?? '';

$cs = [
    'category' => $category_name,
    'title' => $story['title'],
    'intro' => $story['excerpt'],
    'image' => media_url($story['featured_image']),
    'client' => $story['company_name'],
    'industry' => $story['industry'],
    'services' => $story['services_provided'],
    'outcome' => $story['outcome_summary'],
    'tech_stack' => array_values(array_filter(array_map('trim', explode(',', (string) $story['tech_stack_summary'])))),
    'third_party' => array_values(array_filter(array_map('trim', explode(',', (string) $story['third_party_services'])))),
];

$cs_body_html = $story['body_html'];

$objectives = ss_json($story['objectives_json']);

$architecture = array_map(static function ($node) {
    if (!empty($node['items']) && is_string($node['items'])) {
        $node['items'] = array_values(array_filter(array_map('trim', explode("\n", $node['items']))));
    }
    return $node;
}, ss_json($story['architecture_json']));

$challenge = [
    'sub' => $story['challenge_sub'],
    'title' => $story['challenge_title'],
    'text' => $story['challenge_html'],
    'image' => $story['challenge_image'],
];

$solution = [
    'sub' => $story['solution_sub'],
    'title' => $story['solution_title'],
    'text' => $story['solution_html'],
    'image' => $story['solution_image'],
];

$workflow = ss_json($story['workflow_json']);
$results_impact = ss_json($story['results_json']);
$deliverables = ss_json($story['deliverables_json']);
$tech_stack_list = ss_json($story['tech_stack_items_json']);
$why_cards = ss_json($story['why_cards_json']);
$why_final_html = $story['why_final_html'] ?? '';

$client_responsibilities = [
    'sub' => $story['responsibilities_sub'],
    'title' => $story['responsibilities_title'],
    'intro' => $story['responsibilities_text'],
    'items' => array_column(ss_json($story['responsibilities_json']), 'text'),
];

$future_enhancements = [
    'sub' => $story['future_sub'],
    'title' => $story['future_title'],
    'intro' => $story['future_text'],
    'items' => array_column(ss_json($story['future_json']), 'text'),
];

$final_cta = [
    'sub' => $story['final_cta_sub'],
    'title' => $story['final_cta_title'],
    'desc' => $story['final_cta_desc'],
];

// --- More Success Stories: always the 4 most recently added, excluding the current one ---
$related_stories = array_slice(
    get_success_stories($pdo, ['status' => 'published', 'exclude_id' => (int) $story['id'], 'order' => 'created_at DESC, id DESC']),
    0,
    4
);
$related_stories = array_map(static function ($s) use ($category_map) {
    return [
        'title' => $s['title'],
        'category' => $category_map[(int) ($s['category_id'] ?? 0)] ?? '',
        'client' => $s['company_name'],
        'image' => media_url($s['featured_image']),
        'slug' => $s['slug'],
    ];
}, $related_stories);

// --- SEO / breadcrumb (template-level overrides read by partials/seo-head.php) ---
$crumb = $story['crumb'] !== '' ? $story['crumb'] : $story['title'];
$page_title = $story['meta_title'] !== '' ? $story['meta_title'] : $story['title'] . ' - ' . SITE_NAME;
$page_description = $story['meta_description'] !== '' ? $story['meta_description'] : $story['excerpt'];
$page_keywords = $story['meta_keywords'];
$page_og_image = media_url($story['og_image'] !== '' ? $story['og_image'] : $story['featured_image']);
$canonical = $story['canonical'] !== '' ? $story['canonical'] : (defined('SITE_URL') ? rtrim(SITE_URL, '/') : '') . '/success-stories/' . $story['slug'];
$page_schema_json = $story['schema_json'];
$page_robots = $story['robots'];
?>

<div class="success-stories-page success-story-detail-page">

    <!-- ============== 1. HERO / MAIN SECTION + IMAGE ============== -->
    <section class="ss-detail-hero">
        <div class="decor-glow decor-glow--left decor-glow--top" aria-hidden="true"></div>
        <!-- <div class="decor-glow decor-glow--right decor-glow--bottom" aria-hidden="true"></div> -->
        <div class="container">

            <div class="row align-items-center g-4 mt-2">
                <div class="col-lg-7">
                    <p class="ss-breadcrumb">
                        <a href="<?= url('/') ?>">Home</a>
                        <span class="sep">/</span>
                        <a href="<?= url('/success-stories') ?>">Success Stories</a>
                        <span class="sep">/</span>
                        <span><?= e($crumb) ?></span>
                    </p>

                    <!-- <?php if (!empty($cs['category'])): ?>
                    <span class="ss-featured-category"><?= e($cs['category']) ?></span>
                    <?php endif; ?> -->

                    <h1><?= e($cs['title']) ?></h1>

                    <p class="mb-4" style="color:#c7c7c7;font-size:17px;line-height:30px;max-width:600px;">
                        <?= e($cs['intro']) ?>
                    </p>
                    <div class="service-btns">
                        <a href="<?= url('/contact') ?>" class="theme-btn btn-style-one me-3">
                            <span class="btn-title">Book a Free Scoping Call</span>
                        </a>
                        <a href="<?= url('/success-stories') ?>" class="theme-btn btn-style-border">
                            <span class="btn-title">View Case Studies</span>
                        </a>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="ss-detail-image img-reveal fix mb-0">
                        <img src="<?= attr($cs['image']) ?>" alt="<?= attr($cs['title']) ?>">
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- ============== 2. CONTENT + TAGS + DESCRIPTION ============== -->
    <section class="pb-100">
        <div class="container">
            <div class="row g-4">

                <!-- Left: main introduction/content -->
                <div class="col-lg-7">
                    <h2>Our Client</h2>
                    <p class="wow fadeInUp" style="color:#c7c7c7;line-height:1.9;margin-bottom:18px;font-size:17px;">
                        <?= e($cs['intro']) ?>
                    </p>
                    <?php if (!empty($cs_body_html)): ?>
                        <div class="wow fadeInUp" style="color:#c7c7c7;line-height:1.9;"><?= $cs_body_html ?></div>
                    <?php endif; ?>
                </div>

                <!-- Right: plain label/value information — no cards, boxes,
                     borders or pill styling, clean typography only. -->
                <div class="col-lg-5" style="padding: 20px;background-color: #121212;
">
                    <div class="ssd-plain-info wow fadeInUp" data-wow-delay=".2s">
                        <?php if (!empty($cs['industry'])): ?>
                            <div class="ssd-plain-info-row">
                                <div class="label">Industry</div>
                                <div class="value"><?= e($cs['industry']) ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($cs['services'])): ?>
                            <div class="ssd-plain-info-row">
                                <div class="label">Services Provided</div>
                                <div class="value"><?= e($cs['services']) ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($cs['tech_stack'])): ?>
                            <div class="ssd-plain-info-row">
                                <div class="label">Tech Stack</div>
                                <div class="value"><?= e(implode(', ', $cs['tech_stack'])) ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($cs['third_party'])): ?>
                            <div class="ssd-plain-info-row">
                                <div class="label">3rd Party Services</div>
                                <div class="value"><?= e(implode(', ', $cs['third_party'])) ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($cs['outcome'])): ?>
                            <div class="ssd-plain-info-row">
                                <div class="label">Outcome</div>
                                <div class="value"><?= e($cs['outcome']) ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ============== 3. OBJECTIVES ============== -->
    <?php if (!empty($objectives)): ?>
        <section class="pb-100 section-bg-3 dark-bg">
            <div class="container">
                <div class="section-title text-center mb-70">
                    <div class="sub-title">
                        <svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M6.81319 14.6759C6.83947 14.8971 7.16053 14.8971 7.18681 14.6759L7.40705 12.8197C7.69143 10.4229 9.58112 8.53323 11.9779 8.24884L13.834 8.0286C14.0553 8.00233 14.0553 7.68127 13.834 7.65499L11.9779 7.43475C9.58112 7.15036 7.69143 5.26068 7.40705 2.86391L7.18681 1.00776C7.16053 0.786476 6.83947 0.786476 6.81319 1.00776L6.59296 2.86391C6.30857 5.26068 4.41888 7.15036 2.02209 7.43475L0.165943 7.65499C-0.0553144 7.68127 -0.0553144 8.00233 0.165943 8.0286L2.02209 8.24884C4.41888 8.53323 6.30857 10.4229 6.59296 12.8197L6.81319 14.6759Z"
                                fill="currentColor" />
                        </svg>
                        <span>What We Set Out to Do</span>
                    </div>
                    <h2 class="title split-text split-in-right">Objectives</h2>
                </div>

                <div class="row g-4">
                    <?php foreach ($objectives as $i => $obj): ?>
                        <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="<?= 0.1 + $i * 0.15 ?>s">
                            <div class="why-card">
                                <div class="why-number"><?= sprintf('%02d', $i + 1) ?></div>
                                <h4><?= e($obj['title'] ?? '') ?></h4>
                                <?php if (!empty($obj['desc'])): ?>
                                    <p><?= e($obj['desc']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- ============== 4. PROPOSED ARCHITECTURE ============== -->
    <?php if (!empty($architecture)): ?>
        <section class="pb-100 dark-bg ss-architecture-section">
            <div class="decor-glow decor-glow--right decor-glow--top" aria-hidden="true"></div>
            <!-- Same background-pattern convention as .our-framework-section /
             .benefits-section elsewhere on the site (see assets/css/style.css) —
             reuses the existing line-shape.png asset, not a new image. -->
            <div class="line-shape" aria-hidden="true">
                <img src="<?= asset('images/home-1/features/line-shape.png') ?>" alt="">
            </div>
            <div class="container">
                <div class="section-title text-center mb-70">
                    <div class="sub-title">
                        <svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M6.81319 14.6759C6.83947 14.8971 7.16053 14.8971 7.18681 14.6759L7.40705 12.8197C7.69143 10.4229 9.58112 8.53323 11.9779 8.24884L13.834 8.0286C14.0553 8.00233 14.0553 7.68127 13.834 7.65499L11.9779 7.43475C9.58112 7.15036 7.69143 5.26068 7.40705 2.86391L7.18681 1.00776C7.16053 0.786476 6.83947 0.786476 6.81319 1.00776L6.59296 2.86391C6.30857 5.26068 4.41888 7.15036 2.02209 7.43475L0.165943 7.65499C-0.0553144 7.68127 -0.0553144 8.00233 0.165943 8.0286L2.02209 8.24884C4.41888 8.53323 6.30857 10.4229 6.59296 12.8197L6.81319 14.6759Z"
                                fill="currentColor" />
                        </svg>
                        <span>System Design</span>
                    </div>
                    <h2 class="title split-text split-in-right">Proposed Architecture</h2>
                </div>

                <!-- 5-step flow diagram: numbered card per step, connected by
                 arrows on desktop/tablet and a vertical timeline on mobile.
                 Scoped entirely under .ss-architecture — see CSS. -->
                <div class="ss-architecture">
                    <?php foreach ($architecture as $i => $node): ?>
                        <div class="ss-arch-item wow fadeInUp" data-wow-delay="<?= 0.1 + $i * 0.15 ?>s">
                            <div class="ss-arch-number"><?= $i + 1 ?></div>
                            <div class="ss-arch-connector"></div>
                            <div class="ss-arch-card">
                                <div class="ss-arch-icon"><i class="<?= attr($node['icon']) ?>"></i></div>
                                <h4><?= e($node['title']) ?></h4>
                                <?php if (!empty($node['subtitle'])): ?>
                                    <span class="ss-arch-subtitle"><?= e($node['subtitle']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($node['items'])): ?>
                                    <ul class="ss-arch-list">
                                        <?php foreach ($node['items'] as $item): ?>
                                            <li><?= e($item) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ($i < count($architecture) - 1): ?>
                            <div class="ss-arch-arrow<?= $i === 2 ? ' ss-arch-arrow--break' : '' ?>" aria-hidden="true">
                                <div class="ss-arch-arrow-spacer"></div>
                                <div class="ss-arch-arrow-icon"><i class="far fa-long-arrow-right"></i></div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- ============== 5. THE CHALLENGE ============== -->
    <?php if (!empty($challenge['text'])): ?>
        <section class="pb-100 section-bg-3 dark-bg challenge">
            <div class="decor-glow decor-glow--left decor-glow--top" aria-hidden="true"></div>
            <div class="container">
                <div class="section-title mb-40">
                    <div class="sub-title">
                        <svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M6.81319 14.6759C6.83947 14.8971 7.16053 14.8971 7.18681 14.6759L7.40705 12.8197C7.69143 10.4229 9.58112 8.53323 11.9779 8.24884L13.834 8.0286C14.0553 8.00233 14.0553 7.68127 13.834 7.65499L11.9779 7.43475C9.58112 7.15036 7.69143 5.26068 7.40705 2.86391L7.18681 1.00776C7.16053 0.786476 6.83947 0.786476 6.81319 1.00776L6.59296 2.86391C6.30857 5.26068 4.41888 7.15036 2.02209 7.43475L0.165943 7.65499C-0.0553144 7.68127 -0.0553144 8.00233 0.165943 8.0286L2.02209 8.24884C4.41888 8.53323 6.30857 10.4229 6.59296 12.8197L6.81319 14.6759Z"
                                fill="currentColor" />
                        </svg>
                        <span><?= e($challenge['sub'] !== '' ? $challenge['sub'] : 'The Challenge') ?></span>
                    </div>
                    <?php if (!empty($challenge['title'])): ?>
                        <h2 class="title split-text split-in-right"><?= e($challenge['title']) ?></h2>
                    <?php endif; ?>
                </div>
                <?php if (!empty($challenge['image'])): ?>
                    <div class="ss-detail-image img-reveal fix mb-4" style="max-width:520px;">
                        <img src="<?= attr(media_url($challenge['image'])) ?>" alt="<?= attr($challenge['title']) ?>">
                    </div>
                <?php endif; ?>
                <div class="wow fadeInUp" style="color:#c7c7c7;line-height:1.9;font-size:17px;">
                    <?= $challenge['text'] ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- ============== 6. THE SOLUTION ============== -->
    <?php if (!empty($solution['text'])): ?>
        <section class="pb-100 dark-bg solution">
            <div class="decor-glow decor-glow--right decor-glow--bottom" aria-hidden="true"></div>
            <div class="container">
                <div class="section-title mb-40">
                    <div class="sub-title">
                        <svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M6.81319 14.6759C6.83947 14.8971 7.16053 14.8971 7.18681 14.6759L7.40705 12.8197C7.69143 10.4229 9.58112 8.53323 11.9779 8.24884L13.834 8.0286C14.0553 8.00233 14.0553 7.68127 13.834 7.65499L11.9779 7.43475C9.58112 7.15036 7.69143 5.26068 7.40705 2.86391L7.18681 1.00776C7.16053 0.786476 6.83947 0.786476 6.81319 1.00776L6.59296 2.86391C6.30857 5.26068 4.41888 7.15036 2.02209 7.43475L0.165943 7.65499C-0.0553144 7.68127 -0.0553144 8.00233 0.165943 8.0286L2.02209 8.24884C4.41888 8.53323 6.30857 10.4229 6.59296 12.8197L6.81319 14.6759Z"
                                fill="currentColor" />
                        </svg>
                        <span><?= e($solution['sub'] !== '' ? $solution['sub'] : 'The Solution') ?></span>
                    </div>
                    <?php if (!empty($solution['title'])): ?>
                        <h2 class="title split-text split-in-right"><?= e($solution['title']) ?></h2>
                    <?php endif; ?>
                </div>
                <?php if (!empty($solution['image'])): ?>
                    <div class="ss-detail-image img-reveal fix mb-4" style="max-width:520px;">
                        <img src="<?= attr(media_url($solution['image'])) ?>" alt="<?= attr($solution['title']) ?>">
                    </div>
                <?php endif; ?>
                <div class="wow fadeInUp" style="color:#c7c7c7;line-height:1.9;font-size:17px;">
                    <?= $solution['text'] ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- ============== 7. OUR WORKFLOW ============== -->
    <?php if (!empty($workflow)): ?>
        <section class="process-accordion-section pb-100">
            <div class="decor-glow decor-glow--right decor-glow--top" aria-hidden="true"></div>
            <div class="container">
                <div class="section-title text-center mb-70">
                    <div class="sub-title">
                        <svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M6.81319 14.6759C6.83947 14.8971 7.16053 14.8971 7.18681 14.6759L7.40705 12.8197C7.69143 10.4229 9.58112 8.53323 11.9779 8.24884L13.834 8.0286C14.0553 8.00233 14.0553 7.68127 13.834 7.65499L11.9779 7.43475C9.58112 7.15036 7.69143 5.26068 7.40705 2.86391L7.18681 1.00776C7.16053 0.786476 6.83947 0.786476 6.81319 1.00776L6.59296 2.86391C6.30857 5.26068 4.41888 7.15036 2.02209 7.43475L0.165943 7.65499C-0.0553144 7.68127 -0.0553144 8.00233 0.165943 8.0286L2.02209 8.24884C4.41888 8.53323 6.30857 10.4229 6.59296 12.8197L6.81319 14.6759Z"
                                fill="currentColor" />
                        </svg>
                        <span>Step by Step</span>
                    </div>
                    <h2 class="title split-text split-in-right">Our Workflow</h2>
                </div>

                <div class="accordion process-accordion" id="workflowAccordion">
                    <?php foreach ($workflow as $i => $step): ?>
                        <div class="accordion-item">
                            <div class="timeline-number">
                                <button class="accordion-button <?= $i ? 'collapsed' : '' ?>" data-bs-toggle="collapse"
                                    data-bs-target="#wfStep<?= $i ?>">
                                    <span class="step-circle"><?= sprintf('%02d', $i + 1) ?></span>
                                    <span class="step-heading"><?= e($step['title'] ?? '') ?></span>
                                </button>
                            </div>
                            <div id="wfStep<?= $i ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>"
                                data-bs-parent="#workflowAccordion">
                                <div class="accordion-body">
                                    <p><?= e($step['desc'] ?? '') ?></p>

                                    <?php if (!empty($step['items'])): ?>
                                        <div class="ssd-highlight-card mt-4"
                                            style="border-left-color:var(--theme-color1);padding:24px 26px;">
                                            <div class="eyebrow mb-3">Highlighted Points</div>
                                            <?php foreach ($step['items'] as $wf_point): ?>
                                                <p style="margin-bottom:8px;"><?= e($wf_point) ?></p>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($step['example'])): ?>
                                        <div class="ssd-highlight-card mt-4"
                                            style="border-left-color:var(--theme-color1);padding:24px 26px;">
                                            <div class="eyebrow mb-3"><?= e($step['example']['label']) ?></div>
                                            <?php foreach ($step['example']['rows'] as $row): ?>
                                                <p style="margin-bottom:8px;"><strong
                                                        style="color:#fff;"><?= e($row['label']) ?>:</strong> <?= e($row['value']) ?>
                                                </p>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- ============== 8. RESULTS & IMPACT ============== -->
    <?php if (!empty($results_impact)): ?>
        <section class="benefits-section pb-100 section-bg-3">
            <div class="container">
                <div class="section-title text-center mb-70">
                    <div class="sub-title">
                        <svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M6.81319 14.6759C6.83947 14.8971 7.16053 14.8971 7.18681 14.6759L7.40705 12.8197C7.69143 10.4229 9.58112 8.53323 11.9779 8.24884L13.834 8.0286C14.0553 8.00233 14.0553 7.68127 13.834 7.65499L11.9779 7.43475C9.58112 7.15036 7.69143 5.26068 7.40705 2.86391L7.18681 1.00776C7.16053 0.786476 6.83947 0.786476 6.81319 1.00776L6.59296 2.86391C6.30857 5.26068 4.41888 7.15036 2.02209 7.43475L0.165943 7.65499C-0.0553144 7.68127 -0.0553144 8.00233 0.165943 8.0286L2.02209 8.24884C4.41888 8.53323 6.30857 10.4229 6.59296 12.8197L6.81319 14.6759Z"
                                fill="currentColor" />
                        </svg>
                        <span>Proven Outcome</span>
                    </div>
                    <h2 class="title split-text split-in-right">Results &amp; Impact</h2>
                </div>

                <div class="row g-4">
                    <?php foreach ($results_impact as $i => $result): ?>
                        <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="<?= 0.1 + ($i % 3) * 0.15 ?>s">
                            <div class="benefit-card">
                                <div class="benefit-number"><?= sprintf('%02d', $i + 1) ?></div>
                                <h3><?= e($result['title']) ?></h3>
                                <p><?= e($result['desc']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- ============== 9. WHAT WE DELIVERED ============== -->
    <?php if (!empty($deliverables)): ?>
        <section class="services-grid-section pb-100 dark-bg">
            <div class="container">
                <div class="section-title text-center mb-70">
                    <div class="sub-title">
                        <svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M6.81319 14.6759C6.83947 14.8971 7.16053 14.8971 7.18681 14.6759L7.40705 12.8197C7.69143 10.4229 9.58112 8.53323 11.9779 8.24884L13.834 8.0286C14.0553 8.00233 14.0553 7.68127 13.834 7.65499L11.9779 7.43475C9.58112 7.15036 7.69143 5.26068 7.40705 2.86391L7.18681 1.00776C7.16053 0.786476 6.83947 0.786476 6.81319 1.00776L6.59296 2.86391C6.30857 5.26068 4.41888 7.15036 2.02209 7.43475L0.165943 7.65499C-0.0553144 7.68127 -0.0553144 8.00233 0.165943 8.0286L2.02209 8.24884C4.41888 8.53323 6.30857 10.4229 6.59296 12.8197L6.81319 14.6759Z"
                                fill="currentColor" />
                        </svg>
                        <span>Deliverables</span>
                    </div>
                    <h2 class="title split-text split-in-right">What We Delivered</h2>
                </div>

                <div class="row g-4">
                    <?php foreach ($deliverables as $i => $item): ?>
                        <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="<?= 0.1 + ($i % 3) * 0.15 ?>s">
                            <div class="ml-service-card">
                                <?php if (!empty($item['icon'])): ?>
                                    <div class="service-icon"><i class="<?= attr($item['icon']) ?>"></i></div>
                                <?php endif; ?>
                                <h4><?= e($item['title'] ?? '') ?></h4>
                                <p><?= e($item['desc'] ?? '') ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- ============== 10. OUR TECHNOLOGY STACK ============== -->
    <!-- Editorial numbered list — deliberately not cards/boxes/pills, per
         project instructions. Reuses the site's .section-title header
         pattern and accent typography; the row/divider treatment is new
         (scoped under .ssd-tech-*) since no box-free list component
         existed elsewhere to reuse. -->
    <?php if (!empty($tech_stack_list)): ?>
        <section class="pb-100 section-bg-3 dark-bg">
            <div class="container">
                <div class="section-title text-center mb-70">
                    <div class="sub-title">
                        <svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M6.81319 14.6759C6.83947 14.8971 7.16053 14.8971 7.18681 14.6759L7.40705 12.8197C7.69143 10.4229 9.58112 8.53323 11.9779 8.24884L13.834 8.0286C14.0553 8.00233 14.0553 7.68127 13.834 7.65499L11.9779 7.43475C9.58112 7.15036 7.69143 5.26068 7.40705 2.86391L7.18681 1.00776C7.16053 0.786476 6.83947 0.786476 6.81319 1.00776L6.59296 2.86391C6.30857 5.26068 4.41888 7.15036 2.02209 7.43475L0.165943 7.65499C-0.0553144 7.68127 -0.0553144 8.00233 0.165943 8.0286L2.02209 8.24884C4.41888 8.53323 6.30857 10.4229 6.59296 12.8197L6.81319 14.6759Z"
                                fill="currentColor" />
                        </svg>
                        <span>Under the Hood</span>
                    </div>
                    <h2 class="title split-text split-in-right">Our Technology Stack</h2>
                </div>

                <div class="ssd-tech-list">
                    <?php foreach ($tech_stack_list as $i => $tech): ?>
                        <div class="ssd-tech-row wow fadeInUp" data-wow-delay="<?= 0.1 + $i * 0.1 ?>s">
                            <div class="ssd-tech-number"><?= sprintf('%02d', $i + 1) ?></div>
                            <div class="ssd-tech-name">
                                <?php if (!empty($tech['icon'])): ?><i class="<?= attr($tech['icon']) ?>"></i><?php endif; ?>
                                <?= e($tech['name'] ?? '') ?>
                            </div>
                            <div class="ssd-tech-purpose"><?= e($tech['purpose'] ?? '') ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- ============== 11. WHY CHOOSE OUR SOLUTION ============== -->
    <?php if (!empty($why_cards) || !empty($why_final_html)): ?>
        <section class="why-quantal-section pb-100 dark-bg">
            <div class="about-vector tm-gsap-animate-circle">
                <img src="<?= asset('images/home-1/about/about-vector.png') ?>" alt="">
            </div>
            <div class="decor-glow decor-glow--right decor-glow--bottom" aria-hidden="true"></div>
            <div class="container">
                <div class="section-title text-center mb-70">
                    <div class="sub-title">
                        <svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M6.81319 14.6759C6.83947 14.8971 7.16053 14.8971 7.18681 14.6759L7.40705 12.8197C7.69143 10.4229 9.58112 8.53323 11.9779 8.24884L13.834 8.0286C14.0553 8.00233 14.0553 7.68127 13.834 7.65499L11.9779 7.43475C9.58112 7.15036 7.69143 5.26068 7.40705 2.86391L7.18681 1.00776C7.16053 0.786476 6.83947 0.786476 6.81319 1.00776L6.59296 2.86391C6.30857 5.26068 4.41888 7.15036 2.02209 7.43475L0.165943 7.65499C-0.0553144 7.68127 -0.0553144 8.00233 0.165943 8.0286L2.02209 8.24884C4.41888 8.53323 6.30857 10.4229 6.59296 12.8197L6.81319 14.6759Z"
                                fill="currentColor" />
                        </svg>
                        <span>Why Quantal AI</span>
                    </div>
                    <h2 class="title split-text split-in-right">Why Choose Our Solution</h2>
                </div>

                <div class="row g-4">
                    <?php foreach ($why_cards as $i => $card): ?>
                        <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="<?= 0.1 + $i * 0.15 ?>s">
                            <div class="why-card">
                                <div class="why-number"><?= sprintf('%02d', $i + 1) ?></div>
                                <h4><?= e($card['title'] ?? '') ?></h4>
                                <p><?= e($card['desc'] ?? '') ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (!empty($why_final_html)): ?>
                    <div class="wow fadeInUp mt-4" style="color:#c7c7c7;line-height:1.9;font-size:17px;">
                        <?= $why_final_html ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- ============== 12a. CLIENT RESPONSIBILITIES (standalone) ============== -->
    <?php if (!empty($client_responsibilities['items'])): ?>
        <section class="pb-100 section-bg-3 dark-bg">
            <div class="decor-glow decor-glow--left decor-glow--bottom" aria-hidden="true"></div>
            <div class="container">
                <div class="section-title text-center mb-70">
                    <div class="sub-title">
                        <svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M6.81319 14.6759C6.83947 14.8971 7.16053 14.8971 7.18681 14.6759L7.40705 12.8197C7.69143 10.4229 9.58112 8.53323 11.9779 8.24884L13.834 8.0286C14.0553 8.00233 14.0553 7.68127 13.834 7.65499L11.9779 7.43475C9.58112 7.15036 7.69143 5.26068 7.40705 2.86391L7.18681 1.00776C7.16053 0.786476 6.83947 0.786476 6.81319 1.00776L6.59296 2.86391C6.30857 5.26068 4.41888 7.15036 2.02209 7.43475L0.165943 7.65499C-0.0553144 7.68127 -0.0553144 8.00233 0.165943 8.0286L2.02209 8.24884C4.41888 8.53323 6.30857 10.4229 6.59296 12.8197L6.81319 14.6759Z"
                                fill="currentColor" />
                        </svg>
                        <span><?= e($client_responsibilities['sub'] !== '' ? $client_responsibilities['sub'] : "What's Needed") ?></span>
                    </div>
                    <h2 class="title split-text split-in-right">
                        <?= e($client_responsibilities['title'] !== '' ? $client_responsibilities['title'] : 'Client Responsibilities') ?>
                    </h2>
                    <?php if (!empty($client_responsibilities['intro'])): ?>
                        <div class="text mt-3"><?= e($client_responsibilities['intro']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="hero-features ssd-checklist-wrap">
                    <?php foreach ($client_responsibilities['items'] as $i => $item): ?>
                        <div class="feature-item wow fadeInUp" data-wow-delay="<?= 0.1 + $i * 0.1 ?>s">
                            <div class="icon"><i class="fas fa-check"></i></div>
                            <span><?= e($item) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- ============== 12b. FUTURE ENHANCEMENTS (standalone) ============== -->
    <?php if (!empty($future_enhancements['items'])): ?>
        <section class="pb-100 dark-bg">
            <div class="decor-glow decor-glow--right decor-glow--top" aria-hidden="true"></div>
            <div class="container">
                <div class="section-title text-center mb-70">
                    <div class="sub-title">
                        <svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M6.81319 14.6759C6.83947 14.8971 7.16053 14.8971 7.18681 14.6759L7.40705 12.8197C7.69143 10.4229 9.58112 8.53323 11.9779 8.24884L13.834 8.0286C14.0553 8.00233 14.0553 7.68127 13.834 7.65499L11.9779 7.43475C9.58112 7.15036 7.69143 5.26068 7.40705 2.86391L7.18681 1.00776C7.16053 0.786476 6.83947 0.786476 6.81319 1.00776L6.59296 2.86391C6.30857 5.26068 4.41888 7.15036 2.02209 7.43475L0.165943 7.65499C-0.0553144 7.68127 -0.0553144 8.00233 0.165943 8.0286L2.02209 8.24884C4.41888 8.53323 6.30857 10.4229 6.59296 12.8197L6.81319 14.6759Z"
                                fill="currentColor" />
                        </svg>
                        <span><?= e($future_enhancements['sub'] !== '' ? $future_enhancements['sub'] : "What's Next") ?></span>
                    </div>
                    <h2 class="title split-text split-in-right">
                        <?= e($future_enhancements['title'] !== '' ? $future_enhancements['title'] : 'Our Future Enhancements') ?>
                    </h2>
                    <?php if (!empty($future_enhancements['intro'])): ?>
                        <div class="text mt-3"><?= e($future_enhancements['intro']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="hero-features ssd-checklist-wrap">
                    <?php foreach ($future_enhancements['items'] as $i => $item): ?>
                        <div class="feature-item wow fadeInUp" data-wow-delay="<?= 0.1 + $i * 0.1 ?>s">
                            <div class="icon"><i class="fas fa-arrow-trend-up"></i></div>
                            <span><?= e($item) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- ============== 13. MORE SUCCESS STORIES ============== -->
    <?php if (!empty($related_stories)): ?>
        <section class="case-wrapper case-one section-padding section-bg-2">

            <div class="shape">
                <img src="<?= asset('images/home-1/case/shape-01.webp') ?>" alt="Case Studies - Featured Projects"
                    class="shape-1 tm-gsap-animate-circle">
                <div class="light-shape"></div>
            </div>

            <div class="auto-container">
                <div class="row g-4">

                    <!-- LEFT CONTENT -->
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

                                    <span>Featured Projects</span>
                                </div>

                                <h2 class="title split-text split-in-right">
                                    Success Stories That
                                    <span>Transform Businesses</span>
                                </h2>

                            </div>

                            <a class="theme-btn-main mb-5 mb-xl-0 wow fadeInUp" data-wow-delay=".3s"
                                href="<?= url('/success-stories') ?>">

                                <span class="theme-btn-arrow-left">
                                    <i class="far fa-long-arrow-right"></i>
                                </span>

                                <span class="theme-btn">
                                    View All Case Studies
                                </span>

                                <span class="theme-btn-arrow-right">
                                    <i class="far fa-long-arrow-right"></i>
                                </span>

                            </a>

                        </div>
                    </div>


                    <!-- RIGHT CONTENT - DYNAMIC SUCCESS STORIES -->
                    <div class="col-xxl-7">
                        <div class="row design-choose-item-wrap">

                            <?php

                            // Same 4 positional classes used on Home Page.
                            // These classes control the visual variation
                            // of each Success Story card.
                            $more_ss_slot_classes = [
                                'case-block design-choose-item-1',
                                'case-block style-2 design-choose-item-2',
                                'case-block style-3 design-choose-item-1',
                                'case-block style-2 style-3 design-choose-item-2',
                            ];

                            foreach ($related_stories as $i => $rs):

                                // Convert related story data to the same
                                // structure expected by success-story-card.php
                                $story = [
                                    'title' => $rs['title'],
                                    'category' => $rs['category'],
                                    'image' => $rs['image'],
                                    'url' => url('/success-stories/' . $rs['slug']),
                                ];

                                $slot_class = $more_ss_slot_classes[$i] ?? 'case-block';

                                ?>

                                <div class="col-xl-6 col-lg-6 col-md-6">

                                    <?php include PARTIALS_DIR . '/success-story-card.php'; ?>

                                </div>

                            <?php endforeach; ?>

                        </div>
                    </div>

                </div>
            </div>

        </section>
    <?php endif; ?>

    <!-- ============== 14. FINAL CTA + EXISTING CONTACT FORM ============== -->
    <section class="contact-details pb-100 dark-bg">
        <div class="decor-glow decor-glow--left decor-glow--top" aria-hidden="true"></div>
        <div class="decor-glow decor-glow--right decor-glow--bottom" aria-hidden="true"></div>
        <div class="container">
            <div class="row">

                <!-- Left: CTA content -->
                <div class="col-lg-6">
                    <div class="section-title mb-30">
                        <div class="sub-title">
                            <svg width="14" height="15" viewBox="0 0 14 15" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M6.81319 14.6759C6.83947 14.8971 7.16053 14.8971 7.18681 14.6759L7.40705 12.8197C7.69143 10.4229 9.58112 8.53323 11.9779 8.24884L13.834 8.0286C14.0553 8.00233 14.0553 7.68127 13.834 7.65499L11.9779 7.43475C9.58112 7.15036 7.69143 5.26068 7.40705 2.86391L7.18681 1.00776C7.16053 0.786476 6.83947 0.786476 6.81319 1.00776L6.59296 2.86391C6.30857 5.26068 4.41888 7.15036 2.02209 7.43475L0.165943 7.65499C-0.0553144 7.68127 -0.0553144 8.00233 0.165943 8.0286L2.02209 8.24884C4.41888 8.53323 6.30857 10.4229 6.59296 12.8197L6.81319 14.6759Z"
                                    fill="currentColor" />
                            </svg>
                            <span><?= e($final_cta['sub'] !== '' ? $final_cta['sub'] : 'Ready to Scale?') ?></span>
                        </div>
                        <h2 class="title split-text split-in-right">
                            <?= $final_cta['title'] !== '' ? $final_cta['title'] : 'Let&rsquo;s Build Your <span>AI Outbound Engine</span>' ?>
                        </h2>
                        <?php if (!empty($final_cta['desc'])): ?>
                            <div class="text mt-3"><?= e($final_cta['desc']) ?></div>
                        <?php else: ?>
                            <div class="text mt-3">Have a similar recruitment, sales, or outreach challenge? Let&rsquo;s
                                discuss how we can build an AI-assisted system for your business.</div>
                        <?php endif; ?>
                    </div>
                    <ul class="list-unstyled contact-details__info">
                        <li class="d-block d-sm-flex align-items-sm-center">
                            <div class="icon">
                                <span class="lnr-icon-phone-plus"></span>
                            </div>
                            <div class="text ml-xs--0 mt-xs-10">
                                <h4>Call us</h4>
                                <a href="tel:+13158093225">+1 315 809 3225</a>
                            </div>
                        </li>
                        <li class="d-block d-sm-flex align-items-sm-center">
                            <div class="icon">
                                <span class="lnr-icon-envelope1"></span>
                            </div>
                            <div class="text ml-xs--0 mt-xs-10">
                                <h4>Email us</h4>
                                <a href="mailto:contact@quantaltech.ai">contact@quantaltech.ai</a>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Right: existing Contact Form (shared, site-wide — same component/backend/JS as pages/contact.php) -->
                <div class="col-lg-6">
                    <div id="contact-msg" class="contact-msg" style="display:none;"></div>
                    <form id="contact_form" name="contact_form" action="<?= url('/contact-submit') ?>" method="post">
                        <?= csrf_field() ?>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="mb-3"><input name="form_name" class="form-control" type="text"
                                        placeholder="Enter Name" required></div>
                            </div>
                            <div class="col-sm-6">
                                <div class="mb-3"><input name="form_phone" class="form-control" type="text"
                                        placeholder="Enter Phone"></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="mb-3"><input name="form_email" class="form-control required email"
                                        type="email" placeholder="Enter Email" required></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="mb-3"><input name="form_subject" class="form-control required" type="text"
                                        placeholder="Enter Subject" required></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <textarea name="form_message" class="form-control required" rows="7"
                                placeholder="Enter Message" required></textarea>
                        </div>
                        <div class="mb-5 theme-btn-main">
                            <input name="form_botcheck" type="hidden" value="">
                            <button type="submit" id="contact-submit-btn"
                                class="theme-btn btn-style-one transform"><span class="btn-title">Send
                                    message</span></button>
                            <button type="reset" class="theme-btn btn-style-one transform"><span
                                    class="btn-title">Reset</span></button>
                        </div>
                    </form>
                    <script>
                        (function () {
                            var form = document.getElementById('contact_form');
                            var msgEl = document.getElementById('contact-msg');
                            var btn = document.getElementById('contact-submit-btn');
                            if (!form) return;

                            form.addEventListener('submit', function (e) {
                                e.preventDefault();
                                var origLabel = btn.querySelector('.btn-title').textContent;
                                btn.disabled = true;
                                btn.querySelector('.btn-title').textContent = 'Sending…';

                                fetch(form.action, {
                                    method: 'POST',
                                    body: new FormData(form),
                                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                                })
                                    .then(function (r) { return r.json(); })
                                    .then(function (data) {
                                        msgEl.textContent = data.message;
                                        msgEl.className = 'contact-msg ' + (data.success ? 'contact-msg--ok' : 'contact-msg--err');
                                        msgEl.style.display = 'block';
                                        if (data.success) {
                                            form.reset();
                                            if (data.redirect) {
                                                window.location.href = data.redirect;
                                            }
                                        }
                                    })
                                    .catch(function () {
                                        msgEl.textContent = 'Something went wrong. Please try again.';
                                        msgEl.className = 'contact-msg contact-msg--err';
                                        msgEl.style.display = 'block';
                                    })
                                    .finally(function () {
                                        btn.disabled = false;
                                        btn.querySelector('.btn-title').textContent = origLabel;
                                        msgEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                                    });
                            });
                        })();
                    </script>
                </div>

            </div>
        </div>
    </section>

</div>

<!-- Selective reverse-scroll parallax on the hero image only (reuses the
     GSAP + ScrollTrigger instances already loaded/registered sitewide —
     see assets/js/gsap-custom.js's .tm-gsap-animate-circle for the same
     pattern; no new library, no new global file). Not applied to every
     image on the page, as instructed. -->
<script>
    (function () {
        if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') return;

        function applyReverseParallax(el) {
            gsap.to(el, {
                y: -50,
                ease: 'none',
                scrollTrigger: {
                    trigger: el,
                    start: 'top bottom',
                    end: 'bottom top',
                    scrub: 1
                }
            });
        }

        var el = document.querySelector('.success-story-detail-page .ss-detail-image img');
        if (el) {
            if (el.complete && el.naturalWidth > 0) {
                applyReverseParallax(el);
            } else {
                el.addEventListener('load', function () { applyReverseParallax(el); });
            }
        }
    })();
</script>