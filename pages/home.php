<?php

/**
 * Source: index.html (theme original)
 * Stage 1: structural conversion only - content port happens in Stage 3.
 */
$page_title = $page_title ?? 'Quantal AI - Enterprise AI Solutions & Intelligent Automation';
$active_page = $active_page ?? 'home';
?>

<section class="hero-section fix hero-1 bg-cover" style="background-image: url('<?= asset('images/home-1/hero/hero-bg.jpg') ?>');">
    <div class="line-shape-animation cus-z-1 first w-100 h-100">
        <span></span>
        <span></span>
        <span></span>
        <span></span>
    </div>
    <div class="object-shape tm-gsap-animate-circle">
        <img src="<?= asset('images/home-1/hero/object-shape.png') ?>" alt="img">
    </div>
    <div class="ellipse-bg">
        <img src="<?= asset('images/home-1/hero/ellipse-bg.webp') ?>" alt="">
    </div>
    <div class="right-shape">
        <img src="<?= asset('images/home-1/hero/right-shape.png') ?>" alt="img">
    </div>
    <div class="container">
        <div class="row g-xl-0 g-4 align-items-center">
        <div class="col-xl-9">
            <div class="hero-content">
            <h1 class="hero-title split-text split-in-right">
                <span class="vec-shape"><img src="<?= asset('images/home-1/hero/vec-sp.png') ?>" alt="img"></span>
                Gen AI
            </h1>
            <h2 class="hero-title-2">
                <span class="text-1 split-text split-in-right">Engineering</span>
                Services
            </h2>
            <div class="content">
                <p class="wow fadeInUp" data-wow-delay=".3s">Production-ready AI systems built by experienced engineers. We help businesses turn AI into real, measurable outcomes &mdash; from voice bots and chat flows to vision and process automation.</p>
                <a class="theme-btn-main wow fadeInUp" data-wow-delay=".5s" href="<?= url('/contact') ?>">
                <span class="theme-btn-arrow-left"> <i class="far fa-long-arrow-right "></i> </span>
                <span class="theme-btn ">Talk to an AI Expert</span>
                <span class="theme-btn-arrow-right"> <i class="far fa-long-arrow-right"></i> </span>
                </a>
            </div>
            </div>
        </div>
        <div class="col-xl-3">
            <div class="hero-image">
            <div class="image fix">
                <img data-speed=".7" src="<?= asset('images/quantal/services/hero_banner.png') ?>" alt="img">
            </div>
            <div class="shape-1 float-bob-x">
                <img src="<?= asset('images/home-1/hero/shape1.png') ?>" alt="img">
            </div>
            <div class="hero-info float-bob-y">
                <div class="ratting-top">
                <span>5.0 </span>
                <div class="star">
                    <i class="fa-solid fa-star" style="color: rgb(255, 212, 59);"></i>
                    <i class="fa-solid fa-star" style="color: rgb(255, 212, 59);"></i>
                    <i class="fa-solid fa-star" style="color: rgb(255, 212, 59);"></i>
                    <i class="fa-solid fa-star" style="color: rgb(255, 212, 59);"></i>
                    <i class="fa-solid fa-star" style="color: rgb(255, 212, 59);"></i>
                    <p>Top rated on Upwork</p>
                </div>
                </div>
                <div class="client-image">
                  <div class="bg-shape">
                      <img src="<?= asset('images/home-1/hero/info-mask.png') ?>" alt="img">
                  </div>
                  <!-- <img src="<?= asset('images/quantal/clients/icici.png') ?>" alt="ICICI" class="icon-1">
                  <img src="<?= asset('images/quantal/clients/waterfield.png') ?>" alt="Waterfield" class="icon-2">
                  <img src="<?= asset('images/quantal/clients/grip.png') ?>" alt="Grip" class="icon-3">
                      <img src="<?= asset('images/quantal/clients/armstrong.png') ?>" alt="Armstrong" class="icon-4">
                  <span>+</span> -->
                </div>
            </div>
            </div>
        </div>
        </div>
    </div>
</section> <div class="marquee-section">
    <div class="marquee-two">
      <div class="marquee-group">
        <div class="text"><img src="<?= asset('images/home-1/soft-star.svg') ?>" alt="img">Voice Bot AI</div>
        <div class="text"><img src="<?= asset('images/home-1/soft-star.svg') ?>" alt="img">Chat flow AI</div>
        <div class="text"><img src="<?= asset('images/home-1/soft-star.svg') ?>" alt="img">VisionEdge AI</div>
        <div class="text"><img src="<?= asset('images/home-1/soft-star.svg') ?>" alt="img">Agent Ops AI</div>
        <div class="text"><img src="<?= asset('images/home-1/soft-star.svg') ?>" alt="img">Content AI</div>
      </div>
      <div class="marquee-group">
        <div class="text"><img src="<?= asset('images/home-1/soft-star.svg') ?>" alt="img">Voice Bot AI</div>
        <div class="text"><img src="<?= asset('images/home-1/soft-star.svg') ?>" alt="img">Chat flow AI</div>
        <div class="text"><img src="<?= asset('images/home-1/soft-star.svg') ?>" alt="img">VisionEdge AI</div>
        <div class="text"><img src="<?= asset('images/home-1/soft-star.svg') ?>" alt="img">Agent Ops AI</div>
        <div class="text"><img src="<?= asset('images/home-1/soft-star.svg') ?>" alt="img">Content AI</div>
      </div>
      <div class="marquee-group">
        <div class="text"><img src="<?= asset('images/home-1/soft-star.svg') ?>" alt="img">Voice Bot AI</div>
        <div class="text"><img src="<?= asset('images/home-1/soft-star.svg') ?>" alt="img">Chat flow AI</div>
        <div class="text"><img src="<?= asset('images/home-1/soft-star.svg') ?>" alt="img">VisionEdge AI</div>
        <div class="text"><img src="<?= asset('images/home-1/soft-star.svg') ?>" alt="img">Agent Ops AI</div>
        <div class="text"><img src="<?= asset('images/home-1/soft-star.svg') ?>" alt="img">Content AI</div>
      </div>
      <div class="marquee-group">
        <div class="text"><img src="<?= asset('images/home-1/soft-star.svg') ?>" alt="img">Voice Bot AI</div>
        <div class="text"><img src="<?= asset('images/home-1/soft-star.svg') ?>" alt="img">Chat flow AI</div>
        <div class="text"><img src="<?= asset('images/home-1/soft-star.svg') ?>" alt="img">VisionEdge AI</div>
        <div class="text"><img src="<?= asset('images/home-1/soft-star.svg') ?>" alt="img">Agent Ops AI</div>
        <div class="text"><img src="<?= asset('images/home-1/soft-star.svg') ?>" alt="img">Content AI</div>
      </div>
    </div>
