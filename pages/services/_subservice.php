<?php

/**
 * Shared service-details renderer — NEW long-form design
 * (based on the "AI Engineering" HTML design you supplied).
 *
 * Each service file (voice.php, text.php, image.php, process-auto.php,
 * ai-engineering.php ...) sets up the variables documented below and then
 * does:
 *
 *   include __DIR__ . '/_subservice.php';
 *
 * NOTE ON ESCAPING
 * -----------------
 * Fields suffixed "_html" (or documented as "(html allowed)") are printed
 * RAW — no e(). Only put trusted, developer-written markup in them
 * (e.g. `Smarter <span>Business</span>` for a highlighted word, or a
 * `<br>` line break). Never put user-submitted text in an _html field.
 * Every other field is escaped with e()/attr() as normal.
 *
 * ============================================================
 * VARIABLES (all optional — each section below only renders when its
 * data is present; leave a group unset/empty to hide that section
 * entirely, with no empty heading/wrapper left behind)
 * ============================================================
 *
 * --- Page banner / breadcrumb ---
 * $current_slug   string  slug for sidebar active state, e.g. 'voice'
 * $page_label     string  H1 shown in the dark banner, e.g. "Voice AI Solutions"
 * $crumb          string  breadcrumb leaf, e.g. "Voice AI"
 *
 * --- Hero block ---
 * $service_tag        string  small eyebrow tag above the H2, e.g. "Voice AI Services"
 * $service_title_html string  (html allowed) main H2, e.g. 'AI Voice Solutions for <span>Smarter</span>, Faster Business <br> Conversations'
 * $service_desc       string  paragraph under the H2
 *
 * --- "Powered by" platform strip ---
 * $platform_title  string        e.g. "Powered by the World's Leading AI & ML Platforms"
 * $platforms       string[]      e.g. ['PyTorch','TensorFlow','OpenAI', ...]
 *
 * --- Impact stats (4 cards) ---
 * $impact_stats    array of ['number' => '40+', 'title' => 'Production AI', 'desc' => 'Projects Successfully Delivered']
 *
 * --- Service overview (2-col: text + feature list) ---
 * $overview_sub          string   eyebrow, e.g. "UNDERSTANDING THE SERVICE"
 * $overview_title_html   string   (html allowed) e.g. 'What Are <span>AI ML Services?</span>'
 * $overview_paragraphs   string[] one or more paragraphs
 * $overview_btn_text     string   e.g. "Talk With Our Experts"
 * $overview_features     array of ['icon' => 'fas fa-brain', 'title' => 'Production AI', 'desc' => '...']
 *
 * --- "Why AI/this service" benefit cards (with Real Example box) ---
 * $benefits_sub         string
 * $benefits_title_html  string (html allowed)
 * $benefits_text        string
 * $benefit_cards        array of ['icon'=>'fas fa-coins','title'=>'...','desc'=>'...','example'=>'...']
 *
 * --- "What We Build" services grid ---
 * $grid_sub         string
 * $grid_title_html  string (html allowed)
 * $grid_text        string
 * $grid_services    array of ['icon'=>'fas fa-brain','title'=>'...','desc'=>'...','tags'=>['Python','TensorFlow']]
 *
 * --- "What You Get" numbered benefit cards ---
 * $whatyouget_sub         string
 * $whatyouget_title_html  string (html allowed)
 * $whatyouget_text        string
 * $whatyouget_cards       array of ['title'=>'...','desc'=>'...']   (numbered 01, 02... automatically)
 *
 * --- Industries / use cases ---
 * $industries_sub         string
 * $industries_title_html  string (html allowed)
 * $industries_text        string
 * $industries             array of ['title'=>'Healthcare','items'=>['Medical imaging & diagnostics', ...]]
 *
 * --- Framework / methodology cards ---
 * $framework_sub         string
 * $framework_title_html  string (html allowed)
 * $framework_text        string
 * $framework_steps       array of ['icon'=>'fas fa-brain','title'=>'...','desc'=>'...']
 *
 * --- "Why Quantal" numbered cards ---
 * $why_sub         string
 * $why_title_html  string (html allowed)
 * $why_text        string
 * $why_cards       array of ['title'=>'...','desc'=>'...']   (numbered 01, 02... automatically)
 *
 * --- Engagement models ---
 * $engagement_sub         string
 * $engagement_title_html  string (html allowed)
 * $engagement_text        string
 * $engagement_models      array of [
 *                            'badge'    => 'Model 01' | 'Most Popular',
 *                            'title'    => 'ML Consulting',
 *                            'desc'     => '...',
 *                            'features' => ['AI Readiness Assessment', ...],
 *                            'btn_text' => 'Book Consultation',
 *                            'featured' => true|false,
 *                          ]
 *
 * --- Process timeline ---
 * $process_sub         string
 * $process_title_html  string (html allowed)
 * $process_text        string
 * $process_steps       array of ['title'=>'...','desc'=>'...']   (numbered 01, 02... automatically)
 *
 * --- Mid-page CTA band ---
 * $cta_tag         string  e.g. "READY TO BUILD WITH AI?"
 * $cta_title_html  string  (html allowed)
 * $cta_text        string
 *
 * --- Case studies ---
 * $cs_sub         string
 * $cs_title_html  string (html allowed)
 * $cs_text        string
 * $case_studies   array of ['tag'=>'Business Impact','title'=>'...','desc'=>'...','result'=>'Reduced unplanned downtime by 35%...']
 *
 * --- Tech stack ---
 * $tech_sub         string
 * $tech_title_html  string (html allowed)
 * $tech_text        string
 * $tech_categories  array of ['title'=>'Machine Learning Frameworks','items'=>['PyTorch','TensorFlow', ...]]
 *
 * --- Security & compliance ---
 * $security_sub         string
 * $security_title_html  string (html allowed)
 * $security_text        string
 * $security_cards       array of ['title'=>'...','desc'=>'...']   (numbered 01, 02... automatically)
 *
 * --- Related services ---
 * $related_sub          string
 * $related_title_html   string (html allowed)
 * $related_text         string
 * $related_group_title  string  e.g. "Recommended Solutions"
 * $related_items        array of ['label'=>'AI Development','slug'=>'ai-engineering']  slug -> url('/services/'.slug), or ['label'=>..,'href'=>'#'] for external/manual links
 *
 * --- Knowledge hub / blog teasers ---
 * $blog_sub         string
 * $blog_title_html  string (html allowed)
 * $blog_text        string
 * $blog_posts       array of ['image'=>'images/blog/blog-1.jpg','category'=>'Machine Learning','title'=>'...','desc'=>'...','link'=>'#']
 *
 * --- FAQ ---
 * $faq_intro   string
 * $faqs        array of [question, answer]   (2nd item auto pre-opened, matching theme behaviour)
 *
 * The brand/clients strip, the client testimonial slider, and the contact
 * form are shared site-wide content and are NOT passed in — they're
 * rendered directly below from fixed markup, same as the old template's
 * sidebar contact box.
 */
