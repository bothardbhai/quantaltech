<?php

/**
 * Hire - content from quantaltech.ai/hire, rebuilt on theme/page-about.html
 * markup. Same sections, animation hooks, and shape decorations as the theme;
 * only copy and content imagery are swapped.
 */
$page_title = !empty($page_seo['title']) ? $page_seo['title'] : 'Hire Experienced AI Engineers';
$active_page = 'hire';
?>

<?php

/*
 * <!-- Start main-content -->
 * <section class="page-banner news-banner" style="padding:120px 0 80px;background:#1d2327;color:#fff;text-align:center;" hidden>
 *     <div class="container">
 *         <h1 style="color:#fff;font-size:36px;margin:0 0 14px;line-height:1.2;">Hire AI Engineers</h1>
 *         <p style="opacity:0.75;margin:0;font-size:14px;">
 *             <a href="<?= url('/') ?>" style="color:#72aee6;">Home</a> &nbsp;/&nbsp;
 *             <span>Hire</span>
 *         </p>
 *     </div>
 * </section>
 * <!-- end main-content -->
 */
?>

<!-- Start main-content -->
<section class="page-banner news-banner" style="padding:120px 0 80px;background:#1d2327;color:#fff;text-align:center;">
    <div class="container">
        <h1 style="color:#fff;font-size:36px;margin:25px 0 12px;line-height:1.2;"><?= e($page_title) ?></h1>
        <p style="opacity:0.75;margin:0;font-size:14px;">
            <a href="<?= url('/') ?>" style="color:#72aee6;">Home</a> &nbsp;/&nbsp;
            <a href="" style="color:#72aee6;">Hire with Us</a> &nbsp;/&nbsp;
            <span>Hire AI Engineers</span>
        </p>
    </div>
</section>
<!-- end main-content -->

<!-- About Section -->
<section class="about-section section-padding fix">
	<div class="about-vector tm-gsap-animate-circle d-none d-xxl-block"><img src="<?= asset('images/home-1/about/about-vector.png') ?>" alt="img"></div>
	<div class="about-shape d-none d-xxl-block"><img src="<?= asset('images/home-1/about/about-shape.png') ?>" alt="img"></div>
	<div class="light-bg d-none d-xxl-block"><img src="<?= asset('images/home-1/about/light-bg.png') ?>" alt="img"></div>
	<h2 class="stoke-title">Hire AI</h2>
	<div class="container">
		<div class="row g-4">
		<div class="col-xl-6">
			<div class="about-image-items1">
			<div class="about-thumb fix img-reveal">
				<img data-speed=".7" src="<?= asset('images/quantal/services/hero_banner.png') ?>" alt="img">
			</div>
			<!-- <div class="about-image-2 fix">
				<img data-speed=".7s" src="<?= asset('images/home-1/about/about-2.jpg') ?>" alt="img">
			</div> -->
			<!-- <div class="about-circle-box">
				<div class="circle-text">
				<svg viewBox="0 0 250 250">
					<defs>
					<path id="circlePath1" d="M125,125 m-95,0 a95,95 0 1,1 190,0 a95,95 0 1,1 -190,0" />
					</defs>
					<text>
					<textPath xlink:href=" #circlePath" startOffset="0%">Top Rated on Upwork ~ Top Rated on Upwork ~</textPath>
					</text>
				</svg>
				</div>
				<div class="experience count-box">
				<h3 class="num"><span class="count-text" data-speed="3000" data-stop="100" data-lag="0">100</span>%</h3>
				</div>
			</div> -->
			</div>
		</div>
		<div class="col-xl-6">
			<div class="about-content1">
			<div class="section-title mb-0">
				<div class="sub-title text-left">
				<svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M6.81319 14.6759C6.83947 14.8971 7.16053 14.8971 7.18681 14.6759L7.40705 12.8197C7.69143 10.4229 9.58112 8.53323 11.9779 8.24884L13.834 8.0286C14.0553 8.00233 14.0553 7.68127 13.834 7.65499L11.9779 7.43475C9.58112 7.15036 7.69143 5.26068 7.40705 2.86391L7.18681 1.00776C7.16053 0.786476 6.83947 0.786476 6.81319 1.00776L6.59296 2.86391C6.30857 5.26068 4.41888 7.15036 2.02209 7.43475L0.165943 7.65499C-0.0553144 7.68127 -0.0553144 8.00233 0.165943 8.0286L2.02209 8.24884C4.41888 8.53323 6.30857 10.4229 6.59296 12.8197L6.81319 14.6759Z" fill="currentColor" />
				</svg>
				<span>Why Hire Us</span>
				</div>
				<h2 class="title split-text split-in-right">Hire Senior AI Engineers <span>On Demand</span></h2>
			</div>
			<p class="about-text wow fadeInUp" data-wow-delay=".3s">Senior AI engineers with 100% job success and 15+ years of combined expertise. We help businesses turn AI into real, measurable outcomes - from strategy and custom development to automation, deployed end-to-end.</p>
			<div class="about-box wow fadeInUp" data-wow-delay=".5s">
				<h6 class="para-text">Fast onboarding without long hiring cycles. Production-first systems, real-world LLM and data experience, and 100% transparent communication and delivery from kickoff to launch.</h6>
			</div>
			<a class="theme-btn-main wow fadeInUp" data-wow-delay=".9s" href="<?= url('/contact') ?>">
				<span class="theme-btn-arrow-left"> <i class="far fa-long-arrow-right "></i> </span>
				<span class="theme-btn ">Hire Gen AI Engineer</span>
				<span class="theme-btn-arrow-right"> <i class="far fa-long-arrow-right"></i> </span>
			</a>
			</div>
		</div>
		</div>
		<div class="about-image-video zoom-effect-style">
		<div class="about-video bg-cover tm-gsap-img-parallax mt-100" style="background-image: url('<?= asset('images/home-1/about-image.jpg') ?>');"></div>
		<div class="video-outer">
			<a href="<?= url('/contact') ?>" class="play-now">
			<i class="icon fa-solid fa-arrow-right"></i>
			<span class="ripple"></span>
			</a>
			<h3 class="watch-title wow fadeInUp" data-wow-delay=".3s">Talk to an AI Expert</h3>
		</div>
		</div>
	</div>