</div>
<section class="about-section section-padding fix">
    <div class="about-vector tm-gsap-animate-circle d-none d-xxl-block"><img src="<?= asset('images/home-1/about/about-vector.png') ?>" alt="img"></div>
    <div class="about-shape d-none d-xxl-block"><img src="<?= asset('images/home-1/about/about-shape.png') ?>" alt="img"></div>
    <div class="light-bg d-none d-xxl-block"><img src="<?= asset('images/home-1/about/light-bg.png') ?>" alt="img"></div>
    <h2 class="stoke-title">Quantal AI</h2>
    <div class="container">
      <div class="row g-4">
        <div class="col-xl-6 col-lg-8">
          <div class="about-image-items1">
            <div class="about-thumb fix img-reveal">
              <img src="<?= asset('images/quantal/engineering-production-1.png') ?>" alt="img">
            </div>
            <div class="about-image-2 fix">
              <img src="<?= asset('images/quantal/engineering-production-2.png') ?>" alt="img">
            </div>
            <div class="about-circle-box">
                <div class="circle-text">
                    <svg viewBox="0 0 250 250">
                    <defs>
                        <path id="circlePath1" d="M125,125 m-95,0 a95,95 0 1,1 190,0 a95,95 0 1,1 -190,0" />
                    </defs>
                    <text>
                        <textPath xlink:href=" #circlePath" startOffset="0%">AI Projects Delivered ~ AI Projects Delivered ~</textPath>
                    </text>
                    </svg>
                </div>
                <div class="experience count-box">
                    <h3 class="num"><span class="count-text" data-speed="3000" data-stop="40" data-lag="0">40+</span></h3>
                </div>
            </div>
          </div>
        </div>
        <div class="col-xl-6 col-lg-10">
            <div class="about-content1">
                <div class="section-title mb-0">
                <div class="sub-title text-left">
                    <svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M6.81319 14.6759C6.83947 14.8971 7.16053 14.8971 7.18681 14.6759L7.40705 12.8197C7.69143 10.4229 9.58112 8.53323 11.9779 8.24884L13.834 8.0286C14.0553 8.00233 14.0553 7.68127 13.834 7.65499L11.9779 7.43475C9.58112 7.15036 7.69143 5.26068 7.40705 2.86391L7.18681 1.00776C7.16053 0.786476 6.83947 0.786476 6.81319 1.00776L6.59296 2.86391C6.30857 5.26068 4.41888 7.15036 2.02209 7.43475L0.165943 7.65499C-0.0553144 7.68127 -0.0553144 8.00233 0.165943 8.0286L2.02209 8.24884C4.41888 8.53323 6.30857 10.4229 6.59296 12.8197L6.81319 14.6759Z" fill="currentColor" />
                    </svg>
                    <span>Who We Are</span>
                </div>
                <h2 class="title split-text split-in-right">Engineering Production-Ready <span>AI Systems</span></h2>
                </div>
                <p class="about-text wow fadeInUp" data-wow-delay=".3s">Quantal AI is a GenAI engineering studio. We help companies move beyond experimentation by designing and deploying intelligent systems &mdash; from AI agents and automation platforms to knowledge systems and generative AI applications.</p>
                <div class="about-box wow fadeInUp" data-wow-delay=".5s">
                <h6 class="para-text">We don&rsquo;t just prototype AI &mdash; we build systems that run in production. With 15+ years of combined expertise and 100% job success on Upwork, we deliver AI that fits your operations and scales with your business.</h6>
                </div>
                <a class="theme-btn-main wow fadeInUp" data-wow-delay=".9s" href="<?= url('/about') ?>">
                <span class="theme-btn-arrow-left"> <i class="far fa-long-arrow-right "></i> </span>
                <span class="theme-btn ">About Us</span>
                <span class="theme-btn-arrow-right"> <i class="far fa-long-arrow-right"></i> </span>
                </a>
            </div>
        </div>
      </div>
      <div class="about-image-video zoom-effect-style">
        <div class="about-video bg-cover tm-gsap-img-parallax mt-100" style="background-image: url('<?= asset('images/home-1/about-image.jpg') ?>');"></div>
        <div class="video-outer">
          <a href="<?= url('contact') ?>" class="play-now">
            <i class="icon fa-solid fa-arrow-right"></i>
            <span class="ripple"></span>
          </a>
          <h3 class="watch-title wow fadeInUp" data-wow-delay=".3s">Talk to an AI Expert</h3>
        </div>
      </div>
    </div>
</section>


<!-- Brand Section Start -->
<div class="brand-section-2 mb-5">
	<div class="container">
		<div class="brand-wrap-2">
			<div class="text-box">
				<p>Trusted by founders & business owners</p>
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


<section class="feature-section1 section-padding bb-top fix">
    <div class="scroll-text">
      <h2 class="stoke-title text2">Why Quantal</h2>
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
                <path d="M6.81319 14.6759C6.83947 14.8971 7.16053 14.8971 7.18681 14.6759L7.40705 12.8197C7.69143 10.4229 9.58112 8.53323 11.9779 8.24884L13.834 8.0286C14.0553 8.00233 14.0553 7.68127 13.834 7.65499L11.9779 7.43475C9.58112 7.15036 7.69143 5.26068 7.40705 2.86391L7.18681 1.00776C7.16053 0.786476 6.83947 0.786476 6.81319 1.00776L6.59296 2.86391C6.30857 5.26068 4.41888 7.15036 2.02209 7.43475L0.165943 7.65499C-0.0553144 7.68127 -0.0553144 8.00233 0.165943 8.0286L2.02209 8.24884C4.41888 8.53323 6.30857 10.4229 6.59296 12.8197L6.81319 14.6759Z" fill="currentColor" />
              </svg>
              <span>Why Quantal AI</span>
            </div>
            <h2 class="title split-text split-in-right">Built for Real <span>Production AI</span></h2>
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
              <h4 class="title">Senior AI Engineers</h4>
              <div class="list-item">
                <span class="text">15+ years experience</span>
                <span class="dot"></span>
                <span class="text">100% job success</span>
              </div>
            </li>
            <li>
              <h4 class="title">Fast Onboarding</h4>
              <div class="list-item">
                <span class="text">24-hour response</span>
                <span class="dot"></span>
                <span class="text">No long hiring cycles</span>
              </div>
            </li>
            <li>
              <h4 class="title">Production-First Systems</h4>
              <div class="list-item">
                <span class="text">Real LLM experience</span>
                <span class="dot"></span>
                <span class="text">Scalable deployments</span>
              </div>
            </li>
            <li>
              <h4 class="title">Transparent Delivery</h4>
              <div class="list-item">
                <span class="text">On time</span>
                <span class="dot"></span>
                <span class="text">Under budget</span>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
