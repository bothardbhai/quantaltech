<?php

/**
 * Based on theme template - content swapped to Quantal AI.
 *
 * The 6 cards below are unchanged static content. Any additional published
 * Service Master entry (i.e. not already one of those 6 hand-written pages)
 * is appended after them using the same card markup, auto-numbered
 * continuing from 07 — this is what makes a brand-new admin-created service
 * actually reachable from navigation instead of only via a typed URL.
 */
$page_title = !empty($page_seo['title']) ? $page_seo['title'] : 'AI Services - Quantal AI';
$active_page = 'services';

$pdo = db();
$dynamic_services = $pdo ? get_services($pdo, ['status' => 'published']) : [];
?>

<!-- Start main-content -->
<section class="page-banner news-banner" style="padding:120px 0 80px;background:#1d2327;color:#fff;text-align:center;">
    <div class="container">
        <h1 style="color:#fff;font-size:36px;margin:25px 0 12px;;line-height:1.2;">AI Services</h1>
        <p style="opacity:0.75;margin:0;font-size:14px;">
            <a href="<?= url('/') ?>" style="color:#72aee6;">Home</a> &nbsp;/&nbsp;
            <span>Services</span>
        </p>
    </div>
</section>
<!-- end main-content --> 
<!-- Services Section -->
<section class="service-wrapper service-two section-padding section-bg-3">
  <div class="shape">
    <div class="light-shape"></div>
    <h2 class="title-shadow style-2">Services</h2>
    <img src="<?= asset('images/home-2/service/shape-01.webp') ?>" alt="" class="shape-1 left-to-right-ani d-none d-xl-block">
  </div>
  <div class="auto-container">
    <div class="row">
      <?php foreach ($dynamic_services as $i => $svc): ?>
        <div class="col-xl-4 col-md-6 wow fadeInUp" data-wow-delay=".<?= $i + 1 ?>s">
          <div class="service-block-two">
            <div class="head">
              <div class="icon"><i class="<?= attr($svc['icon_class']) ?>"></i></div>
              <!-- <div class="num"><?= sprintf('%02d', 6 + $i + 1) ?></div> -->
            </div>
            <div class="content">
              <h3 class="title"><a href="<?= url('/services/' . attr($svc['slug'])) ?>"><?= e($svc['name']) ?></a></h3>
              <p class="text"><?= e(truncate_text($svc['excerpt'], 85)) ?></p>
              <a href="<?= url('/services/' . attr($svc['slug'])) ?>" class="theme-btn-main theme-btn-main2">
                <span class="theme-btn-arrow-left"> <i class="far fa-long-arrow-right "></i></span>
                <span class="theme-btn">Read More</span>
                <span class="theme-btn-arrow-right"><i class="far fa-long-arrow-right"></i></span>
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
      <div class="col-xl-4 col-md-6 wow fadeInUp" data-wow-delay=".6s">
        <div class="service-block-two">
          <div class="head">
            <div class="icon"><i class="flaticon-tech flaticon-tech-interaction-1"></i></div>
            <div class="num">06</div>
          </div>
          <div class="content">
            <h3 class="title"><a href="<?= url('/hire') ?>">Hire AI Engineers</a></h3>
            <p class="text"><?= e(truncate_text('Senior AI engineers ready to join your team in 24 hours. Production-first systems, transparent delivery.', 85)) ?></p>
            <a href="<?= url('/hire') ?>" class="theme-btn-main theme-btn-main2">
              <span class="theme-btn-arrow-left"> <i class="far fa-long-arrow-right "></i></span>
              <span class="theme-btn">Read More</span>
              <span class="theme-btn-arrow-right"><i class="far fa-long-arrow-right"></i></span>
            </a>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>
<!--End Services Section -->

 <!-- Main Footer -->