// Falls back to the original hardcoded map when a caller doesn't supply one
// (kept for backward compatibility with any page written against the old
// contract) — the dynamic DB-driven path supplies the live service list.
$service_links ??= [
    'voice' => 'Voice AI Solutions',
    'text' => 'Text AI Solutions',
    'image' => 'Image / Document AI',
    'process-auto' => 'Process Automation',
    'ai-engineering' => 'AI Engineering',
];
?>

<!-- Start main-content -->
<section class="page-banner news-banner" style="padding:120px 0 80px;background:#1d2327;color:#fff;text-align:center;">
    <div class="container">
        <h1 style="color:#fff;font-size:36px;margin:25px 0 14px;line-height:1.2;"><?= e($page_title) ?></h1>
        <p style="opacity:0.75;margin:0;font-size:14px;">
            <a href="<?= url('/') ?>" style="color:#72aee6;">Home</a> &nbsp;/&nbsp;
            <a href="<?= url('/services') ?>" style="color:#72aee6;">Services</a> &nbsp;/&nbsp;
            <span><?= e($crumb) ?></span>
        </p>
    </div>
</section>
<!-- end main-content -->

<!-- Start Services Details -->
<section class="services-details pt-120 pb-100">
    <div class="container">
        <div class="row">

            <!-- Content -->
            <div class="col-xl-8 col-lg-8">
                <div class="services-details__content">

                    <?php if (!empty($service_tag) || !empty($service_title_html) || !empty($service_desc)): ?>
                    <span class="service-tag"><?= e($service_tag ?? '') ?></span>

                    <h2 class="service-title mt-4"><?= $service_title_html ?? '' /* trusted HTML */ ?></h2>

                    <p class="service-desc"><?= e($service_desc ?? '') ?></p>

                    <div class="service-btns pb-25">
                        <a href="<?= url('/contact') ?>" class="theme-btn btn-style-one me-3">
                            <span class="btn-title">Schedule a Demo</span>
                        </a>
                        <a href="<?= url('/case-studies') ?>" class="theme-btn btn-style-border">
                            <span class="btn-title">View Case Studies</span>
                        </a>
                    </div>
                    <?php endif; ?>

                    <!-- Platform strip -->
                    <?php if (!empty($platforms)): ?>
                    <div class="ai-platforms-section pb-100">
                        <span class="platform-title"><?= e($platform_title ?? '') ?></span>
                        <div class="platform-list">
                            <?php foreach ($platforms as $platform): ?>
                                <span class="platform-item"><?= e($platform) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Impact stats -->
                    <?php if (!empty($impact_stats)): ?>
                    <section class="impact-section pb-100">
                        <div class="container">
                            <div class="row g-4">
                                <?php foreach ($impact_stats as $i => $stat): ?>
                                    <div class="col-md-6 wow fadeInUp" data-wow-delay="<?= 0.2 + $i * 0.1 ?>s">
                                        <div class="impact-card">
                                            <div class="impact-number"><?= e($stat['number']) ?></div>
                                            <h5><?= e($stat['title']) ?></h5>
                                            <p><?= e($stat['desc']) ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </section>
                    <?php endif; ?>

                    <!-- Trusted clients (shared, site-wide) -->
                    <div class="brand-section-2 pb-100">
                        <div class="container">
                            <div class="brand-wrap-2">
                                <div class="text-box"><p>Our Trusted Clients</p></div>
                                <div class="swiper brand-slider2">
                                    <div class="swiper-wrapper">
                                        <?php
                                        $clients = [
                                            ['icici.png', 'ICICI'],
                                            ['waterfield.png', 'Waterfield'],
                                            ['grip.png', 'Grip'],
                                            ['armstrong.png', 'Armstrong'],
                                            ['elunic.jpg', 'Elunic'],
                                            ['nortmaq.png', 'Nortmaq'],
                                            ['xanevo.jpg', 'Xanevo'],
                                            ['ackuity.jpg', 'Ackuity'],
                                            ['benow_logo.png', 'Benow'],
                                            ['brandtrust.jpg', 'Brand Trust'],
                                            ['civiq.png', 'Civiq'],
                                            ['corsano.png', 'Corsano'],
                                            ['desert_recovery_center.png', 'Desert Recovery Center'],
                                            ['northwell-health.png', 'Northwell Health'],
                                            ['osteopathic_healing_hands.png', 'Osteopathic Healing Hands'],
                                            ['sanofi.png', 'Sanofi'],
                                            ['street.png', 'Street'],
                                            ['tbl.webp', 'TBL'],
                                        ];
                                        foreach ($clients as $client):
                                            ?>
                                            <div class="swiper-slide">
                                                <div class="brand-img2 image-fluid">
                                                    <img src="<?= asset('images/quantal/clients/' . $client[0]) ?>" alt="<?= attr($client[1]) ?>">
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Service overview -->
                    <?php if (!empty($overview_paragraphs) || !empty($overview_features)): ?>
                    <section class="service-overview pb-100">
                        <div class="container">
                            <div class="row align-items-center g-5">
                                <div class="col-lg-6">
                                    <span class="sub-title"><?= e($overview_sub ?? '') ?></span>
                                    <h2 class="sec-title mb-30"><?= $overview_title_html ?? '' /* trusted HTML */ ?></h2>
                                    <?php foreach (($overview_paragraphs ?? []) as $p): ?>
                                        <p><?= e($p) ?></p>
                                    <?php endforeach; ?>
                                    <a href="<?= url('/contact') ?>" class="theme-btn btn-style-one mt-20">
                                        <span class="btn-title"><?= e($overview_btn_text ?? '') ?></span>
                                    </a>
                                </div>
                                <div class="col-lg-6">
                                    <div class="feature-list">
                                        <?php foreach (($overview_features ?? []) as $f): ?>
                                            <div class="feature-item">
                                                <div class="feature-icon"><i class="<?= attr($f['icon']) ?>"></i></div>
                                                <div>
                                                    <h5><?= e($f['title']) ?></h5>
                                                    <p><?= e($f['desc']) ?></p>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                    <?php endif; ?>

                    <!-- Benefit cards w/ real-example box -->
                    <?php if (!empty($benefit_cards)): ?>
                    <section class="ai-benefits-section pb-100">
                        <div class="container">
                            <div class="sec-title text-center mb-70">
                                <span class="sub-title"><?= e($benefits_sub ?? '') ?></span>
                                <h2><?= $benefits_title_html ?? '' /* trusted HTML */ ?></h2>
                                <div class="text"><?= e($benefits_text ?? '') ?></div>
                            </div>
                            <div class="row g-4">
                                <?php foreach ($benefit_cards as $i => $card): ?>
                                    <div class="col-lg-6 col-md-6 wow fadeInUp" data-wow-delay="<?= 0.2 + $i * 0.2 ?>s">
                                        <div class="service-card-v2">
                                            <div class="card-top">
                                                <div class="service-icon"><i class="<?= attr($card['icon']) ?>"></i></div>
                                            </div>
                                            <h4><?= e($card['title']) ?></h4>
                                            <p><?= e($card['desc']) ?></p>
                                            <div class="example-box">
                                                <strong>Real Example</strong>
                                                <p><?= e($card['example']) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </section>
                    <?php endif; ?>

                    <!-- What we build -->
                    <?php if (!empty($grid_services)): ?>
                    <section class="services-grid-section pb-100">
                        <div class="container">
                            <div class="sec-title text-center mb-70">
                                <span class="sub-title"><?= e($grid_sub ?? '') ?></span>
                                <h2><?= $grid_title_html ?? '' /* trusted HTML */ ?></h2>
                                <div class="text"><?= e($grid_text ?? '') ?></div>
                            </div>
                            <div class="row g-4">
                                <?php foreach ($grid_services as $svc): ?>
                                    <div class="col-md-6 wow fadeInUp">
                                        <div class="ml-service-card">
                                            <div class="service-icon"><i class="<?= attr($svc['icon']) ?>"></i></div>
                                            <h4><?= e($svc['title']) ?></h4>
                                            <p><?= e($svc['desc']) ?></p>
                                            <div class="tech-tags">
                                                <?php foreach ($svc['tags'] as $tag): ?>
                                                    <span><?= e($tag) ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </section>
                    <?php endif; ?>

                    <!-- What you get -->
                    <?php if (!empty($whatyouget_cards)): ?>
                    <section class="benefits-section pb-100">
                        <div class="container">
                            <div class="sec-title text-center mb-70">
                                <span class="sub-title"><?= e($whatyouget_sub ?? '') ?></span>
                                <h2><?= $whatyouget_title_html ?? '' /* trusted HTML */ ?></h2>
                                <div class="text"><?= e($whatyouget_text ?? '') ?></div>
                            </div>
                            <div class="row g-4">
                                <?php foreach ($whatyouget_cards as $i => $card): ?>
                                    <div class="col-md-6 wow fadeInUp" data-wow-delay="<?= 0.2 + $i * 0.1 ?>s">
                                        <div class="benefit-card">
                                            <div class="benefit-number"><?= sprintf('%02d', $i + 1) ?></div>
                                            <h4><?= e($card['title']) ?></h4>
                                            <p><?= e($card['desc']) ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </section>
                    <?php endif; ?>

                    <!-- Industries -->
                    <?php if (!empty($industries)): ?>
                    <section class="industries-section pb-100">
                        <div class="container">
                            <div class="sec-title text-center mb-70">
                                <span class="sub-title"><?= e($industries_sub ?? '') ?></span>
                                <h2><?= $industries_title_html ?? '' /* trusted HTML */ ?></h2>
                                <div class="text"><?= e($industries_text ?? '') ?></div>
                            </div>
                            <div class="row g-4">
                                <?php foreach ($industries as $ind): ?>
                                    <div class="col-lg-4 col-md-6">
                                        <div class="industry-card">
                                            <h4><?= e($ind['title']) ?></h4>
                                            <ul>
                                                <?php foreach ($ind['items'] as $item): ?>
                                                    <li><?= e($item) ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </section>
                    <?php endif; ?>

                    <!-- Framework -->
                    <?php if (!empty($framework_steps)): ?>
                    <section class="our-framework-section pb-100">
                        <div class="container">
                            <div class="sec-title text-center mb-70">
                                <span class="sub-title"><?= e($framework_sub ?? '') ?></span>
                                <h2><?= $framework_title_html ?? '' /* trusted HTML */ ?></h2>
                                <div class="text"><?= e($framework_text ?? '') ?></div>
                            </div>
                            <div class="row g-4">
                                <?php foreach ($framework_steps as $step): ?>
                                    <div class="col-md-6">
                                        <div class="framework-card">
                                            <div class="framework-icon"><i class="<?= attr($step['icon']) ?>"></i></div>
                                            <h4><?= e($step['title']) ?></h4>
                                            <p><?= e($step['desc']) ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </section>
                    <?php endif; ?>

                    <!-- Why Quantal -->
                    <?php if (!empty($why_cards)): ?>
                    <section class="why-quantal-section pb-100">
                        <div class="container">
                            <div class="sec-title text-center mb-70">
                                <span class="sub-title"><?= e($why_sub ?? '') ?></span>
                                <h2><?= $why_title_html ?? '' /* trusted HTML */ ?></h2>
                                <div class="text"><?= e($why_text ?? '') ?></div>
                            </div>
                            <div class="row g-4">
                                <?php foreach ($why_cards as $i => $card): ?>
                                    <div class="col-lg-4 col-md-6 wow fadeInUp">
                                        <div class="why-card">
                                            <div class="why-number"><?= sprintf('%02d', $i + 1) ?></div>
                                            <h4><?= e($card['title']) ?></h4>
                                            <p><?= e($card['desc']) ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </section>
                    <?php endif; ?>

                    <!-- Engagement models -->
                    <?php if (!empty($engagement_models)): ?>
                    <section class="engagement-section pb-100">
                        <div class="container">
                            <div class="sec-title text-center mb-70">
                                <span class="sub-title"><?= e($engagement_sub ?? '') ?></span>
                                <h2><?= $engagement_title_html ?? '' /* trusted HTML */ ?></h2>
                                <div class="text"><?= e($engagement_text ?? '') ?></div>
                            </div>
                            <div class="row g-4">
                                <?php foreach ($engagement_models as $i => $model): ?>
                                    <div class="col-lg-6 wow fadeInUp" data-wow-delay="<?= 0.2 + $i * 0.2 ?>s">
                                        <div class="engagement-card<?= !empty($model['featured']) ? ' featured' : '' ?>">
                                            <div class="engagement-badge"><?= e($model['badge']) ?></div>
                                            <h3><?= e($model['title']) ?></h3>
                                            <p><?= e($model['desc']) ?></p>
                                            <ul>
                                                <?php foreach ($model['features'] as $feat): ?>
                                                    <li><?= e($feat) ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                            <a href="<?= url('/contact') ?>" class="theme-btn btn-style-one">
                                                <span class="btn-title"><?= e($model['btn_text']) ?></span>
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </section>
                    <?php endif; ?>

                    <!-- Process timeline -->
                    <?php if (!empty($process_steps)): ?>
                    <section class="process-timeline-section pb-100">
                        <div class="container">
                            <div class="sec-title text-center mb-70">
                                <span class="sub-title"><?= e($process_sub ?? '') ?></span>
                                <h2><?= $process_title_html ?? '' /* trusted HTML */ ?></h2>
                                <div class="text"><?= e($process_text ?? '') ?></div>
                            </div>
                            <div class="process-timeline">
                                <?php foreach ($process_steps as $i => $step): ?>
                                    <div class="process-item">
                                        <div class="process-number"><?= sprintf('%02d', $i + 1) ?></div>
                                        <div class="process-content">
                                            <h3><?= e($step['title']) ?></h3>
                                            <p><?= e($step['desc']) ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </section>
                    <?php endif; ?>

                    <!-- Mid CTA -->
                    <?php if (!empty($cta_tag) || !empty($cta_title_html) || !empty($cta_text)): ?>
                    <section class="mid-cta-section pb-100">
                        <div class="container">
                            <div class="mid-cta-box">
                                <span class="service-tag"><?= e($cta_tag ?? '') ?></span>
                                <h2><?= $cta_title_html ?? '' /* trusted HTML */ ?></h2>
                                <p><?= e($cta_text ?? '') ?></p>
                                <div class="service-btns">
                                    <hr>
                                    <a href="<?= url('/contact') ?>" class="theme-btn btn-style-one me-3">
                                        <span class="btn-title">Schedule a Demo</span>
                                    </a>
                                    <a href="<?= url('/case-studies') ?>" class="theme-btn btn-style-border">
                                        <span class="btn-title">View Case Studies</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </section>
                    <?php endif; ?>

                    <!-- Case studies -->
                    <?php if (!empty($case_studies)): ?>
                    <section class="case-studies-section pb-100">
                        <div class="container">
                            <div class="sec-title text-center mb-70">
                                <span class="sub-title"><?= e($cs_sub ?? '') ?></span>
                                <h2><?= $cs_title_html ?? '' /* trusted HTML */ ?></h2>
                                <div class="text"><?= e($cs_text ?? '') ?></div>
                            </div>
                            <div class="row g-4">
                                <?php foreach ($case_studies as $i => $cs): ?>
                                    <div class="col-lg-6 col-md-6 wow fadeInUp" data-wow-delay="<?= 0.2 + $i * 0.1 ?>s">
                                        <div class="case-study-card">
                                            <h6 class="service-tag mb-3"><?= e($cs['tag']) ?></h6>
                                            <h4><?= e($cs['title']) ?></h4>
                                            <p><?= e($cs['desc']) ?></p>
                                            <div class="case-study-box">
                                                <p><?= e($cs['result']) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </section>
                    <?php endif; ?>

                    <!-- Testimonials (shared, site-wide) -->
                    <section class="testimonial-wrapper testimonial-one pb-100">
                        <div class="auto-container">
                            <div class="row g-sm-4">
                                <div class="col-xl-12 col-lg-12">
                                    <div class="slider-box">
                                        <div class="section-title">
                                            <div class="sub-title">
                                                <svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M6.81319 14.6759C6.83947 14.8971 7.16053 14.8971 7.18681 14.6759L7.40705 12.8197C7.69143 10.4229 9.58112 8.53323 11.9779 8.24884L13.834 8.0286C14.0553 8.00233 14.0553 7.68127 13.834 7.65499L11.9779 7.43475C9.58112 7.15036 7.69143 5.26068 7.40705 2.86391L7.18681 1.00776C7.16053 0.786476 6.83947 0.786476 6.81319 1.00776L6.59296 2.86391C6.30857 5.26068 4.41888 7.15036 2.02209 7.43475L0.165943 7.65499C-0.0553144 7.68127 -0.0553144 8.00233 0.165943 8.0286L2.02209 8.24884C4.41888 8.53323 6.30857 10.4229 6.59296 12.8197L6.81319 14.6759Z" fill="currentColor" />
                                                </svg>
                                                <span>Client Stories</span>
                                            </div>
                                            <h2 class="title split-text split-in-right">What clients say <span>about us.</span></h2>
                                        </div>
                                        <div class="swiper testimonial-slider">
                                            <div class="swiper-wrapper">
                                                <div class="swiper-slide">
                                                    <div class="testimonial-block">
                                                        <p class="text">&ldquo;Quantal AI and Team are EXPERTS at building ANY AI functionality you&rsquo;re seeking! We&rsquo;ve hired them for 2 projects already &mdash; each completed ON TIME and UNDER BUDGET. Highly Recommended!&rdquo;</p>
                                                        <div class="infu">
                                                            <div class="image"><img src="<?= asset('images/quantal/clients/myhomecarebiz.jpg') ?>" alt="Melissa C"></div>
                                                            <div class="name-info"><h5 class="name">Melissa C</h5><span>myhomecarebiz.com</span></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="swiper-slide">
                                                    <div class="testimonial-block">
                                                        <p class="text">&ldquo;Working with Quantal AI team has been an absolute pleasure. Their technical aptitude is outstanding &mdash; they&rsquo;re not only highly competent but also creative, thoughtful, and reliable. They built a complex integration for our wine business connecting PhotoRoom, Google Cloud, AWS, and Shopify, and it works beautifully.&rdquo;</p>
                                                        <div class="infu">
                                                            <div class="image"><img src="<?= asset('images/quantal/clients/armstrong.png') ?>" alt="David F"></div>
                                                            <div class="name-info"><h5 class="name">David F</h5><span>Osteopathic Healing Hands</span></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="swiper-slide">
                                                    <div class="testimonial-block">
                                                        <p class="text">&ldquo;It was a pleasure working with Quantal AI team. Communication was smooth, deadlines were respected, and the overall collaboration was professional and efficient. I would definitely consider working together again in the future. Recommended!&rdquo;</p>
                                                        <div class="infu">
                                                            <div class="image"><img src="<?= asset('images/quantal/clients/elunic.jpg') ?>" alt="Ivana M"></div>
                                                            <div class="name-info"><h5 class="name">Ivana M</h5><span>Elunic AG</span></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="array-button">
                                            <button class="array-prev"><i class="fas fa-long-arrow-left"></i></button>
                                            <button class="array-next"><i class="fas fa-long-arrow-right"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Tech stack -->
                    <?php if (!empty($tech_categories)): ?>
                    <section class="tech-stack-section pb-100">
                        <div class="container">
                            <div class="sec-title">
                                <span class="sub-title"><?= e($tech_sub ?? '') ?></span>
                                <h2><?= $tech_title_html ?? '' /* trusted HTML */ ?></h2>
                                <div class="text"><?= e($tech_text ?? '') ?></div>
                            </div>
                            <div class="tech-stack-wrapper">
                                <?php foreach ($tech_categories as $cat): ?>
                                    <div class="tech-category">
                                        <span class="tech-category-title"><?= e($cat['title']) ?></span>
                                        <div class="tech-list">
                                            <?php foreach ($cat['items'] as $item): ?>
                                                <span class="tech-item"><?= e($item) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </section>
                    <?php endif; ?>

                    <!-- Contact form (shared, site-wide) -->
                    <section class="contact-details pb-50">
                        <div class="container">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="section-title mb-30">
                                        <div class="sub-title">
                                            <svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M6.81319 14.6759C6.83947 14.8971 7.16053 14.8971 7.18681 14.6759L7.40705 12.8197C7.69143 10.4229 9.58112 8.53323 11.9779 8.24884L13.834 8.0286C14.0553 8.00233 14.0553 7.68127 13.834 7.65499L11.9779 7.43475C9.58112 7.15036 7.69143 5.26068 7.40705 2.86391L7.18681 1.00776C7.16053 0.786476 6.83947 0.786476 6.81319 1.00776L6.59296 2.86391C6.30857 5.26068 4.41888 7.15036 2.02209 7.43475L0.165943 7.65499C-0.0553144 7.68127 -0.0553144 8.00233 0.165943 8.0286L2.02209 8.24884C4.41888 8.53323 6.30857 10.4229 6.59296 12.8197L6.81319 14.6759Z" fill="currentColor" />
                                            </svg>
                                            <span>Get in Touch</span>
                                        </div>
                                        <h2 class="title split-text split-in-right">Talk to an AI Expert</h2>
                                    </div>
                                    <div id="contact-msg" class="contact-msg" style="display:none;"></div>
                                    <form id="contact_form" name="contact_form" action="<?= url('/contact-submit') ?>" method="post">
                                        <?= csrf_field() ?>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="mb-3"><input name="form_name" class="form-control" type="text" placeholder="Enter Name" required></div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="mb-3"><input name="form_phone" class="form-control" type="text" placeholder="Enter Phone"></div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div class="mb-3"><input name="form_email" class="form-control required email" type="email" placeholder="Enter Email" required></div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div class="mb-3"><input name="form_subject" class="form-control required" type="text" placeholder="Enter Subject" required></div>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <textarea name="form_message" class="form-control required" rows="7" placeholder="Enter Message" required></textarea>
                                        </div>
                                        <div class="mb-5 theme-btn-main">
                                            <input name="form_botcheck" type="hidden" value="">
                                            <button type="submit" id="contact-submit-btn" class="theme-btn btn-style-one transform"><span class="btn-title">Send message</span></button>
                                            <button type="reset" class="theme-btn btn-style-one transform"><span class="btn-title">Reset</span></button>
                                        </div>
                                    </form>
                                    <script>
                                    (function () {
                                        var form  = document.getElementById('contact_form');
                                        var msgEl = document.getElementById('contact-msg');
                                        var btn   = document.getElementById('contact-submit-btn');
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
                                                msgEl.className   = 'contact-msg ' + (data.success ? 'contact-msg--ok' : 'contact-msg--err');
                                                msgEl.style.display = 'block';
                                                if (data.success) { form.reset(); }
                                            })
                                            .catch(function () {
                                                msgEl.textContent   = 'Something went wrong. Please try again.';
                                                msgEl.className     = 'contact-msg contact-msg--err';
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

                    <!-- Security & compliance -->
                    <?php if (!empty($security_cards)): ?>
                    <section class="security-compliance-section pb-120">
                        <div class="container">
                            <div class="sec-title text-center mb-70">
                                <span class="sub-title"><?= e($security_sub ?? '') ?></span>
                                <h2><?= $security_title_html ?? '' /* trusted HTML */ ?></h2>
                                <div class="text"><?= e($security_text ?? '') ?></div>
                            </div>
                            <div class="row g-4">
                                <?php foreach ($security_cards as $i => $card): ?>
                                    <div class="col-lg-6 col-md-6 wow fadeInUp" data-wow-delay="<?= 0.2 + $i * 0.1 ?>s">
                                        <div class="security-card">
                                            <div class="security-number"><?= sprintf('%02d', $i + 1) ?></div>
                                            <h4><?= e($card['title']) ?></h4>
                                            <p><?= e($card['desc']) ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </section>
                    <?php endif; ?>

                    <!-- Related services -->
                    <?php if (!empty($related_items)): ?>
                    <section class="related-services-section pb-100">
                        <div class="container">
                            <div class="sec-title">
                                <span class="sub-title"><?= e($related_sub ?? '') ?></span>
                                <h2><?= $related_title_html ?? '' /* trusted HTML */ ?></h2>
                                <div class="text"><?= e($related_text ?? '') ?></div>
                            </div>
                            <div class="related-services-wrapper">
                                <div class="related-service-group">
                                    <span class="related-service-title"><?= e($related_group_title ?? '') ?></span>
                                    <div class="related-service-list">
                                        <?php foreach ($related_items as $item):
                                            $href = !empty($item['slug']) ? url('/services/' . attr($item['slug'])) : ($item['href'] ?? '#'); ?>
                                            <a href="<?= $href ?>" class="related-service-item"><?= e($item['label']) ?></a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                    <?php endif; ?>

                    <!-- Knowledge hub -->
                    <?php if (!empty($blog_posts)): ?>
                    <section class="knowledge-hub-section pb-100">
                        <div class="container">
                            <div class="sec-title text-center mb-70">
                                <span class="sub-title"><?= e($blog_sub ?? '') ?></span>
                                <h2><?= $blog_title_html ?? '' /* trusted HTML */ ?></h2>
                                <div class="text"><?= e($blog_text ?? '') ?></div>
                            </div>
                            <div class="row g-4">
                                <?php foreach ($blog_posts as $i => $post): ?>
                                    <div class="col-md-6 wow fadeInUp" data-wow-delay="<?= 0.2 + $i * 0.2 ?>s">
                                        <article class="knowledge-card">
                                            <div class="knowledge-image">
                                                <img src="<?= asset($post['image']) ?>" alt="<?= attr($post['category']) ?>">
                                            </div>
                                            <div class="knowledge-content">
                                                <span class="knowledge-category"><?= e($post['category']) ?></span>
                                                <h4><a href="<?= $post['link'] ?? '#' ?>"><?= e($post['title']) ?></a></h4>
                                                <p><?= e($post['desc']) ?></p>
                                                <a href="<?= $post['link'] ?? '#' ?>" class="knowledge-btn">Read More <i class="far fa-arrow-right"></i></a>
                                            </div>
                                        </article>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="text-center mt-70">
                                <a href="<?= url('/blog') ?>" class="theme-btn btn-style-one">
                                    <span class="btn-title">View All Articles</span>
                                </a>
                            </div>
                        </div>
                    </section>
                    <?php endif; ?>

                    <!-- FAQ -->
                    <?php if (!empty($faqs)): ?>
                    <section class="pb-100">
                        <div class="faq-content">
                            <h3 class="mb-3">Frequently Asked Questions</h3>
                            <p class="text"><?= e($faq_intro ?? '') ?></p>
                            <ul class="accordion-box wow fadeInUp p-0 mt-40" data-wow-delay=".3s">
                                <?php foreach ($faqs as $i => $faq):
                                    $is_active = $i === 1;  // mimic theme's pre-opened second item
                                    $faq_q = $faq['question'] ?? ($faq[0] ?? '');
                                    $faq_a = $faq['answer'] ?? ($faq[1] ?? ''); ?>
                                    <li class="accordion block<?= $is_active ? ' active-block' : '' ?>">
                                        <div class="acc-btn<?= $is_active ? ' active' : '' ?>"><?= e($faq_q) ?>
                                            <div class="icon fa fa-plus"></div>
                                        </div>
                                        <div class="acc-content<?= $is_active ? ' current' : '' ?>">
                                            <div class="content">
                                                <div class="text"><?= e($faq_a) ?></div>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </section>
                    <?php endif; ?>

                </div>
            </div>

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

        </div>
    </div>
</section>
<!-- End Services Details -->