</section>
<section class="service-wrapper service-one section-padding section-bg">
    <div class="auto-container">
        <div class="section-title text-center">
        <div class="sub-title">
            <svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M6.81319 14.6759C6.83947 14.8971 7.16053 14.8971 7.18681 14.6759L7.40705 12.8197C7.69143 10.4229 9.58112 8.53323 11.9779 8.24884L13.834 8.0286C14.0553 8.00233 14.0553 7.68127 13.834 7.65499L11.9779 7.43475C9.58112 7.15036 7.69143 5.26068 7.40705 2.86391L7.18681 1.00776C7.16053 0.786476 6.83947 0.786476 6.81319 1.00776L6.59296 2.86391C6.30857 5.26068 4.41888 7.15036 2.02209 7.43475L0.165943 7.65499C-0.0553144 7.68127 -0.0553144 8.00233 0.165943 8.0286L2.02209 8.24884C4.41888 8.53323 6.30857 10.4229 6.59296 12.8197L6.81319 14.6759Z" fill="currentColor" />
            </svg>
            <span>AI Solutions</span>
        </div>
        <h2 class="title split-text split-in-right">
            AI Services Built for <span>Your</span> <br> <span>Real Operations</span>
        </h2>
        </div>
    </div>
    <div class="swiper service-slide">
        <div class="swiper-wrapper">
        <div class="swiper-slide">
            <div class="service-block">
            <div class="image">
                <img src="<?= asset('images/quantal/services/voice_ai.png') ?>" alt="Voice AI">
            </div>
            <div class="content">
                <span class="tag">Voice</span>
                <h4 class="title"><a href="<?= url('/services/voice') ?>">Voice AI Solutions</a></h4>
                <p class="text">Conversational, multilingual, sentiment-aware voice agents for customer service, lead nurturing, and reminders.</p>
                <a href="<?= url('/services/voice') ?>" class="theme-btn-main theme-btn-main2">
                <span class="theme-btn-arrow-left"> <i class="far fa-long-arrow-right "></i></span>
                <span class="theme-btn">Read More</span>
                <span class="theme-btn-arrow-right">
                    <i class="far fa-long-arrow-right"></i>
                </span>
                </a>
            </div>
            </div>
        </div>
        <div class="swiper-slide">
            <div class="service-block">
            <div class="image">
                <img src="<?= asset('images/quantal/services/text_ai.png') ?>" alt="Text AI">
            </div>
            <div class="content">
                <span class="tag">Text</span>
                <h4 class="title"><a href="<?= url('/services/text') ?>">Text AI Solutions</a></h4>
                <p class="text">
                Intelligent chatbots, NLP-powered market intelligence, AI email marketing, and personalized outreach.
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
        </div>
        <div class="swiper-slide">
            <div class="service-block">
            <div class="image">
                <img src="<?= asset('images/quantal/services/image_ai.png') ?>" alt="Image / Document AI">
            </div>
            <div class="content">
                <span class="tag">Vision</span>
                <h4 class="title"><a href="<?= url('/services/image') ?>">Image / Document AI</a></h4>
                <p class="text">
                KYC verification, fraud detection, signature verification, and invoice processing with computer vision.
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
        <div class="swiper-slide">
            <div class="service-block">
            <div class="image">
                <img src="<?= asset('images/quantal/services/process_ai.png') ?>" alt="Process Automation">
            </div>
            <div class="content">
                <span class="tag">Automation</span>
                <h4 class="title"><a href="<?= url('/services/process-auto') ?>">Process Automation</a></h4>
                <p class="text">
                Reporting, reconciliations, compliance, and workflow automation that streamlines operations.
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
        </div>
        </div>
        <div class="swiper-dot color-style-two border-style center">
        <div class="dot"></div>
        </div>
    </div>
