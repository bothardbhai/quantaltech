<?php

/**
 * Hire - content from quantaltech.ai/hire, rebuilt on theme/page-about.html
 * markup. Same sections, animation hooks, and shape decorations as the theme;
 * only copy and content imagery are swapped.
 */
$page_title = !empty($page_seo['title']) ? $page_seo['title'] : 'Hire Experienced AI Engineers';
$active_page = 'hire';

$pdo = db();

// --- Hire Master roles (dynamic, admin-managed — appended as cards below,
// mirroring how pages/services/index.php appends dynamic Service Master
// rows after its hard-coded cards) ---
$dynamic_hire_pages = [];
if ($pdo) {
	try {
		$dynamic_hire_pages = get_hire_pages($pdo, ['status' => 'published', 'display_on_hub' => true]);
	} catch (\PDOException $e) {
		// hire_pages table may not exist yet
	}
}

// --- Related services (real published services, same shape _service-dynamic.php builds) ---
$related_sub = 'Explore More';
$related_title_html = 'Related <span>Services</span>';
$related_text = 'Pair your new AI engineers with our end-to-end delivery services.';
$related_items = [];
if ($pdo) {
	try {
		$related_items = array_map(
			static fn(array $s) => [
				'label' => $s['name'],
				'title' => $s['title'],
				'slug' => $s['slug'],
				'excerpt' => $s['excerpt'],
				'featured_image' => $s['featured_image'],
			],
			array_slice(get_services($pdo, ['status' => 'published']), 0, 3)
		);
	} catch (\PDOException $e) {
		// services table may not exist yet
	}
}

// --- Knowledge hub / latest posts (same query + placeholder-fallback pattern as pages/home.php) ---
$blog_sub = 'Insights';
$blog_title_html = 'From Our <span>Knowledge Hub</span>';
$blog_text = 'Ideas and lessons from teams building production AI.';
$latest_posts = [];
if ($pdo) {
	try {
		$stmt = $pdo->prepare(
			"SELECT slug, title, excerpt, featured_image
			 FROM posts
			 WHERE status = 'published' AND (published_at IS NULL OR published_at <= NOW())
			 ORDER BY published_at DESC, id DESC
			 LIMIT 3"
		);
		$stmt->execute();
		$latest_posts = $stmt->fetchAll();
	} catch (\PDOException $e) {
		// posts table may not exist yet
	}
}
$blog_placeholders = [
	['title' => 'How to Structure an AI Engineering Engagement', 'excerpt' => 'Dedicated hire vs team extension vs project delivery, and how to pick the right model for your roadmap.', 'featured_image' => asset('images/quantal/blog/Blog2-1.jpeg')],
	['title' => 'What to Vet For When Hiring an LLM Engineer', 'excerpt' => 'The interview signals that actually predict production-grade Generative AI work.', 'featured_image' => asset('images/quantal/blog/Blog3-1.jpeg')],
	['title' => 'MLOps From Day One: Why It Matters for Hired Teams', 'excerpt' => "Deployment pipelines, monitoring and governance shouldn't be an afterthought.", 'featured_image' => asset('images/quantal/blog/Blog4-1.jpeg')],
];
$blog_cards = $latest_posts;
$bi = 0;
while (count($blog_cards) < 3 && $bi < count($blog_placeholders)) {
	$blog_cards[] = $blog_placeholders[$bi++];
}
$blog_posts = array_map(static function (array $p) {
	return [
		'image' => !empty($p['slug']) ? media_url($p['featured_image']) : $p['featured_image'],
		'category' => '',
		'title' => $p['title'],
		'desc' => $p['excerpt'],
		'link' => !empty($p['slug']) ? url('/blog/' . $p['slug']) : url('/blog'),
	];
}, $blog_cards);

// --- Case studies ---
$cs_sub = 'Proven Results';
$cs_title_html = 'AI Teams That <span>Delivered</span>';
$cs_text = 'A sample of what our hired AI engineers have shipped for clients.';
$case_studies = [
	['title' => 'Automated KYC Document Verification', 'desc' => 'A dedicated AI engineering team cut manual document review time by 70% for a fintech client.'],
	['title' => 'Voice AI Agent for Customer Support', 'desc' => 'An embedded LLM engineer shipped a production voice agent handling 40% of first-line support tickets.'],
	['title' => 'Recommendation Engine for E-commerce', 'desc' => 'A hired ML engineering team built a real-time recommendation system that lifted conversion by 18%.'],
];

// --- Mid CTA ---
$cta_tag = 'Ready to Scale?';
$cta_title_html = 'Hire Your Next <span>AI Engineer</span> This Week';
$cta_text = 'Share your requirements and we will match you with a vetted AI engineer or team within 24 hours.';

// --- FAQ ---
$faq_intro = 'Answers to the questions we hear most often from teams hiring AI engineers.';
$faqs = [
	['question' => 'How quickly can I onboard an AI engineer?', 'answer' => 'Most clients are matched with a vetted AI engineer and begin onboarding within 24-48 hours of sharing their requirements.'],
	['question' => 'What engagement models do you offer?', 'answer' => 'Dedicated hire, team extension, and end-to-end project delivery, all with flexible monthly or hourly terms.'],
	['question' => 'Do your engineers work in my time zone?', 'answer' => 'Yes, our teams maintain overlapping hours with US EST/PST as well as European and Indian business hours.'],
	['question' => 'Who owns the code and IP?', 'answer' => 'You retain full ownership of all code, models, and intellectual property produced during the engagement.'],
	['question' => "What happens if an engineer isn't a fit?", 'answer' => 'We offer a replacement at no additional cost during the initial trial period.'],
];