</section>
<!-- End About Section -->

<!-- feature Section Start -->
<!-- <section class="feature-section1 section-padding bb-top fix">
	<div class="scroll-text">
		<h2 class="stoke-title text2">
		Features
		</h2>
	</div>

	<div class="line-shape">
		<img src="<?= asset('images/home-1/features/line-shape.png') ?>" alt="img">
	</div>
	<div class="ellipse-shape">
		<img src="<?= asset('images/home-1/features/ellipse-bg.png') ?>" alt="img">
	</div>
	<div class="container">
		<div class="row mb-60">
			<div class="col-lg-5">
				<div class="section-title">
					<div class="sub-title">
					<svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path
						d="M6.81319 14.6759C6.83947 14.8971 7.16053 14.8971 7.18681 14.6759L7.40705 12.8197C7.69143 10.4229 9.58112 8.53323 11.9779 8.24884L13.834 8.0286C14.0553 8.00233 14.0553 7.68127 13.834 7.65499L11.9779 7.43475C9.58112 7.15036 7.69143 5.26068 7.40705 2.86391L7.18681 1.00776C7.16053 0.786476 6.83947 0.786476 6.81319 1.00776L6.59296 2.86391C6.30857 5.26068 4.41888 7.15036 2.02209 7.43475L0.165943 7.65499C-0.0553144 7.68127 -0.0553144 8.00233 0.165943 8.0286L2.02209 8.24884C4.41888 8.53323 6.30857 10.4229 6.59296 12.8197L6.81319 14.6759Z"
						fill="currentColor" />
					</svg>

					<span>Features</span>
					</div>
					<h2 class="title split-text split-in-right">
					What You Get When <span>You Hire With Us</span>
					</h2>
				</div>
			</div>
			</div>
		<div class="row g-4">
			<div class="col-lg-6">
			<div id="rotatable-image" class="feature-image-1 appear_left">
				<img src="<?= asset('images/home-1/features/feature-image.png') ?>" alt="img">
			</div>
			</div>
			<div class="col-lg-6">
			<ul class="feature-list-items wow fadeInUp" data-wow-delay=".3s">
				<li>
					<h4 class="title">
					Website Design
					</h4>
					<div class="list-item">
					<span class="text">Online Branding</span>
					<span class="dot"></span>
					<span class="text">App Promotion</span>
					</div>
				</li>
				<li>
					<h4 class="title">
					App Design
					</h4>
					<div class="list-item">
					<span class="text">App Promotion</span>
					<span class="dot"></span>
					<span class="text">Online Branding</span>
					</div>
				</li>
				<li>
					<h4 class="title">
					Brand Design
					</h4>
					<div class="list-item">
					<span class="text">App Promotion</span>
					<span class="dot"></span>
					<span class="text">App Promotion</span>
					</div>
				</li>
				<li>
					<h4 class="title">
					Product Design
					</h4>
					<div class="list-item">
					<span class="text">Product Branding</span>
					<span class="dot"></span>
					<span class="text">Event Promotion</span>
					</div>
				</li>
			</ul>
			</div>
		</div>
	</div>