</section><section class="skill-section1 section-padding section-bg bb-top">
    <div class="line-shape d-none d-xl-block">
      <img src="<?= asset('images/home-1/skills/line-shape.png') ?>" alt="img">
    </div>
    <div class="light-bg d-none d-xl-block">
      <img src="<?= asset('images/home-1/skills/light-bg.png') ?>" alt="img">
    </div>
    <div class="object-shape d-none d-xl-block tm-gsap-animate-circle">
      <img src="<?= asset('images/home-1/skills/object-shape.png') ?>" alt="img">
    </div>
    <div class="container">
      <div class="row g-4">
        <div class="col-lg-5">
          <div class="skill-left-items">
            <div class="row g-4">
              <div class="col-lg-4 col-md-4 col-sm-4 col-6 wow fadeInLeft" data-wow-delay=".3s">
                <div class="skill-box">
                  <div class="skill-thumb">
                    <img class="img-fluid" src="<?= asset('images/quantal/skills/openai.png') ?>" alt="OpenAI">
                  </div>
                </div>
              </div>
              <div class="col-lg-4 col-md-4 col-sm-4 col-6 wow fadeInLeft" data-wow-delay=".5s">
                <div class="skill-box">
                  <div class="skill-thumb">
                    <img class="img-fluid" src="<?= asset('images/quantal/skills/perplexity.png') ?>" alt="Perplexity">
                  </div>
                </div>
              </div>
              <div class="col-lg-4 col-md-4 col-sm-4 col-6 wow fadeInLeft" data-wow-delay=".7s">
                <div class="skill-box">
                  <div class="skill-thumb">
                    <img class="img-fluid" src="<?= asset('images/quantal/skills/anthropic.png') ?>" alt="Anthropic">
                  </div>
                </div>
              </div>
              <div class="col-lg-4 col-md-4 col-sm-4 col-6 wow fadeInRight" data-wow-delay=".3s">
                <div class="skill-box">
                  <div class="skill-thumb">
                    <img class="img-fluid" src="<?= asset('images/quantal/skills/lovable.png') ?>" alt="Lovable">
                  </div>
                </div>
              </div>
              <div class="col-lg-4 col-md-4 col-sm-4 col-6 wow fadeInRight" data-wow-delay=".5s">
                <div class="skill-box">
                  <div class="skill-thumb">
                    <img class="img-fluid" src="<?= asset('images/quantal/skills/midjourney.png') ?>" alt="MidJourney">
                  </div>
                </div>
              </div>
              <div class="col-lg-4 col-md-4 col-sm-4 col-6 wow fadeInRight" data-wow-delay=".7s">
                <div class="skill-box">
                  <div class="skill-thumb">
                    <img class="img-fluid" src="<?= asset('images/quantal/skills/gemini.png') ?>" alt="Gemini">
                  </div>
                </div>
              </div>
              <div class="col-lg-4 col-md-4 col-sm-4 col-6 wow fadeInLeft" data-wow-delay=".3s">
                <div class="skill-box">
                  <div class="skill-thumb">
                    <img class="img-fluid" src="<?= asset('images/quantal/skills/salesforce.png') ?>" alt="Salesforce">
                  </div>
                </div>
              </div>
              <div class="col-lg-4 col-md-4 col-sm-4 col-6 wow fadeInLeft" data-wow-delay=".5s">
                <div class="skill-box">
                  <div class="skill-thumb">
                    <img class="img-fluid" src="<?= asset('images/quantal/skills/jira.png') ?>" alt="Jira">
                  </div>
                </div>
              </div>
              <div class="col-lg-4 col-md-3 col-sm-4 col-6 wow fadeInLeft" data-wow-delay=".7s">
                <div class="skill-box">
                  <div class="skill-thumb">
                    <img class="img-fluid" src="<?= asset('images/quantal/skills/aws.png') ?>" alt="AWS">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-7">
          <div class="skill-content-1">
            <div class="section-title">
              <div class="sub-title">
                <svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M6.81319 14.6759C6.83947 14.8971 7.16053 14.8971 7.18681 14.6759L7.40705 12.8197C7.69143 10.4229 9.58112 8.53323 11.9779 8.24884L13.834 8.0286C14.0553 8.00233 14.0553 7.68127 13.834 7.65499L11.9779 7.43475C9.58112 7.15036 7.69143 5.26068 7.40705 2.86391L7.18681 1.00776C7.16053 0.786476 6.83947 0.786476 6.81319 1.00776L6.59296 2.86391C6.30857 5.26068 4.41888 7.15036 2.02209 7.43475L0.165943 7.65499C-0.0553144 7.68127 -0.0553144 8.00233 0.165943 8.0286L2.02209 8.24884C4.41888 8.53323 6.30857 10.4229 6.59296 12.8197L6.81319 14.6759Z" fill="currentColor" />
                </svg>
                <span>Our Stack</span>
              </div>
              <h2 class="title split-text split-in-right">
                Innovating
                <span class="d-block">Through AI Expertise</span>
              </h2>
            </div>
            <div class="skill-feature-items wow fadeInUp" data-wow-delay=".3s">
              <div class="skill-feature">
                <div class="progress">
                  <div class="progress-bar" style="width: 75%; animation: 2.6s ease 0s 1 normal none running animate-positive; opacity: 1;">
                    <h3 class="box-title">Voice AI &amp; Speech</h3>
                    <div class="progress-value"><span class="counter-number2">80</span>%</div>
                  </div>
                </div>
                <div class="progress style-2">
                  <div class="progress-bar" style="width: 83%; animation: 2.6s ease 0s 1 normal none running animate-positive; opacity: 1;">
                    <h3 class="box-title">Process Automation</h3>
                    <div class="progress-value"><span class="counter-number2">85</span>%</div>
                  </div>
                </div>
                <div class="progress style-3">
                  <div class="progress-bar" style="width: 90%; animation: 2.6s ease 0s 1 normal none running animate-positive; opacity: 1;">
                    <h3 class="box-title">Computer Vision &amp; OCR</h3>
                    <div class="progress-value"><span class="counter-number2">90</span>%</div>
                  </div>
                </div>
                <div class="progress style-4">
                  <div class="progress-bar" style="width: 95%; animation: 2.6s ease 0s 1 normal none running animate-positive; opacity: 1;">
                    <h3 class="box-title">LLMs &amp; Conversational AI</h3>
                    <div class="progress-value"><span class="counter-number2">95</span>%</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
