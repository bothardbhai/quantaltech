<?php

/**
 * Header partial — opens the document, includes <head>, navigation, and the
 * structural wrappers. Closes nothing structural here; that's footer.php's job.
 *
 * Variables it reads (all optional):
 *   $page_title        — string. Page title (overrides DB).
 *   $page_description  — string. Meta description (overrides DB).
 *   $active_page       — string. Drives nav active class. e.g. 'home','about','services','blog','contact'
 *   $body_class        — string. Extra CSS class on <body>.
 *   $page_seo          — array.  Set by the router from the DB.
 */
if (!isset($page_seo)) {
    $page_seo = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-TNH27Q9Z');</script>
    <!-- End Google Tag Manager -->


    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php require PARTIALS_DIR . '/seo-head.php'; ?>

    <!-- Stylesheets -->
    <link href="<?= asset('css/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/style.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/responsive.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/live-search.css') ?>" rel="stylesheet">

    <!-- Favicon -->
    <link rel="shortcut icon" href="<?= asset('images/brain-logo.png') ?>" type="image/x-icon">
    <link rel="icon"          href="<?= asset('images/brain-logo.png') ?>" type="image/x-icon">
</head>
<body<?= !empty($body_class) ? ' class="' . attr($body_class) . '"' : '' ?>>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-TNH27Q9Z"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->


<div class="page-wrapper">
<!-- Preloader removed -->

<!-- Custom Cursor Wrapper -->
<div id="custom-cursor-wrapper" class="tp-cursor">
    <div id="cursor-outer">
        <div id="cursorDot"></div>
    </div>
</div>

<div class="scroll-to-top scroll-to-target" data-target="html"><span class="fas fa-angle-up"></span></div>

<header class="main-header header-style-one header-1">
    <div class="header-lower">
        <div class="main-box">
            <div class="nav-outer">
                <div class="header-left">
                    <div class="logo">
                        <a href="<?= attr(BASE_URL) ?>" title="<?= attr(SITE_NAME) ?>" class="brand-logo">
                            <img src="<?= asset('images/brain-logo.png') ?>" alt="<?= attr(SITE_NAME) ?>" title="<?= attr(SITE_NAME) ?>" style="max-height:46px;width:auto;">
                            <span class="brand-title">Quantal AI</span>
                        </a>
                    </div>
                    <nav class="nav main-menu">
                        <?php require PARTIALS_DIR . '/nav-menu.php'; ?>
                    </nav>
                </div>
                <div class="outer-box">
                    <div class="ui-btn-outer">
                        <div class="ui-btn-search">
                            <button class="ui-btn ui-btn search-btn main-header__search search-toggler d-none d-lg-inline-block" aria-label="Search">
                                <i class="fa-regular fa-magnifying-glass"></i>
                            </button>
                            <a href="<?= url('/contact') ?>" class="header-btn d-none d-lg-block">Let&rsquo;s Talk</a>
                        </div>
                    </div>
                    <div class="mobile-nav-toggler">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Header Lower -->

    <!-- Mobile Menu (theme JS clones the main menu into here) -->
    <div class="mobile-menu">
        <div class="menu-backdrop"></div>
        <nav class="menu-box">
            <div class="upper-box">
                <div class="nav-logo">
                    <a href="<?= attr(BASE_URL) ?>" class="brand-logo"><img src="<?= asset('images/brain-logo.png') ?>" alt="<?= attr(SITE_NAME) ?>" style="max-height:42px;"><span class="brand-title">Quantal AI</span></a>
                </div>
                <div class="close-btn"><i class="icon fa fa-times"></i></div>
            </div>
            <ul class="navigation clearfix">
                <!-- Populated automatically by theme JS -->
            </ul>
            <ul class="contact-list-one">
                <li>
                    <div class="contact-info-box">
                        <i class="icon lnr-icon-phone-handset"></i>
                        <span class="title">Call Now</span>
                        <a href="tel:+13158093225">+1 315 809 3225</a>
                    </div>
                </li>
                <li>
                    <div class="contact-info-box">
                        <span class="icon lnr-icon-envelope1"></span>
                        <span class="title">Send Email</span>
                        <a href="mailto:contact@quantaltech.ai">contact@quantaltech.ai</a>
                    </div>
                </li>
                <?php

                /*
                 * <li>
                 *     <div class="contact-info-box">
                 *         <span class="icon lnr-icon-clock"></span>
                 *         <span class="title">Hours</span>
                 *         Mon &ndash; Fri 9:00 &ndash; 18:00 IST
                 *     </div>
                 * </li>
                 */
                ?>
            </ul>
            <ul class="social-links">
                <li><a href="https://www.linkedin.com/company/quantaltech-ai" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a></li>
            </ul>
        </nav>
    </div>
    <!-- End Mobile Menu -->

    <!-- Header Search -->
    <div class="search-popup">
        <span class="search-back-drop"></span>
        <button class="close-search" aria-label="Close search"><span class="fa fa-times"></span></button>
        <div class="search-inner">
            <form method="get" action="<?= url('/search') ?>" id="site-search-form" autocomplete="off">
                <div class="form-group">
                    <input type="search" name="q" id="site-search-input" placeholder="Search..." required
                           aria-autocomplete="list" aria-controls="site-search-results" aria-expanded="false">
                    <button type="submit"><i class="fa fa-search"></i></button>
                </div>
            </form>
            <div id="site-search-results" class="site-search-results" role="listbox" hidden></div>
        </div>
    </div>

    <!-- Sticky Header -->
    <div class="sticky-header">
        <div class="large-container">
            <div class="inner-container">
                <div class="logo">
                    <a href="<?= attr(BASE_URL) ?>" title="<?= attr(SITE_NAME) ?>" class="brand-logo">
                        <img src="<?= asset('images/brain-logo.png') ?>" alt="<?= attr(SITE_NAME) ?>" title="<?= attr(SITE_NAME) ?>" style="max-height:42px;width:auto;">
                        <span class="brand-title">Quantal AI</span>
                    </a>
                </div>
                <div class="nav-outer">
                    <nav class="main-menu">
                        <div class="navbar-collapse show collapse clearfix">
                            <ul class="navigation clearfix">
                                <!-- Populated by theme JS -->
                            </ul>
                        </div>
                    </nav>
                </div>
                <div class="mobile-nav-toggler">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>
    </div>
    <!-- End Sticky Menu -->
</header>
<!-- End Main Header -->