</section> -->

<!-- Brand Section Start -->
<div class="brand-section-2 section-padding">
	<div class="container">
		<div class="brand-wrap-2">
			<div class="text-box">
				<p>Trusted by <b>1200+</b> founders & business owners</p>
			</div>
			
			<div class="swiper brand-slider2">
				<div class="swiper-wrapper">
					<div class="swiper-slide">
						<div class="brand-img2 image-fluid">
							<img src="<?= asset('images/quantal/clients/icici.png') ?>" alt="ICICI">
						</div>
					</div>
					<div class="swiper-slide">
						<div class="brand-img2 image-fluid">
							<img src="<?= asset('images/quantal/clients/waterfield.png') ?>" alt="Waterfield">
						</div>
					</div>
					<div class="swiper-slide">
						<div class="brand-img2 image-fluid">
							<img src="<?= asset('images/quantal/clients/grip.png') ?>" alt="Grip">
						</div>
					</div>
					<div class="swiper-slide">
						<div class="brand-img2 image-fluid">
							<img src="<?= asset('images/quantal/clients/armstrong.png') ?>" alt="Armstrong">
						</div>
					</div>
					<div class="swiper-slide">
						<div class="brand-img2 image-fluid">
							<img src="<?= asset('images/quantal/clients/elunic.jpg') ?>" alt="Elunic">
						</div>
					</div>
					<div class="swiper-slide">
						<div class="brand-img2 image-fluid">
							<img src="<?= asset('images/quantal/clients/nortmaq.png') ?>" alt="Nortmaq">
						</div>
					</div>
					<div class="swiper-slide">
						<div class="brand-img2 image-fluid">
							<img src="<?= asset('images/quantal/clients/xanevo.jpg') ?>" alt="Xanevo">
						</div>
					</div>
					<div class="swiper-slide">
						<div class="brand-img2 image-fluid">
							<img src="<?= asset('images/quantal/clients/ackuity.jpg') ?>" alt="Ackuity">
						</div>
					</div>
					<div class="swiper-slide">
						<div class="brand-img2 image-fluid">
							<img src="<?= asset('images/quantal/clients/benow_logo.png') ?>" alt="Benow">
						</div>
					</div>
					<div class="swiper-slide">
						<div class="brand-img2 image-fluid">
							<img src="<?= asset('images/quantal/clients/brandtrust.jpg') ?>" alt="Brand Trust">
						</div>
					</div>
					<div class="swiper-slide">
						<div class="brand-img2 image-fluid">
							<img src="<?= asset('images/quantal/clients/civiq.png') ?>" alt="Civiq">
						</div>
					</div>
					<div class="swiper-slide">
						<div class="brand-img2 image-fluid">
							<img src="<?= asset('images/quantal/clients/corsano.png') ?>" alt="Corsano">
						</div>
					</div>
					<div class="swiper-slide">
						<div class="brand-img2 image-fluid">
							<img src="<?= asset('images/quantal/clients/desert_recovery_center.png') ?>" alt="Desert Recovery Center">
						</div>
					</div>
					<div class="swiper-slide">
						<div class="brand-img2 image-fluid">
							<img src="<?= asset('images/quantal/clients/northwell-health.png') ?>" alt="Northwell Health">
						</div>
					</div>
					<div class="swiper-slide">
						<div class="brand-img2 image-fluid">
							<img src="<?= asset('images/quantal/clients/osteopathic_healing_hands.png') ?>" alt="Osteopathic Healing Hands">
						</div>
					</div>
					<div class="swiper-slide">
						<div class="brand-img2 image-fluid">
							<img src="<?= asset('images/quantal/clients/sanofi.png') ?>" alt="Sanofi">
						</div>
					</div>
					<div class="swiper-slide">
						<div class="brand-img2 image-fluid">
							<img src="<?= asset('images/quantal/clients/street.png') ?>" alt="Street">
						</div>
					</div>
					<div class="swiper-slide">
						<div class="brand-img2 image-fluid">
							<img src="<?= asset('images/quantal/clients/tbl.webp') ?>" alt="TBL">
						</div>
					</div>
				</div>
			</div>
			<!-- <div class="swiper brand-slider2">
				<div class="swiper-wrapper">
					<div class="swiper-slide">
						<div class="brand-img2">
							<img src="<?= asset('images/home-2/brand/brand-1.png') ?>" alt="img">
						</div>
					</div>
					<div class="swiper-slide">
						<div class="brand-img2">
							<img src="<?= asset('images/home-2/brand/brand-2.png') ?>" alt="img">
						</div>
					</div>
					<div class="swiper-slide">
						<div class="brand-img2">
							<img src="<?= asset('images/home-2/brand/brand-3.png') ?>" alt="img">
						</div>
					</div>
					<div class="swiper-slide">
						<div class="brand-img2">
							<img src="<?= asset('images/home-2/brand/brand-4.png') ?>" alt="img">
						</div>
					</div>
					<div class="swiper-slide">
						<div class="brand-img2">
							<img src="<?= asset('images/home-2/brand/brand-5.png') ?>" alt="img">
						</div>
					</div>
				</div>
			</div> -->
		</div>
	</div>