</section>
<section class="case-wrapper case-one section-padding section-bg-2">
    <div class="shape">
      <img src="<?= asset('images/home-1/case/shape-01.webp') ?>" alt="Case Studies - Featured Projects" class="shape-1 tm-gsap-animate-circle">
      <div class="light-shape"> </div>
    </div>
    <div class="auto-container">
      <div class="row g-4">
        <div class="col-xxl-5 col-lg-6">
          <div class="left-content">
            <div class="section-title pb-3 pb-xl-5">
              <div class="sub-title">
                <svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M6.81319 14.6759C6.83947 14.8971 7.16053 14.8971 7.18681 14.6759L7.40705 12.8197C7.69143 10.4229 9.58112 8.53323 11.9779 8.24884L13.834 8.0286C14.0553 8.00233 14.0553 7.68127 13.834 7.65499L11.9779 7.43475C9.58112 7.15036 7.69143 5.26068 7.40705 2.86391L7.18681 1.00776C7.16053 0.786476 6.83947 0.786476 6.81319 1.00776L6.59296 2.86391C6.30857 5.26068 4.41888 7.15036 2.02209 7.43475L0.165943 7.65499C-0.0553144 7.68127 -0.0553144 8.00233 0.165943 8.0286L2.02209 8.24884C4.41888 8.53323 6.30857 10.4229 6.59296 12.8197L6.81319 14.6759Z" fill="currentColor" />
                </svg>
                <span>Featured Projects</span>
              </div>
              <h2 class="title split-text split-in-right">Success Stories That <span> Transform Businesses</span></h2>
            </div>
            <a class="theme-btn-main mb-5 mb-xl-0 wow fadeInUp" data-wow-delay=".3s" href="<?= url('/contact') ?>">
              <span class="theme-btn-arrow-left"> <i class="far fa-long-arrow-right "></i> </span>
              <span class="theme-btn ">View All Case Studies</span>
              <span class="theme-btn-arrow-right"> <i class="far fa-long-arrow-right"></i> </span>
            </a>
            <h2 class="title-shadow titlt-bottom-top d-none d-xl-block">case studies</h2>
          </div>
        </div>
        <div class="col-xxl-7">
          <div class="row design-choose-item-wrap">
            <div class="col-xl-6 col-lg-6 col-md-6">
              <div class="case-block design-choose-item-1">
                <div class="image not-hide-cursor" data-cursor="View<br>Case">
                  <a href="<?= url('/contact') ?>" class="cursor-hide tp--hover-img" data-displacement="/assets/images/quantal/case-studies/recruitment.png"
                    data-intensity="0.6" data-speedin="1" data-speedout="1">
                    <img src="<?= asset('images/quantal/case-studies/recruitment.png') ?>" alt="Recruitment automation">
                  </a>
                </div>
                <div class="content">
                  <div class="title-area">
                    <h4 class="title">
                     <a href="<?= url('/contact') ?>"> Recruitment Automation</a>
                    </h4>
                    <p class="text">CRM &amp; Data Enrichment</p>
                  </div>
                  <a href="<?= url('/contact') ?>" class="arrow-icon">
                    <i class="far fa-long-arrow-right"></i>
                  </a>
                </div>
              </div>
            </div>
            <div class="col-xl-6 col-lg-6 col-md-6">
              <div class="case-block  style-2 design-choose-item-2">
                <div class="image not-hide-cursor" data-cursor="View<br>Case">
                  <a href="<?= url('/contact') ?>" class="cursor-hide tp--hover-img" data-displacement="/assets/images/quantal/case-studies/ai-ml.png"
                    data-intensity="0.6" data-speedin="1" data-speedout="1">
                    <img src="<?= asset('images/quantal/case-studies/ai-ml.png') ?>" alt="GenAI Data Modeling">
                  </a>
                </div>
                <div class="content">
                  <div class="title-area">
                    <h4 class="title">
                     <a href="<?= url('/contact') ?>">GenAI Data Modeling</a>
                    </h4>
                    <p class="text">AI / ML Research</p>
                  </div>
                  <a href="<?= url('/contact') ?>" class="arrow-icon">
                    <i class="far fa-long-arrow-right"></i>
                  </a>
                </div>
              </div>
            </div>
            <div class="col-xl-6 col-lg-6 col-md-6">
              <div class="case-block  style-3 design-choose-item-1">
                <div class="image not-hide-cursor" data-cursor="View<br>Case">
                  <a href="<?= url('/contact') ?>" class="cursor-hide tp--hover-img" data-displacement="/assets/images/quantal/case-studies/legal.png"
                    data-intensity="0.6" data-speedin="1" data-speedout="1">
                    <img src="<?= asset('images/quantal/case-studies/legal.png') ?>" alt="Legal Recruiting">
                  </a>
                </div>
                <div class="content">
                  <div class="title-area">
                    <h4 class="title">
                     <a href="<?= url('/contact') ?>">Legal Recruiting</a>
                    </h4>
                    <p class="text">Multi-Channel Outreach</p>
                  </div>
                  <a href="<?= url('/contact') ?>" class="arrow-icon">
                    <i class="far fa-long-arrow-right"></i>
                  </a>
                </div>
              </div>
            </div>
            <div class="col-xl-6 col-lg-6 col-md-6">
              <div class="case-block style-2 style-3 design-choose-item-2">
                <div class="image not-hide-cursor" data-cursor="View<br>Case">
                  <a href="<?= url('/contact') ?>" class="cursor-hide tp--hover-img" data-displacement="/assets/images/quantal/case-studies/chatbot.png"
                    data-intensity="0.6" data-speedin="1" data-speedout="1">
                    <img src="<?= asset('images/quantal/case-studies/chatbot.png') ?>" alt="Conversational AI">
                  </a>
                </div>
                <div class="content">
                  <div class="title-area">
                    <h4 class="title">
                      <a href="<?= url('/contact') ?>">Conversational AI</a>
                    </h4>
                    <p class="text">Customer Engagement</p>
                  </div>
                  <a href="<?= url('/contact') ?>" class="arrow-icon">
                    <i class="far fa-long-arrow-right"></i>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
