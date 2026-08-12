<?php

/**
 * Success Stories / Case Studies — main listing page.
 *
 * Categories, the featured project, and the recent-projects grid are pulled
 * from the `success_stories` / `success_story_categories` tables (see
 * admin/success-stories.php / admin/success-story-categories.php). Client
 * logos stay fully static, per project instructions. Main-page SEO (title,
 * description, canonical, schema, robots) is managed separately via the
 * existing Pages & SEO Master (admin/pages.php), keyed on this page's path
 * — $page_seo below already comes from that lookup (see index.php).
 */
$pdo = db();
$page_title = !empty($page_seo['title']) ? $page_seo['title'] : 'Success Stories - Quantal AI';
$active_page = 'resources';

$category_map = [];
$category_slug_map = [];
foreach (get_success_story_categories($pdo, ['status' => 'active']) as $ss_cat) {
    $category_map[(int) $ss_cat['id']] = $ss_cat['name'];
    $category_slug_map[(int) $ss_cat['id']] = $ss_cat['slug'];
}

// --- Category filter bar ("All" is a UI convention, not stored data) -------
$ss_categories = [['label' => 'All', 'filter' => 'all']];
foreach (get_success_story_categories($pdo, ['status' => 'active']) as $ss_cat) {
    $ss_categories[] = ['label' => $ss_cat['name'], 'filter' => $ss_cat['slug']];
}

// --- Client logos (static per project instructions) ------------------------
$ss_client_logos = [
    ['file' => 'icici.png', 'alt' => 'ICICI'],
    ['file' => 'waterfield.png', 'alt' => 'Waterfield'],
    ['file' => 'grip.png', 'alt' => 'Grip'],
    ['file' => 'armstrong.png', 'alt' => 'Armstrong'],
    ['file' => 'elunic.jpg', 'alt' => 'Elunic'],
    ['file' => 'nortmaq.png', 'alt' => 'Nortmaq'],
    ['file' => 'xanevo.jpg', 'alt' => 'Xanevo'],
    ['file' => 'ackuity.jpg', 'alt' => 'Ackuity'],
];

// --- Featured project: the one published story with `featured = 1` --------
$ss_featured = null;
$ss_featured_rows = $pdo ? get_success_stories($pdo, ['status' => 'published', 'featured_only' => true, 'order' => 'updated_at DESC']) : [];
if (!empty($ss_featured_rows)) {
    $fr = $ss_featured_rows[0];
    $ss_featured = [
        'category' => $category_map[(int) ($fr['category_id'] ?? 0)] ?? '',
        'title'    => $fr['title'],
        'excerpt'  => $fr['excerpt'],
        'image'    => media_url($fr['featured_image']),
        'client'   => $fr['company_name'],
        'slug'     => $fr['slug'],
    ];
}

// --- Recent Projects grid ---------------------------------------------------
$ss_projects = [];
if ($pdo) {
    foreach (get_success_stories($pdo, ['status' => 'published', 'order' => 'sort_order ASC, published_at DESC']) as $ss_story) {
        $ss_cid = (int) ($ss_story['category_id'] ?? 0);
        $ss_projects[] = [
            'title'    => $ss_story['title'],
            'category' => $category_map[$ss_cid] ?? '',
            'filter'   => $category_slug_map[$ss_cid] ?? '',
            'excerpt'  => $ss_story['excerpt'],
            'client'   => $ss_story['company_name'],
            'image'    => media_url($ss_story['featured_image']),
            'slug'     => $ss_story['slug'],
        ];
    }
}
?>