</div>

<!-- Services Section -->
<section class="service-wrapper service-two section-padding section-bg-3">
	<div class="shape">
		<div class="light-shape"></div>
		<h2 class="title-shadow style-2">service</h2>
		<img src="<?= asset('images/home-2/service/shape-01.webp') ?>" alt="" class="shape-1 left-to-right-ani d-none d-xl-block">
		<img src="<?= asset('images/home-2/service/shape-02.webp') ?>" alt="" class="shape-2 tm-gsap-animate-circle d-none d-xl-block">
	</div>
	<div class="auto-container">
		<div class="section-title text-left">
		<div class="sub-title">
			<svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path d="M6.81319 14.6759C6.83947 14.8971 7.16053 14.8971 7.18681 14.6759L7.40705 12.8197C7.69143 10.4229 9.58112 8.53323 11.9779 8.24884L13.834 8.0286C14.0553 8.00233 14.0553 7.68127 13.834 7.65499L11.9779 7.43475C9.58112 7.15036 7.69143 5.26068 7.40705 2.86391L7.18681 1.00776C7.16053 0.786476 6.83947 0.786476 6.81319 1.00776L6.59296 2.86391C6.30857 5.26068 4.41888 7.15036 2.02209 7.43475L0.165943 7.65499C-0.0553144 7.68127 -0.0553144 8.00233 0.165943 8.0286L2.02209 8.24884C4.41888 8.53323 6.30857 10.4229 6.59296 12.8197L6.81319 14.6759Z" fill="currentColor" />
			</svg>
			<span>What We Build</span>
		</div>
		<h2 class="title split-text split-in-right text-left">AI Solutions We <br><span>Build &amp; Deploy</span></h2>
		</div>
		<a class="theme-btn-main" href="<?= url('/services') ?>">
		<span class="theme-btn-arrow-left"> <i class="far fa-long-arrow-right "></i></span>
		<span class="theme-btn ">See All Solutions</span>
		<span class="theme-btn-arrow-right"><i class="far fa-long-arrow-right"></i></span>
		</a>
		<div class="row">
			<div class="col-xl-4 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay=".3s">
				<div class="service-block-two style-1">
					<div class="head">
						<div class="icon">
							<i class="flaticon-tech flaticon-tech-interaction-1"></i>
						</div>
						<div class="num">01</div>
					</div>
					<div class="content">
						<h3 class="title"><a href="<?= url('/services/voice') ?>">Voice AI Solutions</a></h3>
						<p class="text">
							Conversational, multilingual, sentiment-aware voice agents for customer service, lead nurturing, and reminders.
						</p>
						<a href="<?= url('/services/voice') ?>" class="theme-btn-main theme-btn-main2">
							<span class="theme-btn-arrow-left"> <i class="far fa-long-arrow-right "></i>
							</span>
							<span class="theme-btn">Read More</span>
							<span class="theme-btn-arrow-right">
								<i class="far fa-long-arrow-right"></i>
							</span>
						</a>
					</div>
				</div>
			</div>
			<div class="col-xl-4 col-lg-6  col-md-6 wow fadeInUp" data-wow-delay=".5s">
				<div class="items1">
					<div class="service-block-two">
						<div class="head">
							<div class="icon">
								<i class="flaticon-tech flaticon-tech-interaction-1"></i>
							</div>
							<div class="num">02</div>
						</div>
						<div class="content">
							<h3 class="title"><a href="<?= url('/services/text') ?>">Text AI Solutions</a></h3>
							<p class="text">
								Intelligent chatbots, NLP-powered market intelligence, AI email marketing, personalized outreach.
							</p>
							<a href="<?= url('/services/text') ?>" class="theme-btn-main theme-btn-main2">
								<span class="theme-btn-arrow-left"> <i class="far fa-long-arrow-right "></i>
								</span>
								<span class="theme-btn">Read More</span>
								<span class="theme-btn-arrow-right">
									<i class="far fa-long-arrow-right"></i>
								</span>
							</a>
						</div>
					</div>
					<div class="service-block-two">
						<div class="head">
							<div class="icon">
								<i class="flaticon-tech flaticon-tech-interaction-1"></i>
							</div>
							<div class="num">04</div>
						</div>
						<div class="content">
							<h3 class="title"><a href="<?= url('/services/image') ?>">Image / Document AI</a></h3>
							<p class="text">
								KYC verification, fraud detection, signature verification, OCR, and intelligent invoice processing.
							</p>
							<a href="<?= url('/services/image') ?>" class="theme-btn-main theme-btn-main2">
								<span class="theme-btn-arrow-left"> <i class="far fa-long-arrow-right "></i>
								</span>
								<span class="theme-btn">Read More</span>
								<span class="theme-btn-arrow-right">
									<i class="far fa-long-arrow-right"></i>
								</span>
							</a>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-4 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay=".7s">
				<div class="items2">
					<div class="service-block-two">
						<div class="head">
							<div class="icon">
								<i class="flaticon-tech flaticon-tech-interaction-1"></i>
							</div>
							<div class="num">03</div>
						</div>
						<div class="content">
							<h3 class="title"><a href="<?= url('/services/process-auto') ?>">Process Automation</a></h3>
							<p class="text">
								Reporting, reconciliations, compliance monitoring, and end-to-end workflow automation that removes manual work.
							</p>
							<a href="<?= url('/services/process-auto') ?>" class="theme-btn-main theme-btn-main2">
								<span class="theme-btn-arrow-left"> <i class="far fa-long-arrow-right "></i>
								</span>
								<span class="theme-btn">Read More</span>
								<span class="theme-btn-arrow-right">
									<i class="far fa-long-arrow-right"></i>
								</span>
							</a>
						</div>
					</div>
					<div class="service-block-two">
						<div class="head">
							<div class="icon">
								<i class="flaticon-tech flaticon-tech-interaction-1"></i>
							</div>
							<div class="num">05</div>
						</div>
						<div class="content">
							<h3 class="title"><a href="<?= url('/contact') ?>">Custom AI Engineering</a></h3>
							<p class="text">
								Bespoke LLM applications, RAG pipelines, AI agents, and integrations into your existing stack - built and shipped to production.
							</p>
							<a href="<?= url('/contact') ?>" class="theme-btn-main theme-btn-main2">
								<span class="theme-btn-arrow-left"> <i class="far fa-long-arrow-right "></i>
								</span>
								<span class="theme-btn">Read More</span>
								<span class="theme-btn-arrow-right">
									<i class="far fa-long-arrow-right"></i>
								</span>
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
<!--End Services Section -->
<div class="multiple-section section-bg-2 section-padding pb-0">
	<div class="inner section-bg section-padding">
		<div class="bg-image bg-cover" style="background-image: url('<?= asset('images/home-1/multiple-sec-bg.jpg') ?>');"> </div>
		<div class="shape d-none d-xxl-block">
		<img src="<?= asset('images/home-1/multiple-sec-shape-01.webp') ?>" alt="AI engineering process illustration - Quantal AI step-by-step production AI development" class="shape-1 tm-gsap-animate-circle">
		<div class="light-shape"></div>
		<div class="light-shape2"></div>
		</div>
		<!-- Work Section Start -->
		<section class="work-wrapper work-one">
		<div class="auto-container">
			<div class="section-title text-center">
			<div class="sub-title">
				<svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M6.81319 14.6759C6.83947 14.8971 7.16053 14.8971 7.18681 14.6759L7.40705 12.8197C7.69143 10.4229 9.58112 8.53323 11.9779 8.24884L13.834 8.0286C14.0553 8.00233 14.0553 7.68127 13.834 7.65499L11.9779 7.43475C9.58112 7.15036 7.69143 5.26068 7.40705 2.86391L7.18681 1.00776C7.16053 0.786476 6.83947 0.786476 6.81319 1.00776L6.59296 2.86391C6.30857 5.26068 4.41888 7.15036 2.02209 7.43475L0.165943 7.65499C-0.0553144 7.68127 -0.0553144 8.00233 0.165943 8.0286L2.02209 8.24884C4.41888 8.53323 6.30857 10.4229 6.59296 12.8197L6.81319 14.6759Z" fill="currentColor" />
				</svg>
				<span>How We Work</span>
			</div>
			<h2 class="title split-text split-in-right">How We Take AI from <br> <span>Idea to Production</span></h2>
			</div>
			<div class="cards-content">
			<div class="row">
				<div class="col-xl-3 col-lg-4 col-md-6 wow fadeInUp" data-wow-delay=".2s">

				<div class="work-block">
					<div class="step-btn">Step 01</div>
					<div class="inner-box">
					<div class="icon"><i class="flaticon-tech flaticon-tech-interaction-1"></i></div>
					<h4 class="title">Discovery &amp; Strategy</h4>
					<p class="text">Goals, success metrics, technical fit, build-or-buy decisions, and a phased delivery plan.</p>
					</div>
				</div>
				</div>
				<div class="col-xl-3 col-lg-4 col-md-6 wow fadeInUp" data-wow-delay=".4s">
				<div class="work-block">
					<div class="step-btn">Step 02</div>
					<div class="inner-box">
					<div class="icon"><i class="flaticon-tech flaticon-tech-interaction-1"></i></div>
					<h4 class="title">Architecture &amp; Design</h4>
					<p class="text">Model selection, data flow, integrations, prompt and evaluation harness, security and compliance design.</p>
					</div>
				</div>
				</div>
				<div class="col-xl-3 col-lg-4 col-md-6 wow fadeInUp" data-wow-delay=".6s">
				<div class="work-block">
					<div class="step-btn">Step 03</div>
					<div class="inner-box">
					<div class="icon"><i class="flaticon-tech flaticon-tech-interaction-1"></i></div>
					<h4 class="title">Build &amp; Deploy</h4>
					<p class="text">Iterative implementation, real-data testing, supervised launch, monitoring, and post-launch support.</p>
					</div>
				</div>
				</div>
				<div class="col-xl-3 col-lg-4 col-md-6 wow fadeInUp" data-wow-delay=".8s">
				<div class="work-block">
					<div class="step-btn">Step 04</div>
					<div class="inner-box">
					<div class="icon"><i class="flaticon-tech flaticon-tech-interaction-1"></i></div>
					<h4 class="title">Optimize &amp; Scale</h4>
					<p class="text">Continuous improvement on accuracy, latency and cost. Scale across new languages, regions, or use cases.</p>
					</div>
				</div>
				</div>
			</div>
			</div>
		</div>
		</section>

		<!-- Testimonial Section Start -->
		<section class="testimonial-wrapper testimonial-one section-padding">
		<div class="auto-container">
			<div class="row g-sm-4">
			<div class="col-xl-5">
				<div class="left-content">
				<div class="circle-box">
					<div class="highlight-arc"></div>
					<div class="circle-text">
					<svg viewBox="0 0 250 250">
						<defs>
						<path id="circlePath" d="M125,125 m-95,0 a95,95 0 1,1 190,0 a95,95 0 1,1 -190,0" />
						</defs>
						<text>
						<textPath xlink:href=" #circlePath" startOffset="0%">
							- Trusted by clients - Trusted by clients
						</textPath>
						</text>
					</svg>
					</div>
					<a href="#" class="icon">
					<img src="<?= asset('images/home-1/testimonial/comma.webp') ?>" alt="Testimonials ">
					</a>
				</div>
				</div>
			</div>
			<div class="col-xl-7">
				<div class="slider-box">
				<div class="section-title">
					<div class="sub-title">
					<svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M6.81319 14.6759C6.83947 14.8971 7.16053 14.8971 7.18681 14.6759L7.40705 12.8197C7.69143 10.4229 9.58112 8.53323 11.9779 8.24884L13.834 8.0286C14.0553 8.00233 14.0553 7.68127 13.834 7.65499L11.9779 7.43475C9.58112 7.15036 7.69143 5.26068 7.40705 2.86391L7.18681 1.00776C7.16053 0.786476 6.83947 0.786476 6.81319 1.00776L6.59296 2.86391C6.30857 5.26068 4.41888 7.15036 2.02209 7.43475L0.165943 7.65499C-0.0553144 7.68127 -0.0553144 8.00233 0.165943 8.0286L2.02209 8.24884C4.41888 8.53323 6.30857 10.4229 6.59296 12.8197L6.81319 14.6759Z" fill="currentColor" />
					</svg>
					<span>Our Testimonials</span>
					</div>
					<h2 class="title split-text split-in-right">What clients say <span>about Quantal AI.</span></h2>
				</div>
				<div class="swiper testimonial-slider">
					<div class="swiper-wrapper">
					<div class="swiper-slide">
						<div class="testimonial-block">
						<p class="text">"It was a pleasure working with this team. Communication was smooth, deadlines were respected, and the overall collaboration was professional and efficient. Recommended!"</p>
						<div class="infu">
							<div class="image">
							<img src="<?= asset('images/quantal/clients/elunic.jpg') ?>" alt="Ivana Machin">
							</div>
							<div class="name-info">
							<h5 class="name">Ivana Machin</h5>
							<span>Elunic AG</span>
							</div>
						</div>
						</div>
					</div>
					<!-- <div class="swiper-slide">
						<div class="testimonial-block">
						<p class="text">"They built a fully functional AI voice agent using Vapi - voice logic, integrations, testing, production rollout - with impressive technical skill and attention to detail."</p>
						<div class="infu">
							<div class="image">
							<img src="<?= asset('images/quantal/clients/armstrong.png') ?>" alt="Thai Nguyen">
							</div>
							<div class="name-info">
							<h5 class="name">Thai Nguyen</h5>
							<span>Desert Recovery Centres</span>
							</div>
						</div>
						</div>
					</div> -->
					<div class="swiper-slide">
						<div class="testimonial-block">
						<p class="text">"Quantal AI and Team are EXPERTS at building ANY AI functionality you're seeking! Each project completed ON TIME and UNDER BUDGET. Highly Recommended!"</p>
						<div class="infu">
							<div class="image">
							<img src="<?= asset('images/quantal/clients/myhomecarebiz.jpg') ?>" alt="Melissa Cott">
							</div>
							<div class="name-info">
							<h5 class="name">Melissa Cott</h5>
							<span>myhomecarebiz.com</span>
							</div>
						</div>
						</div>
					</div>
					<div class="swiper-slide">
						<div class="testimonial-block">
						<p class="text">"Their technical aptitude is outstanding - highly competent, creative, thoughtful, and reliable. They built a complex integration connecting PhotoRoom, Google Cloud, AWS, and Shopify, and it works beautifully."</p>
						<div class="infu">
							<div class="image">
							<img src="<?= asset('images/quantal/clients/Osteopathic.webp') ?>" alt="David Friedland">
							</div>
							<div class="name-info">
							<h5 class="name">David Friedland</h5>
							<span>Osteopathic Healing Hands</span>
							</div>
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
	</div>
</div>