</section>
<section class="team-wrapper team-one  section-bg-2">
    <img src="<?= asset('images/home-1/team/shape-04.webp') ?>" alt="" class="shape-4 tm-gsap-animate-circle">
    <div class="inner section-bg section-padding">
        <div class="shape">
            <img src="<?= asset('images/home-1/team/shape-01.webp') ?>" alt="" class="shape-1 tm-gsap-animate-circle">
            <img src="<?= asset('images/home-1/team/shape-02.webp') ?>" alt="" class="shape-2">
            <img src="<?= asset('images/home-1/team/shape-03.webp') ?>" alt="" class="shape-3">
        </div>
        <div class="auto-container">
            <div class="section-title text-center">
                <div class="sub-title">
                    <svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M6.81319 14.6759C6.83947 14.8971 7.16053 14.8971 7.18681 14.6759L7.40705 12.8197C7.69143 10.4229 9.58112 8.53323 11.9779 8.24884L13.834 8.0286C14.0553 8.00233 14.0553 7.68127 13.834 7.65499L11.9779 7.43475C9.58112 7.15036 7.69143 5.26068 7.40705 2.86391L7.18681 1.00776C7.16053 0.786476 6.83947 0.786476 6.81319 1.00776L6.59296 2.86391C6.30857 5.26068 4.41888 7.15036 2.02209 7.43475L0.165943 7.65499C-0.0553144 7.68127 -0.0553144 8.00233 0.165943 8.0286L2.02209 8.24884C4.41888 8.53323 6.30857 10.4229 6.59296 12.8197L6.81319 14.6759Z" fill="currentColor" />
                    </svg>
                    <span>Leadership Team</span>
                </div>
                <h2 class="title split-text split-in-right">
                    Meet Our <br>
                    <span>Founders</span>
                </h2>
            </div>
            <div class="row team-item-wrapper">
                <div class="col-xl-2 col-0 wow fadeInUp" data-wow-delay=".3s"></div>
                <div class="col-xl-4 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay=".3s">
                    <div class="team-block team-item">
                    <div class="content">
                        <div class="title-area">
                        <h4 class="title"><a href="<?= url('/about') ?>">Shailesh Jain</a></h4>
                        <p class="text">Co-Founder &middot; CMU Alumni</p>
                        </div>
                        <a href="<?= url('/about') ?>" class="arrow-icon">
                        <i class="far fa-long-arrow-right"></i>
                        </a>
                    </div>
                    <div class="image-wrap">
                        <img class="image-shape" src="<?= asset('images/home-1/team/image-shape.webp') ?>" alt="">
                        <div class="image">
                        <img src="<?= asset('images/quantal/founders/shailesh-jain.png') ?>" alt="Shailesh Jain">
                        </div>
                    </div>
                    <div class="social-icon">
                        <a href="https://www.linkedin.com/in/shaileshkumarjain/" target="_blank" rel="noopener"><i class="fa-brands fa-linkedin-in"></i></a>
                    </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay=".5s">
                    <div class="team-block team-item">
                    <div class="content">
                        <div class="title-area">
                        <h4 class="title"><a href="<?= url('/about') ?>">Nirav Shah</a></h4>
                        <p class="text">Co-Founder &middot; Columbia, Ex UBS</p>
                        </div>
                        <a href="<?= url('/about') ?>" class="arrow-icon">
                        <i class="far fa-long-arrow-right"></i>
                        </a>
                    </div>
                    <div class="image-wrap">
                        <img class="image-shape" src="<?= asset('images/home-1/team/image-shape.png') ?>" alt="">
                        <div class="image">
                        <img src="<?= asset('images/quantal/founders/nirav-shah.png') ?>" alt="Nirav Shah">
                        </div>
                    </div>
                    <div class="social-icon">
                        <a href="https://www.linkedin.com/in/theniravshah/" target="_blank" rel="noopener"><i class="fa-brands fa-linkedin-in"></i></a>
                    </div>
                    </div>
                </div>
                <!-- <div class="col-xl-4 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay=".7s">
                    <div class="team-block team-item">
                    <div class="content">
                        <div class="title-area">
                        <h4 class="title"><a href="<?= url('/hire') ?>">Senior AI Engineers</a></h4>
                        <p class="text">Hire with Us</p>
                        </div>
                        <a href="<?= url('/hire') ?>" class="arrow-icon">
                        <i class="far fa-long-arrow-right"></i>
                        </a>
                    </div>
                    <div class="image-wrap">
                        <img class="image-shape" src="<?= asset('images/home-1/team/image-shape.png') ?>" alt="">
                        <div class="image">
                        <img src="<?= asset('images/home-1/team/team-03.png') ?>" alt="">
                        </div>
                    </div>
                    <div class="social-icon">
                        <a href="<?= url('/hire') ?>"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                    </div>
                    </div>
                </div> -->
            </div>
        </div>
    </div>
</section>

<section class="award-wrapper award-one bb-top fix section-padding pb-0">
    <div class="shape">
      <div class="light-shape"></div>
      <h2 class="title-shadow">
        impact
      </h2>
    </div>
    <div class="auto-container">
      <div class="row g-4">
        <div class="col-xl-7 col-lg-7">
          <div class="cards-content">
            <div class="award-block">
              <div class="head">
                <div class="num">40+</div>
                <div class="icon">
                  <img src="<?= asset('images/home-1/award/award-icon.webp') ?>" alt="Production AI Projects Delievered - Award Winner">
                </div>
              </div>
              <h4 class="title">
                Production AI <br> Projects Delivered
              </h4>
              <div class="bottom">
                <div class="line"></div>
                <p class="year">
                  Real-world impact
                </p>
              </div>
            </div>
            <div class="award-block">
              <div class="head">
                <div class="num">24h</div>
                <div class="icon">
                  <img src="<?= asset('images/home-1/award/award-icon.webp') ?>" alt="Production AI Projects Delievered - Award Winner">
                </div>
              </div>
              <h4 class="title">
                AI Engineers Ready<br>to Join Your Team
              </h4>
              <div class="bottom">
                <div class="line"></div>
                <p class="year">
                  Fast onboarding
                </p>
              </div>
            </div>
            <div class="award-block">
              <div class="head">
                <div class="num">3&times;</div>
                <div class="icon">
                  <img src="<?= asset('images/home-1/award/award-icon.webp') ?>" alt="">
                </div>
              </div>
              <h4 class="title">
                Faster Workflow <br>Automation with Agents
              </h4>
              <div class="bottom">
                <div class="line"></div>
                <p class="year">
                  Measurable gains
                </p>
              </div>
            </div>
          </div>
        </div>
        <div class="col-xl-5 col-lg-5">
          <div class="right-content">
            <div class="section-title">
              <div class="sub-title">
                <svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M6.81319 14.6759C6.83947 14.8971 7.16053 14.8971 7.18681 14.6759L7.40705 12.8197C7.69143 10.4229 9.58112 8.53323 11.9779 8.24884L13.834 8.0286C14.0553 8.00233 14.0553 7.68127 13.834 7.65499L11.9779 7.43475C9.58112 7.15036 7.69143 5.26068 7.40705 2.86391L7.18681 1.00776C7.16053 0.786476 6.83947 0.786476 6.81319 1.00776L6.59296 2.86391C6.30857 5.26068 4.41888 7.15036 2.02209 7.43475L0.165943 7.65499C-0.0553144 7.68127 -0.0553144 8.00233 0.165943 8.0286L2.02209 8.24884C4.41888 8.53323 6.30857 10.4229 6.59296 12.8197L6.81319 14.6759Z" fill="currentColor" />
                </svg>
                <span>Why Quantal AI</span>
              </div>
              <h2 class="title split-text split-in-right">
                AI Systems Built for <br>
                <span>Real Production</span>
              </h2>
            </div>
            <p class="text wow fadeInUp" data-wow-delay=".3s">
              Senior AI engineers, fast onboarding without long hiring cycles, production-first systems &mdash; with 100% transparent communication and delivery from planning to deployment.
            </p>
            <div class="experience count-box wow fadeInUp" data-wow-delay=".5s">
              <h3 class="num"><span class="count-text" data-speed="3000" data-stop="40">0</span>+</h3>
              <p class="text-1">Production AI Projects Delivered</p>
            </div>
            <div class="image right-to-left-ani">
              <img src="<?= asset('images/home-1/award/award-01.webp') ?>" alt="Production-ready AI systems built by Quantal AI">
            </div>
          </div>
        </div>
      </div>
    </div>