<div class="success-stories-page">

    <!-- ============== HERO ============== -->
    <section class="ss-hero">
        <div class="container">

            <div class="ss-hero-left">

                <p class="ss-breadcrumb">
                    <a href="<?= url('/') ?>">Home</a>
                    <span class="sep">/</span>
                    <span>Success Stories</span>
                </p>

                <span class="hero-badge">
                    <i class="bi bi-stars"></i>
                    Proven Results
                </span>

                <h1>
                    Success Stories That <span>Drive Real Business Results</span>
                </h1>

                <p>
                    We have helped businesses across the US turn AI ideas into working systems.
                    Explore the challenges they faced, the solutions we built, and the outcomes
                    we delivered together.
                </p>

                <div class="ss-stats">
                    <div>
                        <div class="ss-stat-number">80+</div>
                        <div class="ss-stat-label">Projects Delivered</div>
                    </div>
                    <div>
                        <div class="ss-stat-number">10+</div>
                        <div class="ss-stat-label">Industries Served</div>
                    </div>
                    <div>
                        <div class="ss-stat-number">100%</div>
                        <div class="ss-stat-label">Job Success on Upwork</div>
                    </div>
                </div>

            </div>

            <div class="ss-hero-right">
                <div class="ss-hero-visual">
                    <span class="ss-visual-dot" aria-hidden="true"></span>
                    <div class="ss-visual-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- ============== CLIENT LOGO CAROUSEL ============== -->
    <div class="brand-section-2 pb-70">
        <div class="container">
            <div class="brand-wrap-2">
                <div class="text-box">
                    <p>Our Trusted Clients</p>
                </div>

                <div class="swiper brand-slider2">
                    <div class="swiper-wrapper">
                        <?php foreach ($ss_client_logos as $logo): ?>
                            <div class="swiper-slide">
                                <div class="brand-img2 image-fluid">
                                    <img src="<?= asset('images/quantal/clients/' . $logo['file']) ?>"
                                        alt="<?= attr($logo['alt']) ?>">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============== FEATURED PROJECT ============== -->
    <?php if (!empty($ss_featured)): ?>
    <section class="ss-featured-section pb-100">
        <div class="container">

            <div class="section-title text-center mb-70">
                <div class="sub-title">
                    <svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M6.81319 14.6759C6.83947 14.8971 7.16053 14.8971 7.18681 14.6759L7.40705 12.8197C7.69143 10.4229 9.58112 8.53323 11.9779 8.24884L13.834 8.0286C14.0553 8.00233 14.0553 7.68127 13.834 7.65499L11.9779 7.43475C9.58112 7.15036 7.69143 5.26068 7.40705 2.86391L7.18681 1.00776C7.16053 0.786476 6.83947 0.786476 6.81319 1.00776L6.59296 2.86391C6.30857 5.26068 4.41888 7.15036 2.02209 7.43475L0.165943 7.65499C-0.0553144 7.68127 -0.0553144 8.00233 0.165943 8.0286L2.02209 8.24884C4.41888 8.53323 6.30857 10.4229 6.59296 12.8197L6.81319 14.6759Z"
                            fill="currentColor" />
                    </svg>
                    <span>Our Featured Project</span>
                </div>
                <h2 class="title split-text split-in-right">Our Latest <span>Success Story</span></h2>
            </div>

            <div class="ss-featured-card wow fadeInUp" data-wow-delay=".2s">
                <div class="row g-0 align-items-center">
                    <div class="col-lg-5 col-md-6 col-12">
                        <div class="ss-featured-image">
                            <span class="ss-featured-badge">Featured</span>
                            <img src="<?= attr($ss_featured['image']) ?>" alt="<?= attr($ss_featured['title']) ?>">
                        </div>
                    </div>
                    <div class="col-lg-7 col-md-6 col-12">
                        <div class="ss-featured-content">
                            <?php if (!empty($ss_featured['category'])): ?>
                                <span class="ss-featured-category"><?= e($ss_featured['category']) ?></span>
                            <?php endif; ?>
                            <h3><?= e($ss_featured['title']) ?></h3>
                            <p><?= e($ss_featured['excerpt']) ?></p>
                            <div class="ss-featured-meta">
                                <span><i class="fa-light fa-building"></i><?= e($ss_featured['client']) ?></span>
                            </div>
                            <a href="<?= !empty($ss_featured['slug']) ? url('/success-stories/' . $ss_featured['slug']) : '#' ?>" class="ss-read-link">
                                Read Full Story <i class="far fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
    <?php endif; ?>

    <!-- ============== CATEGORY FILTER + RECENT PROJECTS ============== -->
    <section class="ss-projects-section pt-100 pb-100 section-bg-3">
        <div class="container">

            <div class="section-title text-center mb-40">
                <div class="sub-title">
                    <svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M6.81319 14.6759C6.83947 14.8971 7.16053 14.8971 7.18681 14.6759L7.40705 12.8197C7.69143 10.4229 9.58112 8.53323 11.9779 8.24884L13.834 8.0286C14.0553 8.00233 14.0553 7.68127 13.834 7.65499L11.9779 7.43475C9.58112 7.15036 7.69143 5.26068 7.40705 2.86391L7.18681 1.00776C7.16053 0.786476 6.83947 0.786476 6.81319 1.00776L6.59296 2.86391C6.30857 5.26068 4.41888 7.15036 2.02209 7.43475L0.165943 7.65499C-0.0553144 7.68127 -0.0553144 8.00233 0.165943 8.0286L2.02209 8.24884C4.41888 8.53323 6.30857 10.4229 6.59296 12.8197L6.81319 14.6759Z"
                            fill="currentColor" />
                    </svg>
                    <span>Case Studies</span>
                </div>
                <h2 class="title split-text split-in-right">Our Recent <span>Projects</span></h2>
            </div>

            <!-- Filter bar — powered by mixItUp (already loaded site-wide in
                 partials/footer.php + auto-initialized in assets/js/script.js
                 via `$(".filter-list").mixItUp({})`). No custom JS needed. -->
            <div class="ss-filter-bar">
                <?php foreach ($ss_categories as $i => $cat): ?>
                    <button type="button" class="filter<?= $i === 0 ? ' active' : '' ?>"
                        data-filter="<?= $cat['filter'] === 'all' ? 'all' : '.' . attr($cat['filter']) ?>">
                        <?= e($cat['label']) ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Card component reused verbatim from the Home page's Success
                 Stories section (.case-block, see .case-wrapper in
                 pages/home.php) — same image, hover-zoom, title-underline
                 hover, and circular arrow-icon CTA. Only the `mix` + category
                 classes are added so mixItUp filtering keeps working. -->
            <div class="ss-projects-grid filter-list">
                <?php foreach ($ss_projects as $i => $project):
                    // Only projects with a `slug` have a matching static detail
                    // page (pages/success-stories/single.php) so far; the rest
                    // stay as placeholder links until their own pages exist.
                    $project_url = !empty($project['slug']) ? url('/success-stories/' . $project['slug']) : '#';
                    ?>
                    <div class="case-block mix <?= attr($project['filter']) ?> wow fadeInUp"
                        data-wow-delay="<?= 0.1 + ($i % 3) * 0.1 ?>s">
                        <div class="image not-hide-cursor" data-cursor="View<br>Story">
                            <a href="<?= attr($project_url) ?>" class="cursor-hide tp--hover-img" data-displacement="<?= attr($project['image']) ?>"
                                data-intensity="0.6" data-speedin="1" data-speedout="1">
                                <img src="<?= attr($project['image']) ?>" alt="<?= attr($project['title']) ?>">
                            </a>
                        </div>
                        <div class="content">
                            <div class="title-area">
                                <h4 class="title"><a href="<?= attr($project_url) ?>"><?= e($project['title']) ?></a></h4>
                                <p class="text"><?= e($project['category']) ?> &middot; <?= e($project['client']) ?></p>
                            </div>
                            <a href="<?= attr($project_url) ?>" class="arrow-icon">
                                <i class="far fa-long-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>
    </section>

    <!-- ============== FINAL CTA — two columns: CTA copy | existing Contact Form ==============
         The form column below is the exact same markup/ids/action/JS as
         pages/contact.php's `.contact-details` form (same backend endpoint,
         CSRF field, validation, and AJAX submit handler). Only the left
         column (previously a separate .mid-cta-section) and the column
         order are new; nothing about the form itself was rebuilt. -->
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
                            <span>Want Results Like These?</span>
                        </div>
                        <h2 class="title split-text split-in-right">Let&rsquo;s Build Something <span>That Creates Real
                                Impact</span></h2>
                        <div class="text mt-3">Have an AI idea or business challenge? Let&rsquo;s discuss how we can
                            turn it into a practical, production-ready solution.</div>
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
                                        if (data.success) { form.reset(); }
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