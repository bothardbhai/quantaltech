<?php

/**
 * Shared service-details renderer based on theme/page-service-details.html.
 *
 * Each service file (voice.php, text.php, image.php, process-auto.php) sets
 * up these variables and includes this file:
 *
 *   $current_slug - current service slug for sidebar active state
 *   $page_label   - label shown in page-title section ("Voice AI Solutions")
 *   $crumb        - breadcrumb leaf ("Voice AI")
 *   $hero_image   - main service banner image (used in service-details-image)
 *   $overview_h   - overview heading
 *   $overview_p1  - first paragraph of overview
 *   $overview_p2  - second paragraph of overview
 *   $center_h     - secondary heading ("Service Center" replacement)
 *   $center_p     - paragraph under center heading
 *   $blockquote   - pull-quote text
 *   $faqs         - array of [question, answer] pairs
 *
 * Routing for the underscore-prefixed file is blocked (the router validates
 * URL paths begin with [a-z0-9] only, and discovery skips _-prefixed files).
 *
 * The <Tech Innovation, Network security ...> sidebar list is replaced with
 * the actual 4 service categories with the current one highlighted.
 */
$service_links = [
    'voice' => 'Voice AI Services',
    'text' => 'Text AI Services',
    'image' => 'Image / Document AI',
    'process-auto' => 'Process Automation',
];
?>

<!-- Start main-content -->
<section class="page-banner news-banner" style="padding:120px 0 80px;background:#1d2327;color:#fff;text-align:center;">
    <div class="container">
        <h1 style="color:#fff;font-size:36px;margin:25px 0 14px;line-height:1.2;"><?= e($page_label) ?></h1>
        <p style="opacity:0.75;margin:0;font-size:14px;">
            <a href="<?= url('/') ?>" style="color:#72aee6;">Home</a> &nbsp;/&nbsp;
            <a href="<?= url('/services') ?>" style="color:#72aee6;">Services</a> &nbsp;/&nbsp;
            <span><?= e($crumb) ?></span>
        </p>
    </div>
</section>
<!-- end main-content -->

<!-- Start Services Details -->
<section class="services-details pt-120 pb-90">
    <div class="container">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-xl-4 col-lg-4">
                <div class="service-sidebar">
                    <div class="sidebar-widget service-sidebar-single">
                        <div class="sidebar-service-list">
                            <ul>
                                <?php foreach ($service_links as $slug => $label):
                                    $is_active = $slug === $current_slug; ?>
                                    <li<?= $is_active ? ' class="current"' : '' ?>>
                                        <a href="<?= url('/services/' . attr($slug)) ?>"<?= $is_active ? ' class="current"' : '' ?>>
                                            <i class="fas fa-angle-right"></i>
                                            <span><?= e($label) ?></span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
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

            <!-- Content -->
            <div class="col-xl-8 col-lg-8">
                <div class="services-details__content">
                    <div class="service-details-image fix">
                        <img data-speed=".8" src="<?= asset($hero_image) ?>" alt="<?= attr($page_label) ?>">
                    </div>

                    <h3 class="mt-4"><?= e($overview_h) ?></h3>
                    <p class="text"><?= e($overview_p1) ?></p>
                    <p class="text"><?= e($overview_p2) ?></p>

                    <div class="content mt-40">
                        <div class="text">
                            <h3><?= e($center_h) ?></h3>
                            <p class="text"><?= e($center_p) ?></p>
                            <blockquote class="blockquote-one"><?= e($blockquote) ?></blockquote>
                        </div>

                        <!-- Capability cards (replaces theme's project-image-slider - preserves visual rhythm) -->
                        <div class="row g-4 mt-3">
                            <?php foreach ($capabilities as $cap): ?>
                                <div class="col-md-6 wow fadeInUp" data-wow-delay=".3s">
                                    <div class="feature-block style-2" style="height:100%;">
                                        <div class="content">
                                            <h4 class="title"><?= e($cap['name']) ?></h4>
                                            <p class="text"><?= e($cap['desc']) ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="faq-content mt-5">
                        <h3 class="mb-3">Frequently Asked Questions</h3>
                        <p class="text"><?= e($faq_intro) ?></p>
                        <ul class="accordion-box wow fadeInUp p-0 mt-40" data-wow-delay=".3s">
                            <?php foreach ($faqs as $i => $faq):
                                $is_active = $i === 1; // mimic theme's pre-opened second item ?>
                                <li class="accordion block<?= $is_active ? ' active-block' : '' ?>">
                                    <div class="acc-btn<?= $is_active ? ' active' : '' ?>"><?= e($faq[0]) ?>
                                        <div class="icon fa fa-plus"></div>
                                    </div>
                                    <div class="acc-content<?= $is_active ? ' current' : '' ?>">
                                        <div class="content">
                                            <div class="text"><?= e($faq[1]) ?></div>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- End Services Details -->