// --- Final CTA ---
$final_cta_title_html = "Let's Build Your <span>AI Team</span>";
$final_cta_desc = 'Tell us what you need and we will connect you with the right AI engineers within 24 hours.';
$final_cta_btn_text = 'View Case Studies';
$final_cta_btn_url = '/case-studies';
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

<div class="services-details__content">

	<!-- Hero Section -->
	<section class="hire-ai-hero">
		<div class="container">

			<div class="hero-left">

				<span class="hero-badge">
					<i class="bi bi-stars"></i>
					Trusted by Global Businesses
				</span>

				<h1>
					Hire Experienced
					<span>AI Engineers</span>
				</h1>

				<p>
					Build production-ready AI solutions with experienced engineers specializing in
					Generative AI, LLMs, AI Agents, Computer Vision, NLP, and MLOps.
					Scale your engineering team quickly with flexible engagement models.
				</p>

				<div class="hero-features">

					<div class="feature-item">
						<div class="icon">
							<i class="bi bi-check-lg"></i>
						</div>
						<span>Senior AI Engineers</span>
					</div>

					<div class="feature-item">
						<div class="icon">
							<i class="bi bi-check-lg"></i>
						</div>
						<span>Fast Team Onboarding</span>
					</div>

					<div class="feature-item">
						<div class="icon">
							<i class="bi bi-check-lg"></i>
						</div>
						<span>LLM & AI Agent Specialists</span>
					</div>

					<div class="feature-item">
						<div class="icon">
							<i class="bi bi-check-lg"></i>
						</div>
						<span>Production Ready Development</span>
					</div>

					<div class="feature-item">
						<div class="icon">
							<i class="bi bi-check-lg"></i>
						</div>
						<span>Flexible Hiring Models</span>
					</div>

					<div class="feature-item">
						<div class="icon">
							<i class="bi bi-check-lg"></i>
						</div>
						<span>Transparent Communication</span>
					</div>

				</div>

			</div>


			<div class="hero-right">

				<div class="contact-card">

					<h3>Let's Build Your AI Team</h3>

					<div id="hire-form-msg" class="contact-msg" style="display:none;"></div>
					<form id="hire_form" class="hire-form" action="<?= url('/hire-submit') ?>" method="post">
						<?= csrf_field() ?>
						<input type="hidden" name="form_botcheck" value="">
						<input type="hidden" name="page_url" value="<?= attr(current_url()) ?>">

						<div class="grid-2">

							<div class="form-group">
								<label>Name</label>
								<input type="text" name="name" placeholder="John Doe" required>
							</div>

							<div class="form-group">
								<label>Email</label>
								<input type="email" name="email" placeholder="john@company.com" required>
							</div>

						</div>

						<div class="grid-2">

							<div class="form-group">
								<label>Phone</label>
								<input type="text" name="phone" placeholder="+91 9876543210">
							</div>

							<div class="form-group">
								<label>Company</label>
								<input type="text" name="company" placeholder="Company Name">
							</div>

						</div>

						<div class="grid-2">

							<div class="form-group">
								<label>Country</label>
								<select class="custom-select" name="country">
									<option value="">Select Country</option>
									<option>India</option>
									<option>United States</option>
									<option>Canada</option>
								</select>
							</div>

							<div class="form-group">
								<label>Hiring Model</label>
								<select class="custom-select" name="hiring_model">
									<option value="">Select</option>
									<option>Dedicated</option>
									<option>Hourly</option>
									<option>Project Based</option>
								</select>
							</div>

						</div>

						<div class="form-group">
							<label>Project Requirements</label>

							<textarea rows="3" name="project_details" placeholder="Tell us about your AI project..." required></textarea>
						</div>

						<button type="submit" id="hire-form-submit-btn" class="submit-btn">
							<span class="btn-title">Schedule Free Consultation</span>
						</button>

					</form>
					<script>
						(function () {
							var form = document.getElementById('hire_form');
							var msgEl = document.getElementById('hire-form-msg');
							var btn = document.getElementById('hire-form-submit-btn');
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

	<!-- Count Section -->
	<section class="impact-section pt-50 pb-50">
		<div class="container">

			<div class="row g-4">

				<div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay=".2s">
					<div class="impact-card">
						<div class="impact-number">40+</div>
						<h5>Production AI</h5>
					</div>
				</div>

				<div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay=".3s">
					<div class="impact-card">
						<div class="impact-number">24 Hours</div>
						<h5>AI Engineers</h5>
					</div>
				</div>

				<div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay=".4s">
					<div class="impact-card">
						<div class="impact-number">3× Faster</div>
						<h5>Automation</h5>
					</div>
				</div>

				<div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay=".5s">
					<div class="impact-card">
						<div class="impact-number">Global</div>
						<h5>Clients</h5>
					</div>
				</div>

			</div>

		</div>
	</section>

	<!-- Client Logo Section -->
	<div class="brand-section-2 pb-100">
		<div class="container">
			<div class="brand-wrap-2">
				<div class="text-box">
					<p>Our Trusted Clients</p>
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

			</div>
		</div>
	</div>

	<!-- Meet Our AI Engineers -->

	<!-- Hire By Role (dynamic Hire Master entries) -->
	<?php if (!empty($dynamic_hire_pages)): ?>
		<section class="service-wrapper service-two section-padding section-bg-3">
			<div class="shape">
				<div class="light-shape"></div>
			</div>
			<div class="auto-container">
				<div class="sec-title text-center mb-70">
					<span class="sub-title">Hire By Role</span>
					<h2>
						Browse AI Engineers <span>by Specialization</span>
					</h2>
				</div>
				<div class="row">
					<?php foreach ($dynamic_hire_pages as $i => $hp): ?>
						<div class="col-xl-4 col-md-6 wow fadeInUp" data-wow-delay=".<?= ($i % 5) + 1 ?>s">
							<div class="service-block-two">
								<div class="head">
									<div class="icon"><i class="<?= attr($hp['icon_class'] ?: 'fas fa-brain') ?>"></i></div>
								</div>
								<div class="content">
									<h3 class="title"><a href="<?= url('/hire/' . attr($hp['slug'])) ?>"><?= e($hp['name']) ?></a></h3>
									<p class="text"><?= e(truncate_text($hp['excerpt'], 85)) ?></p>
									<a href="<?= url('/hire/' . attr($hp['slug'])) ?>" class="theme-btn-main theme-btn-main2">
										<span class="theme-btn-arrow-left"> <i class="far fa-long-arrow-right "></i></span>
										<span class="theme-btn">Read More</span>
										<span class="theme-btn-arrow-right"><i class="far fa-long-arrow-right"></i></span>
									</a>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<!-- Expertise of Our Engineers -->
	<section class="our-framework-section pb-100 section-bg section-bg-3">

		<div class="line-shape d-none d-xl-block" aria-hidden="true">
			<img src="<?= asset('images/home-1/skills/line-shape.png') ?>" alt="">
		</div>
		<div class="light-bg d-none d-xl-block" aria-hidden="true">
			<img src="<?= asset('images/home-1/skills/light-bg.png') ?>" alt="">
		</div>
		<div class="object-shape tm-gsap-animate-circle d-none d-xl-block" aria-hidden="true">
			<img src="<?= asset('images/home-1/skills/object-shape.png') ?>" alt="">
		</div>

		<div class="container">

			<div class="sec-title text-center mb-70">

				<span class="sub-title">
					Expertise of Our AI Engineers
				</span>

				<h2>
					Core Capabilities of
					<span>Our AI Engineers</span>
				</h2>

				<div class="text">
					Build smarter AI products with skilled AI engineers experienced in modern AI technologies and
					production-grade development.
				</div>

			</div>

			<div class="row g-4">

				<div class="col-md-4">

					<div class="framework-card">

						<div class="framework-icon">
							<i class="fas fa-brain"></i>
						</div>

						<h3>AI Readiness Assessment</h3>

						<p>
							We evaluate your business objectives, existing
							infrastructure, available data, and AI maturity to
							identify the highest-impact opportunities.
						</p>

					</div>

				</div>

				<div class="col-md-4">

					<div class="framework-card">

						<div class="framework-icon">
							<i class="fas fa-brain"></i>
						</div>

						<h3>Use Case Definition</h3>

						<p>
							We identify practical AI use cases aligned with your
							business goals, prioritizing projects that deliver
							measurable value quickly.
						</p>

					</div>

				</div>

				<div class="col-md-4">

					<div class="framework-card">

						<div class="framework-icon">
							<i class="fas fa-brain"></i>
						</div>

						<h3>Model Selection & Training</h3>

						<p>
							We choose the right AI models, train them with your
							business data, and optimize performance for production
							environments.
						</p>

					</div>

				</div>

				<div class="col-md-4">

					<div class="framework-card">

						<div class="framework-icon">
							<i class="fas fa-brain"></i>
						</div>

						<h3>Testing & Validation</h3>

						<p>
							Every solution undergoes rigorous testing to ensure
							accuracy, reliability, security, and compliance before
							deployment.
						</p>

					</div>

				</div>

				<div class="col-md-4">

					<div class="framework-card">

						<div class="framework-icon">
							<i class="fas fa-brain"></i>
						</div>

						<h3>Integration & Deployment</h3>

						<p>
							We integrate AI seamlessly into your existing systems,
							workflows, APIs, and cloud infrastructure with minimal
							disruption.
						</p>

					</div>

				</div>

				<div class="col-md-4">

					<div class="framework-card">

						<div class="framework-icon">
							<i class="fas fa-brain"></i>
						</div>

						<h3>Monitoring & Iteration</h3>

						<p>
							Continuous monitoring, performance optimization, model
							retraining, and ongoing improvements keep your AI
							delivering long-term value.
						</p>

					</div>

				</div>

			</div>

		</div>

	</section>

	<!-- What Our Engineers Build -->
	<section class="benefits-section pb-100 section-bg-3">
		<div class="line-shape" aria-hidden="true">
			<img src="<?= asset('images/home-1/features/line-shape.png') ?>" alt="">
		</div>
		<div class="ellipse-shape" aria-hidden="true">
			<img src="<?= asset('images/home-1/features/ellipse-bg.png') ?>" alt="">
		</div>
		<div class="container">

			<div class="sec-title text-center mb-70">
				<span class="sub-title">What Our AI Engineers Build</span>

				<h2>
					What You Can Build When You Hire
					<span>AI Developers from Quantal</span>
				</h2>
			</div>

			<div class="row g-4">

				<!-- Card 1 -->
				<div class="col-md-4 wow fadeInUp" data-wow-delay=".2s">
					<div class="benefit-card">
						<div class="benefit-number">01</div>

						<h3>AI Agents for Business Automation</h3>

						<p>
							Build autonomous AI agents that automate workflows and execute business tasks with minimal
							human intervention.
						</p>
					</div>
				</div>

				<!-- Card 2 -->
				<div class="col-md-4 wow fadeInUp" data-wow-delay=".3s">
					<div class="benefit-card">
						<div class="benefit-number">02</div>

						<h3>Generative AI & Enterprise LLM Applications</h3>

						<p>
							Develop enterprise-grade LLM solutions for intelligent assistants, content generation, and
							business automation.
						</p>
					</div>
				</div>

				<!-- Card 3 -->
				<div class="col-md-4 wow fadeInUp" data-wow-delay=".4s">
					<div class="benefit-card">
						<div class="benefit-number">03</div>

						<h3>Faster Time to Market</h3>

						<p>
							Accelerate AI implementation with proven frameworks,
							reusable components, and expert engineering.
						</p>
					</div>
				</div>

				<!-- Card 4 -->
				<div class="col-md-4 wow fadeInUp" data-wow-delay=".5s">
					<div class="benefit-card">
						<div class="benefit-number">04</div>

						<h3>Data-Driven Decisions</h3>

						<p>
							Turn raw business data into actionable insights using
							predictive analytics and intelligent automation.
						</p>
					</div>
				</div>
			</div>

		</div>
	</section>

	<!-- Engagement Models -->
	<section class="engagement-section pb-100 section-bg section-bg-3">

		<div class="container">

			<div class="sec-title text-center mb-70">

				<span class="sub-title">
					HOW TO WORK WITH US
				</span>

				<h2>
					The Right Engagement Model <br>
					<span>for Your Business</span>
				</h2>

			</div>

			<div class="row g-4">

				<!-- Model 1 -->

				<div class="col-md-6 wow fadeInUp" data-wow-delay=".2s">

					<div class="engagement-card">

						<div class="engagement-icon">
							<i class="fas fa-brain"></i>
						</div>

						<h3>Dedicated AI Engineer</h3>

						<p>
							Hire a dedicated AI engineer who works as an extension of your team, delivering long-term AI
							development, optimization, and ongoing technical support.
						</p>

					</div>

				</div>

				<!-- Model 2 -->

				<div class="col-md-6 wow fadeInUp" data-wow-delay=".4s">

					<div class="engagement-card featured">

						<div class="engagement-icon">
							<i class="fas fa-brain"></i>
						</div>

						<h3>AI Team Extension</h3>

						<p>
							Strengthen your existing team with experienced AI specialists who seamlessly integrate into
							your workflows and accelerate project delivery.
						</p>

					</div>

				</div>

				<!-- Model 3 -->

				<div class="col-md-6 wow fadeInUp" data-wow-delay=".6s">

					<div class="engagement-card">

						<div class="engagement-icon">
							<i class="fas fa-brain"></i>
						</div>

						<h3>End-to-End AI Project Delivery</h3>

						<p>
							From AI strategy and solution design to development, deployment, and optimization, we manage
							the complete AI project lifecycle.
						</p>

					</div>

				</div>

				<!-- Model 4 -->

				<div class="col-md-6 wow fadeInUp" data-wow-delay=".6s">

					<div class="engagement-card">

						<div class="engagement-icon">
							<i class="fas fa-brain"></i>
						</div>

						<h3>AI Advisory & Flexible Engagement</h3>

						<p>
							Access expert AI consulting, roadmap planning, architecture guidance, and flexible
							engineering support tailored to your business needs.
						</p>

					</div>

				</div>

			</div>

			<div class="row text-center mt-4">
				<div><a class="theme-btn-main wow fadeInUp justify-content-center" data-wow-delay=".9s"
						href="<?= url('/contact') ?>">
						<span class="theme-btn-arrow-left"> <i class="far fa-long-arrow-right "></i> </span>
						<span class="theme-btn ">Talk to Our AI Experts Today</span>
						<span class="theme-btn-arrow-right"> <i class="far fa-long-arrow-right"></i> </span>
					</a></div>
			</div>

		</div>

	</section>

	<!-- Why Hire -->
	<section class="why-quantal-section pb-100 section-bg-3">

		<div class="about-vector tm-gsap-animate-circle">
			<img src="<?= asset('images/home-1/about/about-vector.png') ?>" alt="">
		</div>
		<div class="decor-glow decor-glow--right decor-glow--bottom" aria-hidden="true"></div>

		<div class="container">

			<div class="sec-title text-center mb-70">

				<span class="sub-title">
					Why Hire AI Engineers From Quantal AI
				</span>

				<h2>
					Why Companies Hire Remote <br>
					<span>AI Engineers From Quantal AI</span>
				</h2>

			</div>

			<div class="row g-4">

				<!-- Card -->

				<div class="col-lg-4 col-md-6 wow fadeInUp">

					<div class="why-card">

						<div class="why-number">01</div>

						<h3>Production-Experienced AI Engineers</h3>

						<p>
							Our engineers have hands-on experience building and deploying AI applications across
							Generative AI, AI Agents, LLMs, RAG, automation, and enterprise AI.
						</p>

					</div>

				</div>

				<!-- Card -->

				<div class="col-lg-4 col-md-6 wow fadeInUp">

					<div class="why-card">

						<div class="why-number">02</div>

						<h3>Seamless Collaboration Across US Time Zones</h3>

						<p>
							We work closely with businesses across North America through overlapping EST and PST working
							hours. This enables faster communication, quicker decision-making, and efficient project
							execution without unnecessary delays.

						</p>

					</div>

				</div>

				<!-- Card -->

				<div class="col-lg-4 col-md-6 wow fadeInUp">

					<div class="why-card">

						<div class="why-number">03</div>

						<h3>Get Started in as Little as 24 Hours</h3>

						<p>
							AI projects shouldn't be held back by lengthy hiring processes. Share your project
							requirements, and we'll identify the right engineer or AI team to match your technical needs
							so development can begin without delay.

						</p>

					</div>

				</div>

				<!-- Card -->

				<div class="col-lg-4 col-md-6 wow fadeInUp">

					<div class="why-card">

						<div class="why-number">04</div>

						<h3>No ML Team Required</h3>

						<p>
							Our specialists become an extension of your business,
							delivering complete end-to-end AI implementation.
						</p>

					</div>

				</div>

				<!-- Card -->

				<div class="col-lg-4 col-md-6 wow fadeInUp">

					<div class="why-card">

						<div class="why-number">05</div>

						<h3>Production MLOps as Standard</h3>

						<p>
							Every AI solution includes deployment pipelines,
							monitoring, governance, and continuous optimization.
						</p>

					</div>

				</div>

				<!-- Card -->

				<div class="col-lg-4 col-md-6 wow fadeInUp">

					<div class="why-card">

						<div class="why-number">06</div>

						<h3>Business ROI, Not Model Accuracy</h3>

						<p>
							We measure success through measurable business outcomes,
							productivity gains, and long-term ROI.
						</p>

					</div>

				</div>

			</div>

		</div>

	</section>

	<!-- Form  -->
	<!-- Contact form (shared, site-wide) -->
	<section class="contact-details pb-50 dark-bg">
		<div class="container">
			<div class="row">
				<div class="col-lg-12">
					<div class="section-title mb-30">
						<div class="sub-title">
							<svg width="14" height="15" viewBox="0 0 14 15" fill="none"
								xmlns="http://www.w3.org/2000/svg">
								<path
									d="M6.81319 14.6759C6.83947 14.8971 7.16053 14.8971 7.18681 14.6759L7.40705 12.8197C7.69143 10.4229 9.58112 8.53323 11.9779 8.24884L13.834 8.0286C14.0553 8.00233 14.0553 7.68127 13.834 7.65499L11.9779 7.43475C9.58112 7.15036 7.69143 5.26068 7.40705 2.86391L7.18681 1.00776C7.16053 0.786476 6.83947 0.786476 6.81319 1.00776L6.59296 2.86391C6.30857 5.26068 4.41888 7.15036 2.02209 7.43475L0.165943 7.65499C-0.0553144 7.68127 -0.0553144 8.00233 0.165943 8.0286L2.02209 8.24884C4.41888 8.53323 6.30857 10.4229 6.59296 12.8197L6.81319 14.6759Z"
									fill="currentColor" />
							</svg>
							<span>Get in Touch</span>
						</div>
						<h2 class="title split-text split-in-right">Talk to an AI Expert</h2>
					</div>
					<div id="contact-msg" class="contact-msg" style="display:none;"></div>
					<form id="contact_form" name="contact_form" action="<?= url('/contact-submit') ?>" method="post">
						<?= csrf_field() ?>
						<input type="hidden" name="form_type" value="contact">
						<input type="hidden" name="page_url" value="<?= attr(current_url()) ?>">
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

	<!-- Industry Section  -->
	<section class="industry-solution-section pb-100 section-bg-3">

		<div class="decor-glow decor-glow--left decor-glow--top" aria-hidden="true"></div>

		<div class="container">

			<div class="sec-title text-center mb-70">

				<span class="sub-title">
					Industries We Help Transform with AI
				</span>

				<h2>
					Our AI Engineering Solutions <br>
					<span>for Every Industry</span>
				</h2>

			</div>

			<div class="row g-4">

				<?php
				$hire_industries = [
					['icon' => 'fas fa-shopping-cart', 'title' => 'Retail & E-commerce', 'desc' => 'Build AI-powered recommendation engines, demand forecasting, customer analytics and intelligent shopping experiences.'],
					['icon' => 'fas fa-landmark', 'title' => 'BFSI & Fintech', 'desc' => 'Hire engineers for fraud detection, KYC automation, credit risk models and secure AI-driven financial products.'],
					['icon' => 'fas fa-heartbeat', 'title' => 'Healthcare & Life Sciences', 'desc' => 'Build clinical decision support, medical imaging analysis, and patient engagement AI with compliance-first engineers.'],
					['icon' => 'fas fa-truck', 'title' => 'Logistics & Manufacturing', 'desc' => 'Optimize supply chains, predictive maintenance and quality inspection with production-grade AI engineering.'],
					['icon' => 'fas fa-cloud', 'title' => 'SaaS & Technology', 'desc' => 'Embed AI copilots, intelligent automation and LLM features into your product with engineers who ship fast.'],
				];
				?>

				<?php foreach ($hire_industries as $industry): ?>
					<div class="col-lg-4 col-md-6">

						<div class="industry-solution-card">
							<div class="industry-solution-icon">
								<i class="<?= e($industry['icon']) ?>"></i>
							</div>
							<h3><?= e($industry['title']) ?></h3>
							<p>
								<?= e($industry['desc']) ?>
							</p>
						</div>
					</div>
				<?php endforeach; ?>

			</div>

		</div>

	</section>

	<!-- Mid CTA -->
	<?php if (!empty($cta_tag) || !empty($cta_title_html) || !empty($cta_text)): ?>
		<section class="mid-cta-section pb-100 section-bg">
			<div class="decor-glow decor-glow--left decor-glow--top" aria-hidden="true"></div>
			<div class="decor-glow decor-glow--right decor-glow--bottom" aria-hidden="true"></div>
			<div class="container">
				<div class="mid-cta-box">
					<span class="service-tag">
						<?= e($cta_tag ?? '') ?>
					</span>
					<h2>
						<?= $cta_title_html ?? '' /* trusted HTML */ ?>
					</h2>
					<p>
						<?= e($cta_text ?? '') ?>
					</p>
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
		<section class="case-wrapper case-one section-padding section-bg-2">

			<div class="shape">
				<img src="<?= asset('images/home-1/case/shape-01.webp') ?>" alt="Case Studies"
					class="shape-1 tm-gsap-animate-circle">

				<div class="light-shape"></div>
			</div>

			<div class="auto-container">

				<div class="row g-4">

					<!-- Left Side -->
					<div class="col-xxl-5 col-lg-6">

						<div class="left-content">

							<div class="section-title pb-3 pb-xl-5">

								<div class="sub-title">
									<svg width="14" height="15" viewBox="0 0 14 15" fill="none">
										<path
											d="M6.81319 14.6759C6.83947 14.8971 7.16053 14.8971 7.18681 14.6759L7.40705 12.8197C7.69143 10.4229 9.58112 8.53323 11.9779 8.24884L13.834 8.0286C14.0553 8.00233 14.0553 7.68127 13.834 7.65499L11.9779 7.43475C9.58112 7.15036 7.69143 5.26068 7.40705 2.86391L7.18681 1.00776C7.16053 0.786476 6.83947 0.786476 6.81319 1.00776L6.59296 2.86391C6.30857 5.26068 4.41888 7.15036 2.02209 7.43475L0.165943 7.65499C-0.0553144 7.68127 -0.0553144 8.00233 0.165943 8.0286L2.02209 8.24884C4.41888 8.53323 6.30857 10.4229 6.59296 12.8197L6.81319 14.6759Z"
											fill="currentColor" />
									</svg>

									<span><?= e($cs_sub ?? '') ?></span>

								</div>

								<h2 class="title split-text split-in-right">
									<?= $cs_title_html ?? '' ?>
								</h2>

								<div class="text mt-3">
									<?= e($cs_text ?? '') ?>
								</div>

							</div>

							<a href="<?= url('/contact') ?>" class="theme-btn-main">

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

							<!-- <h2 class="title-shadow titlt-bottom-top d-none d-xl-block">
										Case Studies
									</h2> -->

						</div>

					</div>

					<!-- Right Side -->
					<div class="col-xxl-7">

						<div class="row design-choose-item-wrap">

							<?php foreach ($case_studies as $i => $cs): ?>

								<div class="col-xl-6 col-lg-6 col-md-6">

									<div class="case-block <?= ($i % 2) ? 'style-2' : '' ?>">

										<?php
										$cs_image = !empty($cs['image'])
											? media_url($cs['image'])
											: asset('images/quantal/case-studies/recruitment.png');
										$cs_alt = !empty($cs['title']) ? $cs['title'] : 'Case study';
										?>
										<div class="image not-hide-cursor" data-cursor="View<br>Case">
											<a href="<?= url('/contact') ?>" class="cursor-hide tp--hover-img"
												data-displacement="<?= attr($cs_image) ?>" data-intensity="0.6" data-speedin="1"
												data-speedout="1">
												<img src="<?= attr($cs_image) ?>" alt="<?= attr($cs_alt) ?>">
											</a>
										</div>

										<div class="content">

											<div class="title-area">

												<h4 class="title">
													<?= e(truncate_text($cs['title'], 30)) ?>
												</h4>

												<p class="text">
													<?= e(truncate_text($cs['desc'], 90)) ?>
												</p>

											</div>

											<a href="<?= url('/contact') ?>" class="arrow-icon">
												<i class="far fa-long-arrow-right"></i>
											</a>

										</div>

									</div>

								</div>

							<?php endforeach; ?>

						</div>

					</div>

				</div>

			</div>

		</section>
	<?php endif; ?>

	<section class="team-wrapper team-one  section-bg-2 pb-70">
		<img src="<?= asset('images/home-1/team/shape-04.webp') ?>" alt="" class="shape-4 tm-gsap-animate-circle">
		<div class="inner section-bg section-padding">
			<div class="shape">
				<img src="<?= asset('images/home-1/team/shape-01.webp') ?>" alt=""
					class="shape-1 tm-gsap-animate-circle">
				<img src="<?= asset('images/home-1/team/shape-02.webp') ?>" alt="" class="shape-2">
				<img src="<?= asset('images/home-1/team/shape-03.webp') ?>" alt="" class="shape-3">
			</div>
			<div class="auto-container">
				<div class="section-title text-center">
					<div class="sub-title">
						<svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path
								d="M6.81319 14.6759C6.83947 14.8971 7.16053 14.8971 7.18681 14.6759L7.40705 12.8197C7.69143 10.4229 9.58112 8.53323 11.9779 8.24884L13.834 8.0286C14.0553 8.00233 14.0553 7.68127 13.834 7.65499L11.9779 7.43475C9.58112 7.15036 7.69143 5.26068 7.40705 2.86391L7.18681 1.00776C7.16053 0.786476 6.83947 0.786476 6.81319 1.00776L6.59296 2.86391C6.30857 5.26068 4.41888 7.15036 2.02209 7.43475L0.165943 7.65499C-0.0553144 7.68127 -0.0553144 8.00233 0.165943 8.0286L2.02209 8.24884C4.41888 8.53323 6.30857 10.4229 6.59296 12.8197L6.81319 14.6759Z"
								fill="currentColor" />
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
								<img class="image-shape" src="<?= asset('images/home-1/team/image-shape.webp') ?>"
									alt="">
								<div class="image">
									<img src="<?= asset('images/quantal/founders/shailesh-jain.png') ?>"
										alt="Shailesh Jain">
								</div>
							</div>
							<div class="social-icon">
								<a href="https://www.linkedin.com/in/shaileshkumarjain/" target="_blank"
									rel="noopener"><i class="fa-brands fa-linkedin-in"></i></a>
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
								<img class="image-shape" src="<?= asset('images/home-1/team/image-shape.png') ?>"
									alt="">
								<div class="image">
									<img src="<?= asset('images/quantal/founders/nirav-shah.png') ?>" alt="Nirav Shah">
								</div>
							</div>
							<div class="social-icon">
								<a href="https://www.linkedin.com/in/theniravshah/" target="_blank" rel="noopener"><i
										class="fa-brands fa-linkedin-in"></i></a>
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

	<!-- Testimonials (shared, site-wide) -->
	<section class="testimonial-wrapper testimonial-one pb-100">
		<div class="auto-container">
			<div class="row g-sm-4">
				<div class="col-xl-12 col-lg-12">
					<div class="slider-box">
						<div class="section-title">
							<div class="sub-title">
								<svg width="14" height="15" viewBox="0 0 14 15" fill="none"
									xmlns="http://www.w3.org/2000/svg">
									<path
										d="M6.81319 14.6759C6.83947 14.8971 7.16053 14.8971 7.18681 14.6759L7.40705 12.8197C7.69143 10.4229 9.58112 8.53323 11.9779 8.24884L13.834 8.0286C14.0553 8.00233 14.0553 7.68127 13.834 7.65499L11.9779 7.43475C9.58112 7.15036 7.69143 5.26068 7.40705 2.86391L7.18681 1.00776C7.16053 0.786476 6.83947 0.786476 6.81319 1.00776L6.59296 2.86391C6.30857 5.26068 4.41888 7.15036 2.02209 7.43475L0.165943 7.65499C-0.0553144 7.68127 -0.0553144 8.00233 0.165943 8.0286L2.02209 8.24884C4.41888 8.53323 6.30857 10.4229 6.59296 12.8197L6.81319 14.6759Z"
										fill="currentColor" />
								</svg>
								<span>Client Stories</span>
							</div>
							<h2 class="title split-text split-in-right">What clients say <span>about
									us.</span></h2>
						</div>
						<div class="swiper testimonial-slider">
							<div class="swiper-wrapper">
								<div class="swiper-slide">
									<div class="testimonial-block">
										<p class="text">&ldquo;Quantal AI and Team are EXPERTS at
											building ANY AI functionality you&rsquo;re seeking!
											We&rsquo;ve hired them for 2 projects already &mdash; each
											completed ON TIME and UNDER BUDGET. Highly
											Recommended!&rdquo;</p>
										<div class="infu">
											<div class="image"><img
													src="<?= asset('images/quantal/clients/myhomecarebiz.jpg') ?>"
													alt="Melissa C"></div>
											<div class="name-info">
												<h5 class="name">Melissa C</h5>
												<span>myhomecarebiz.com</span>
											</div>
										</div>
									</div>
								</div>
								<div class="swiper-slide">
									<div class="testimonial-block">
										<p class="text">&ldquo;Working with Quantal AI team has been an
											absolute pleasure. Their technical aptitude is outstanding
											&mdash; they&rsquo;re not only highly competent but also
											creative, thoughtful, and reliable. They built a complex
											integration for our wine business connecting PhotoRoom,
											Google Cloud, AWS, and Shopify, and it works
											beautifully.&rdquo;</p>
										<div class="infu">
											<div class="image"><img
													src="<?= asset('images/quantal/clients/osteopathic_healing_hands.png') ?>"
													alt="David F"></div>
											<div class="name-info">
												<h5 class="name">David F</h5><span>Osteopathic Healing
													Hands</span>
											</div>
										</div>
									</div>
								</div>
								<div class="swiper-slide">
									<div class="testimonial-block">
										<p class="text">&ldquo;It was a pleasure working with Quantal AI
											team. Communication was smooth, deadlines were respected,
											and the overall collaboration was professional and
											efficient. I would definitely consider working together
											again in the future. Recommended!&rdquo;</p>
										<div class="infu">
											<div class="image"><img
													src="<?= asset('images/quantal/clients/elunic.jpg') ?>"
													alt="Ivana M"></div>
											<div class="name-info">
												<h5 class="name">Ivana M</h5><span>Elunic AG</span>
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

	<!-- Related services -->
	<?php if (!empty($related_items)): ?>
		<section class="related-services-section pb-100">
			<div class="container">

				<div class="sec-title">
					<span class="sub-title">
						<?= e($related_sub ?? '') ?>
					</span>
					<h2>
						<?= $related_title_html ?? '' /* trusted HTML */ ?>
					</h2>
					<div class="text">
						<?= e($related_text ?? '') ?>
					</div>
				</div>

				<div class="swiper service-slide">
					<div class="swiper-wrapper">

						<?php foreach ($related_items as $item): ?>

							<?php
							$svc_url = !empty($item['slug']) ? url('/services/' . $item['slug']) : '#';
							$svc_image = !empty($item['featured_image'])
								? media_url($item['featured_image'])
								: asset('images/quantal/services/image_ai.png');
							?>

							<div class="swiper-slide">
								<div class="service-block">

									<div class="image">
										<img src="<?= attr($svc_image) ?>" alt="<?= attr($item['label'] ?? $item['title']) ?>">
									</div>

									<div class="content">

										<span class="tag">
											<?= e($item['label'] ?? '') ?>
										</span>

										<h4 class="title">
											<a href="<?= attr($svc_url) ?>">
												<?= e($item['title'] ?? $item['label']) ?>
											</a>
										</h4>

										<?php if (!empty($item['excerpt'])): ?>
											<p class="text">
												<?= e(truncate_text($item['excerpt'], 85)) ?>
											</p>
										<?php endif; ?>

										<a href="<?= attr($svc_url) ?>" class="theme-btn-main theme-btn-main2">
											<span class="theme-btn-arrow-left">
												<i class="far fa-long-arrow-right"></i>
											</span>

											<span class="theme-btn">
												Read More
											</span>

											<span class="theme-btn-arrow-right">
												<i class="far fa-long-arrow-right"></i>
											</span>
										</a>

									</div>

								</div>
							</div>

						<?php endforeach; ?>

					</div>

					<div class="swiper-dot color-style-two border-style center">
						<div class="dot"></div>
					</div>

				</div>

			</div>
		</section>
	<?php endif; ?>

	<!-- Knowledge hub -->
	<?php if (!empty($blog_posts)): ?>
		<section class="knowledge-hub-section pb-100 section-bg-3">
			<div class="container">
				<div class="sec-title text-center mb-70">
					<span class="sub-title">
						<?= e($blog_sub ?? '') ?>
					</span>
					<h2>
						<?= $blog_title_html ?? '' /* trusted HTML */ ?>
					</h2>
					<div class="text">
						<?= e($blog_text ?? '') ?>
					</div>
				</div>
				<div class="row g-4">
					<?php foreach ($blog_posts as $i => $post): ?>
						<div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="<?= 0.2 + $i * 0.2 ?>s">
							<article class="knowledge-card">
								<div class="knowledge-image">
									<img src="<?= preg_match('/^https?:\/\//', $post['image'])
										? e($post['image'])
										: url($post['image']) ?>" alt="<?= attr($post['category']) ?>">
								</div>
								<div class="knowledge-content">
									<!-- <span class="knowledge-category"><?= e($post['category']) ?></span> -->
									<h4><a href="<?= $post['link'] ?? '#' ?>">
											<?= e(truncate_text($post['title'], 80)) ?>
										</a>
									</h4>
									<p>
										<?= e(truncate_text($post['desc'], 120)) ?>
									</p>
									<a href="<?= $post['link'] ?? '#' ?>" class="knowledge-btn">Read More <i
											class="far fa-arrow-right"></i></a>
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
		<section class="pb-100 faq-section">

			<div class="container faq-content pt-70">
				<h3 class="mb-3">Frequently Asked Questions</h3>
				<p class="text">
					<?= e($faq_intro ?? '') ?>
				</p>
				<ul class="accordion-box wow fadeInUp p-0 mt-40" data-wow-delay=".3s">
					<?php foreach ($faqs as $i => $faq):
						$is_active = $i === 1;  // mimic theme's pre-opened second item
						$faq_q = $faq['question'] ?? ($faq[0] ?? '');
						$faq_a = $faq['answer'] ?? ($faq[1] ?? ''); ?>
						<li class="accordion block<?= $is_active ? ' active-block' : '' ?>">
							<div class="acc-btn<?= $is_active ? ' active' : '' ?>">
								<?= e($faq_q) ?>
								<div class="icon fa fa-plus"></div>
							</div>
							<div class="acc-content<?= $is_active ? ' current' : '' ?>">
								<div class="content">
									<div class="text">
										<?= e($faq_a) ?>
									</div>
								</div>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>
	<?php endif; ?>

	<!-- Final CTA  -->
	<?php if (!empty($final_cta_title_html) || !empty($final_cta_desc) || !empty($final_cta_btn_text)): ?>
		<section class="mid-cta-section pb-100">
			<div class="decor-glow decor-glow--left decor-glow--top" aria-hidden="true"></div>
			<div class="decor-glow decor-glow--right decor-glow--bottom" aria-hidden="true"></div>
			<div class="container">
				<div class="mid-cta-box">

					<?php if (!empty($final_cta_title_html)): ?>
						<h2>
							<?= $final_cta_title_html /* trusted HTML */ ?>
						</h2>
					<?php endif; ?>

					<?php if (!empty($final_cta_desc)): ?>
						<p>
							<?= e($final_cta_desc) ?>
						</p>
					<?php endif; ?>

					<div class="service-btns">
						<hr>

						<?php if (!empty($final_cta_btn_text)): ?>
							<a href="<?= url($final_cta_btn_url ?: '/case-studies') ?>" class="theme-btn btn-style-border">
								<span class="btn-title">
									<?= e($final_cta_btn_text) ?>
								</span>
							</a>
						<?php endif; ?>
						<br>


					</div>
					<p class="cta-contact mt-4 mb-0">
						Or email us directly at
						<a href="mailto:contact@quantaltech.ai">contact@quantaltech.ai</a>
						or call
						<a href="tel:+13158093225">+1 315 809 3225</a>.
					</p>

				</div>
			</div>
		</section>
	<?php endif; ?>

</div>