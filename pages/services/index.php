<?php
/** Based on theme template - content swapped to Quantal AI. */
$page_title = 'AI Services - Quantal AI';
$active_page = 'services';
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
      <div class="col-xl-4 col-md-6 wow fadeInUp" data-wow-delay=".1s">
        <div class="service-block-two">
          <div class="head">
            <div class="icon"><i class="flaticon-tech flaticon-tech-interaction-1"></i></div>
            <div class="num">01</div>
          </div>
          <div class="content">
            <h3 class="title"><a href="<?= url('/services/voice') ?>">Voice AI</a></h3>
            <p class="text">Conversational, multilingual, sentiment-aware voice agents that transform customer interactions and lead nurturing.</p>
            <a href="<?= url('/services/voice') ?>" class="theme-btn-main theme-btn-main2">
              <span class="theme-btn-arrow-left"> <i class="far fa-long-arrow-right "></i></span>
              <span class="theme-btn">Read More</span>
              <span class="theme-btn-arrow-right"><i class="far fa-long-arrow-right"></i></span>
            </a>
          </div>
        </div>
      </div>
      <div class="col-xl-4 col-md-6 wow fadeInUp" data-wow-delay=".2s">
        <div class="service-block-two">
          <div class="head">
            <div class="icon"><i class="flaticon-tech flaticon-tech-interaction-1"></i></div>
            <div class="num">02</div>
          </div>
          <div class="content">
            <h3 class="title"><a href="<?= url('/services/text') ?>">Text AI</a></h3>
            <p class="text">Intelligent chatbots, NLP-powered insights, AI email marketing, and personalized outreach.</p>
            <a href="<?= url('/services/text') ?>" class="theme-btn-main theme-btn-main2">
              <span class="theme-btn-arrow-left"> <i class="far fa-long-arrow-right "></i></span>
              <span class="theme-btn">Read More</span>
              <span class="theme-btn-arrow-right"><i class="far fa-long-arrow-right"></i></span>
            </a>
          </div>
        </div>
      </div>
      <div class="col-xl-4 col-md-6 wow fadeInUp" data-wow-delay=".3s">
        <div class="service-block-two">
          <div class="head">
            <div class="icon"><i class="flaticon-tech flaticon-tech-interaction-1"></i></div>
            <div class="num">03</div>
          </div>
          <div class="content">
            <h3 class="title"><a href="<?= url('/services/image') ?>">Image / Document AI</a></h3>
            <p class="text">KYC verification, fraud detection, signature authentication, and invoice processing with computer vision.</p>
            <a href="<?= url('/services/image') ?>" class="theme-btn-main theme-btn-main2">
              <span class="theme-btn-arrow-left"> <i class="far fa-long-arrow-right "></i></span>
              <span class="theme-btn">Read More</span>
              <span class="theme-btn-arrow-right"><i class="far fa-long-arrow-right"></i></span>
            </a>
          </div>
        </div>
      </div>
      <div class="col-xl-4 col-md-6 wow fadeInUp" data-wow-delay=".4s">
        <div class="service-block-two">
          <div class="head">
            <div class="icon"><i class="flaticon-tech flaticon-tech-interaction-1"></i></div>
            <div class="num">04</div>
          </div>
          <div class="content">
            <h3 class="title"><a href="<?= url('/services/process-auto') ?>">Process Automation</a></h3>
            <p class="text">Reporting, reconciliations, compliance, and workflow automation that streamlines operations.</p>
            <a href="<?= url('/services/process-auto') ?>" class="theme-btn-main theme-btn-main2">
              <span class="theme-btn-arrow-left"> <i class="far fa-long-arrow-right "></i></span>
              <span class="theme-btn">Read More</span>
              <span class="theme-btn-arrow-right"><i class="far fa-long-arrow-right"></i></span>
            </a>
          </div>
        </div>
      </div>
      <div class="col-xl-4 col-md-6 wow fadeInUp" data-wow-delay=".5s">
        <div class="service-block-two">
          <div class="head">
            <div class="icon"><i class="flaticon-tech flaticon-tech-interaction-1"></i></div>
            <div class="num">05</div>
          </div>
          <div class="content">
            <h3 class="title"><a href="<?= url('/services') ?>">AI Agents</a></h3>
            <p class="text">Autonomous agents that orchestrate multi-step workflows across CRM, calendar, email, and data systems.</p>
            <a href="<?= url('/services') ?>" class="theme-btn-main theme-btn-main2">
              <span class="theme-btn-arrow-left"> <i class="far fa-long-arrow-right "></i></span>
              <span class="theme-btn">Read More</span>
              <span class="theme-btn-arrow-right"><i class="far fa-long-arrow-right"></i></span>
            </a>
          </div>
        </div>
      </div>
      <div class="col-xl-4 col-md-6 wow fadeInUp" data-wow-delay=".6s">
        <div class="service-block-two">
          <div class="head">
            <div class="icon"><i class="flaticon-tech flaticon-tech-interaction-1"></i></div>
            <div class="num">06</div>
          </div>
          <div class="content">
            <h3 class="title"><a href="<?= url('/hire') ?>">Hire AI Engineers</a></h3>
            <p class="text">Senior AI engineers ready to join your team in 24 hours. Production-first systems, transparent delivery.</p>
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