</section>

<div class="multiple-section section-bg-2 section-padding pb-0">
    <div class="inner section-bg section-padding">
        <div class="bg-image bg-cover" style="background-image: url(<?= asset('images/home-1/multiple-sec-bg.jpg') ?>);"> </div>
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
                <h2 class="title split-text split-in-right">Production AI Through <br> <span> Step-by-Step Engineering</span></h2>
            </div>
            <div class="cards-content">
                <div class="row">
                <div class="col-xl-3 col-lg-4 col-md-6 wow fadeInUp" data-wow-delay=".2s">

                    <div class="work-block">
                    <div class="step-btn">Step 01</div>
                    <div class="inner-box">
                        <div class="icon"><i class="flaticon-tech flaticon-tech-interaction-1"></i></div>
                        <h4 class="title">Discovery &amp; Strategy</h4>
                        <p class="text">We map your operations, identify the highest-leverage AI opportunities, and define success metrics with you.</p>
                    </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-4 col-md-6 wow fadeInUp" data-wow-delay=".4s">
                    <div class="work-block">
                    <div class="step-btn">Step 02</div>
                    <div class="inner-box">
                        <div class="icon"><i class="flaticon-tech flaticon-tech-interaction-1"></i></div>
                        <h4 class="title">Architecture &amp; Design</h4>
                        <p class="text">We design AI systems built for scale &mdash; LLM selection, data pipelines, integrations, and security from day one.</p>
                    </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-4 col-md-6 wow fadeInUp" data-wow-delay=".6s">
                    <div class="work-block">
                    <div class="step-btn">Step 03</div>
                    <div class="inner-box">
                        <div class="icon"><i class="flaticon-tech flaticon-tech-interaction-1"></i></div>
                        <h4 class="title">Build &amp; Deploy</h4>
                        <p class="text">Senior AI engineers ship production systems &mdash; voice agents, automation flows, vision pipelines, integrations.</p>
                    </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-4 col-md-6 wow fadeInUp" data-wow-delay=".8s">
                    <div class="work-block">
                    <div class="step-btn">Step 04</div>
                    <div class="inner-box">
                        <div class="icon"><i class="flaticon-tech flaticon-tech-interaction-1"></i></div>
                        <h4 class="title">Optimize &amp; Scale</h4>
                        <p class="text">Continuous monitoring, model tuning, and expansion as your business grows. AI that compounds.</p>
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
                <div class="col-xl-5 col-lg-5">
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
                <div class="col-xl-7 col-lg-7">
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
                            <div class="image">
                                <img src="<?= asset('images/quantal/clients/myhomecarebiz.jpg') ?>" alt="Melissa C">
                            </div>
                            <div class="name-info">
                                <h5 class="name">Melissa C</h5>
                                <span>myhomecarebiz.com</span>
                            </div>
                            </div>
                        </div>
                        </div>
                        <div class="swiper-slide">
                        <div class="testimonial-block">
                            <p class="text">&ldquo;Working with Quantal AI team has been an absolute pleasure. Their technical aptitude is outstanding &mdash; they&rsquo;re not only highly competent but also creative, thoughtful, and reliable. They built a complex integration for our wine business connecting PhotoRoom, Google Cloud, AWS, and Shopify, and it works beautifully.&rdquo;</p>
                            <div class="infu">
                            <div class="image">
                                <img src="<?= asset('images/quantal/clients/armstrong.png') ?>" alt="David F">
                            </div>
                            <div class="name-info">
                                <h5 class="name">David F</h5>
                                <span>Osteopathic Healing Hands</span>
                            </div>
                            </div>
                        </div>
                        </div>
                        <div class="swiper-slide">
                        <div class="testimonial-block">
                            <p class="text">&ldquo;It was a pleasure working with Quantal AI team. Communication was smooth, deadlines were respected, and the overall collaboration was professional and efficient. I would definitely consider working together again in the future. Recommended!&rdquo;</p>
                            <div class="infu">
                            <div class="image">
                                <img src="<?= asset('images/quantal/clients/elunic.jpg') ?>" alt="Ivana M">
                            </div>
                            <div class="name-info">
                                <h5 class="name">Ivana M</h5>
                                <span>Elunic AG</span>
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

<div class="brand-section mt-4 mb-4" hidden>
    <div class="swiper brand-slider">
      <div class="swiper-wrapper">
        <div class="swiper-slide">
          <div class="brand-image1">
            <img class="img-fluid" src="<?= asset('images/quantal/clients/icici.png') ?>" alt="ICICI">
          </div>
        </div>
        <div class="swiper-slide">
          <div class="brand-image1">
            <img class="img-fluid" src="<?= asset('images/quantal/clients/waterfield.png') ?>" alt="Waterfield">
          </div>
        </div>
        <div class="swiper-slide">
          <div class="brand-image1">
            <img class="img-fluid" src="<?= asset('images/quantal/clients/grip.png') ?>" alt="Grip">
          </div>
        </div>
        <div class="swiper-slide">
          <div class="brand-image1">
            <img class="img-fluid" src="<?= asset('images/quantal/clients/armstrong.png') ?>" alt="Armstrong">
          </div>
        </div>
        <div class="swiper-slide">
          <div class="brand-image1">
            <img class="img-fluid" src="<?= asset('images/quantal/clients/elunic.jpg') ?>" alt="Elunic">
          </div>
        </div>
        <div class="swiper-slide">
          <div class="brand-image1">
            <img class="img-fluid" src="<?= asset('images/quantal/clients/nortmaq.png') ?>" alt="Nortmaq">
          </div>
        </div>
        <div class="swiper-slide">
          <div class="brand-image1">
            <img class="img-fluid" src="<?= asset('images/quantal/clients/xanevo.jpg') ?>" alt="Xanevo">
          </div>
        </div>
        <div class="swiper-slide">
          <div class="brand-image1">
            <img class="img-fluid" src="<?= asset('images/quantal/clients/ackuity.jpg') ?>" alt="Ackuity">
          </div>
        </div>
      </div>
    </div>
</div>

<section class="news-wrapper news-one section-padding section-bg-2" hidden>
  <div class="shape">
    <img src="<?= asset('images/home-1/news/shape-01.webp') ?>" alt="" class="shape-1">
    <div class="light-shape"></div>
  </div>
  <div class="auto-container" hidden>
    <div class="section-title-area">
      <div class="section-title">
        <div class="sub-title">
          <svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M6.81319 14.6759C6.83947 14.8971 7.16053 14.8971 7.18681 14.6759L7.40705 12.8197C7.69143 10.4229 9.58112 8.53323 11.9779 8.24884L13.834 8.0286C14.0553 8.00233 14.0553 7.68127 13.834 7.65499L11.9779 7.43475C9.58112 7.15036 7.69143 5.26068 7.40705 2.86391L7.18681 1.00776C7.16053 0.786476 6.83947 0.786476 6.81319 1.00776L6.59296 2.86391C6.30857 5.26068 4.41888 7.15036 2.02209 7.43475L0.165943 7.65499C-0.0553144 7.68127 -0.0553144 8.00233 0.165943 8.0286L2.02209 8.24884C4.41888 8.53323 6.30857 10.4229 6.59296 12.8197L6.81319 14.6759Z" fill="currentColor" />
          </svg>
          <span>Our Latest Insights</span>
        </div>
        <h2 class="title split-text split-in-right">Resources &amp; <br> <span>AI Engineering Insights</span></h2>
      </div>
      <a class="theme-btn-main wow fadeInUp" data-wow-delay=".3s" href="<?= url('/blog') ?>">
        <span class="theme-btn-arrow-left"> <i class="far fa-long-arrow-right"></i> </span>
        <span class="theme-btn">View All Posts</span>
        <span class="theme-btn-arrow-right"> <i class="far fa-long-arrow-right"></i> </span>
      </a>
    </div>
    <div class="inner">
      <div class="row justify-content-between">
        <?php
        // Pull the latest 3 published posts. Falls back to placeholder cards if no posts.
        $latest_posts = [];
        if (db()) {
          try {
            $stmt = db()->prepare(
              "SELECT slug, title, excerpt, featured_image, featured_alt, published_at
                     FROM posts
                     WHERE status = 'published' AND (published_at IS NULL OR published_at <= NOW())
                     ORDER BY published_at DESC, id DESC
                     LIMIT 3"
            );
            $stmt->execute();
            $latest_posts = $stmt->fetchAll();
          } catch (PDOException $e) {
            // table may not exist yet
          }
        }

        // Placeholder content if we don't have 3 posts yet
        $placeholders = [
          ['slug' => 'voice-ai-engineering', 'title' => 'Building Production Voice AI: Lessons from 40+ Deployments', 'excerpt' => 'Architecture decisions, latency tuning, and integration patterns from real voice agent rollouts.', 'featured_image' => asset('images/quantal/blog/Blog2-1.jpeg'), 'featured_alt' => 'Voice AI', 'published_at' => null, 'category' => 'Voice AI'],
          ['slug' => 'kyc-document-ai', 'title' => 'KYC at Scale: How AI is Reshaping Document Verification', 'excerpt' => 'OCR, liveness detection, and fraud scoring &mdash; what works in production today.', 'featured_image' => asset('images/quantal/blog/Blog3-1.jpeg'), 'featured_alt' => 'KYC AI', 'published_at' => null, 'category' => 'Document AI'],
          ['slug' => 'agent-automation', 'title' => 'AI Agents That Run Real Workflows: A Practical Playbook', 'excerpt' => 'Designing autonomous agents for multi-step business processes &mdash; from prototype to production.', 'featured_image' => asset('images/quantal/blog/Blog4-1.jpeg'), 'featured_alt' => 'AI Agents', 'published_at' => null, 'category' => 'Automation'],
        ];

        // Combine real + placeholders, take first 3
        $cards = $latest_posts;
        $i = 0;
        while (count($cards) < 3 && $i < count($placeholders)) {
          $cards[] = $placeholders[$i++];
        }

        $delays = ['.3', '.5', '.7'];
        $styles = ['', 'style-2', 'style-3'];
        $fallbacks = [asset('images/home-1/news/news-01.jpg'), asset('images/home-1/news/news-02.jpg'), asset('images/home-1/news/news-03.jpg')];
        foreach (array_slice($cards, 0, 3) as $idx => $post):
          $img = $post['featured_image'] !== '' ? $post['featured_image'] : $fallbacks[$idx];
          $alt = $post['featured_alt'] !== '' ? $post['featured_alt'] : $post['title'];
          $href = '/blog/' . ($post['slug'] ?? '');
          $cat = $post['category'] ?? 'Insights';
          $date = !empty($post['published_at'])
            ? date('F j, Y', strtotime((string) $post['published_at']))
            : 'Coming soon';
          ?>
        <div class="col-xl-4 col-lg-6 col-md-6  ks_fade_anim" data-delay="<?= attr($delays[$idx]) ?>">
          <div class="news-block <?= attr($styles[$idx]) ?>">
            <div class="image">
              <img src="<?= attr($img) ?>" alt="<?= attr($alt) ?>">
              <img src="<?= attr($img) ?>" alt="<?= attr($alt) ?>">
            </div>
            <div class="content">
              <ul class="list">
                <li><?= e($cat) ?></li>
                <li>|</li>
                <li><?= e($date) ?></li>
              </ul>
              <h4 class="title"><a href="<?= attr($href) ?>"><?= e($post['title']) ?></a></h4>
              <a href="<?= attr($href) ?>" class="read"><i class="far fa-long-arrow-right"></i>Read More</a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section> <!-- Main Footer -->
