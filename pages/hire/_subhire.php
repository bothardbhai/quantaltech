<?php

/**
 * Hire Master presentation template — mirrors pages/services/_subservice.php.
 * Pure renderer: every section is driven by a variable set by whichever
 * bridge included this file (currently only _hire-dynamic.php), and every
 * non-core section is wrapped in `!empty()` so an unfilled section simply
 * doesn't render instead of leaving an empty heading.
 *
 * Variable contract (all optional unless noted):
 *   $page_title, $active_page      - core/hero, banner + <title>
 *   $hero_tag, $hero_title_html*, $hero_desc, $hero_features[{icon,text}]
 *   $impact_stats[{number,title}]
 *   $engineers[{name,role,years_of_experience,image,linkedin_url,education[],skills[]}] - "Meet Our Engineers"
 *   $expertise_sub, $expertise_title_html*, $expertise_text, $expertise_cards[{icon,title,desc}]
 *   $foundation_sub, $foundation_title_html*, $foundation_text, $foundation_cards[{image,title,desc}]
 *   $tech_sub, $tech_title_html*, $tech_text, $tech_categories[{title,items[]}]
 *   $build_sub, $build_title_html*, $build_text, $build_cards[{number,title,desc}]
 *   $engagement_sub, $engagement_title_html*, $engagement_text, $engagement_models[{icon,title,desc,featured}]
 *   $why_sub, $why_title_html*, $why_text, $why_cards[{title,desc}]
 *   $industries_sub, $industries_title_html*, $industries_text, $industries[{icon,title,desc}]
 *   $cta_tag, $cta_title_html*, $cta_text                          - mid-page CTA band
 *   $cs_sub, $cs_title_html*, $cs_text, $case_studies[{title,category,image,url}] - always the latest 4 published Success Stories, not manually picked
 *   $related_sub, $related_title_html*, $related_text, $related_items[{label,title,slug,excerpt,featured_image}]
 *   $blog_sub, $blog_title_html*, $blog_text, $blog_posts[{image,category,title,desc,link}]
 *   $faq_intro, $faqs[{question,answer}]
 *   $final_cta_title_html*, $final_cta_desc, $final_cta_btn_text, $final_cta_btn_url
 *   (* = trusted HTML, printed raw, not escaped)
 */
?>

<!-- Start main-content -->
<!-- <section class="page-banner news-banner" style="padding:120px 0 80px;background:#1d2327;color:#fff;text-align:center;">
	<div class="container">
		<h1 style="color:#fff;font-size:36px;margin:25px 0 12px;line-height:1.2;"><?= e($page_title) ?></h1>
		<p style="opacity:0.75;margin:0;font-size:14px;">
			<a href="<?= url('/') ?>" style="color:#72aee6;">Home</a> &nbsp;/&nbsp;
			<a href="<?= url('/hire-ai-engineers') ?>" style="color:#72aee6;">Hire with Us</a> &nbsp;/&nbsp;
			<span><?= e($page_label ?? $page_title) ?></span>
		</p>
	</div>
</section> -->
<!-- end main-content -->

<div class="services-details__content">

	<!-- Hero Section -->
	<section class="hire-ai-hero">
		<div class="container">

			<div class="hero-left">

				<?php if (!empty($hero_tag)): ?>
					<span class="hero-badge">
						<i class="bi bi-stars"></i>
						<?= e($hero_tag) ?>
					</span>
				<?php endif; ?>

				<h1>
					<?= $hero_title_html ?? '' /* trusted HTML */ ?>
				</h1>

				<?php if (!empty($hero_desc)): ?>
					<p>
						<?= e($hero_desc) ?>
					</p>
				<?php endif; ?>

				<?php if (!empty($hero_features)): ?>
					<div class="hero-features">
						<?php foreach ($hero_features as $feature): ?>
							<div class="feature-item">
								<div class="icon">
									<i class="<?= e($feature['icon'] ?? 'bi bi-check-lg') ?>"></i>
								</div>
								<span><?= e($feature['text'] ?? '') ?></span>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

			</div>


			<div class="hero-right">

				<div class="contact-card">

					<h3>Let's Build Your AI Team</h3>

					<form class="hire-form">

						<div class="grid-2">

							<div class="form-group">
								<label>Name</label>
								<input type="text" placeholder="John Doe">
							</div>

							<div class="form-group">
								<label>Email</label>
								<input type="email" placeholder="john@company.com">
							</div>

						</div>

						<div class="grid-2">

							<div class="form-group">
								<label>Phone</label>
								<input type="text" placeholder="+91 9876543210">
							</div>

							<div class="form-group">
								<label>Company</label>
								<input type="text" placeholder="Company Name">
							</div>

						</div>

						<div class="grid-2">

							<div class="form-group">
								<label>Country</label>
								<select class="custom-select">
									<option value="">Select Country</option>
									<option>India</option>
									<option>United States</option>
									<option>Canada</option>
								</select>
							</div>

							<div class="form-group">
								<label>Hiring Model</label>
								<select class="custom-select">
									<option>Select</option>
									<option>Dedicated</option>
									<option>Hourly</option>
									<option>Project Based</option>
								</select>
							</div>

						</div>

						<div class="form-group">
							<label>Project Requirements</label>

							<textarea rows="3" placeholder="Tell us about your AI project..."></textarea>
						</div>

						<button class="submit-btn">
							Schedule Free Consultation
						</button>

					</form>

				</div>

			</div>

		</div>

	</section>

	<!-- Count Section -->
	<?php if (!empty($impact_stats)): ?>
		<section class="impact-section pt-50 pb-50">
			<div class="container">

				<div class="row g-4">

					<?php foreach ($impact_stats as $i => $stat): ?>
						<div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="<?= 0.2 + $i * 0.1 ?>s">
							<div class="impact-card">
								<div class="impact-number"><?= e($stat['number'] ?? '') ?></div>
								<h5><?= e($stat['title'] ?? '') ?></h5>
							</div>
						</div>
					<?php endforeach; ?>

				</div>

			</div>
		</section>
	<?php endif; ?>

	<!-- Client Logo Section (shared, site-wide) -->
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

	<!-- Meet Our Engineers -->
	<?php if (!empty($engineers)): ?>
		<section class="meet-engineers-section pt-50 pb-50 section-bg-3">
			<div class="line-shape" aria-hidden="true">
				<img src="<?= asset('images/home-1/features/line-shape.png') ?>" alt="">
			</div>
			<div class="ellipse-shape" aria-hidden="true">
				<img src="<?= asset('images/home-1/features/ellipse-bg.png') ?>" alt="">
			</div>
			<div class="container">
				<div class="sec-title text-center mb-70">
					<h2>Meet Our Engineers</h2>
				</div>
				<div class="row g-4">
					<?php foreach ($engineers as $i => $eng): ?>
						<div class="col-lg-6 col-12<?= $i >= 4 ? ' d-none engineer-extra' : '' ?>">
							<div class="engineer-card">
								<div class="engineer-card__media">
									<div class="engineer-card__photo">
										<img src="<?= e($eng['image'] ? media_url($eng['image']) : asset('images/quantal/team/default-avatar.svg')) ?>"
											alt="<?= attr($eng['name']) ?>" loading="lazy">
									</div>
									<?php if (!empty($eng['linkedin_url'])): ?>
										<a class="theme-btn btn-style-border engineer-card__linkedin-btn"
											href="<?= attr($eng['linkedin_url']) ?>" target="_blank" rel="noopener">
											<i class="fa-brands fa-linkedin-in"></i>
											<span class="btn-title">LinkedIn</span>
										</a>
									<?php endif; ?>
								</div>
								<div class="engineer-card__info">
									<div class="engineer-card__header">
										<h4>
											<?= e($eng['name']) ?>
										</h4>
										<span class="engineer-card__exp">
											<?= (float) $eng['years_of_experience'] ?> Years of Experience
										</span>
									</div>
									<p class="engineer-card__role">
										<?= e($eng['role']) ?>
									</p>
									<?php if (!empty($eng['education'])): ?>
										<ul class="engineer-card__education">
											<?php foreach ($eng['education'] as $item): ?>
												<li>
													<?= e($item) ?>
												</li>
											<?php endforeach; ?>
										</ul>
									<?php endif; ?>
									<?php if (!empty($eng['skills'])): ?>
										<div class="engineer-card__skills">
											<?php foreach ($eng['skills'] as $skill): ?>
												<span class="skill-tag">
													<?= e($skill) ?>
												</span>
											<?php endforeach; ?>
										</div>
									<?php endif; ?>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<?php if (count($engineers) > 4): ?>
					<div class="row text-center mt-4">
						<div>
							<a href="javascript:void(0);" id="view-all-engineers"
								class="theme-btn-main wow fadeInUp justify-content-center" data-wow-delay=".9s">

								<span class="theme-btn-arrow-left">
									<i class="far fa-long-arrow-right"></i>
								</span>

								<span class="theme-btn">View All</span>

								<span class="theme-btn-arrow-right">
									<i class="far fa-long-arrow-right"></i>
								</span>

							</a>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</section>
		<?php if (count($engineers) > 4): ?>
			<script>
				(function () {
					var btn = document.getElementById('view-all-engineers');
					if (!btn) return;
					btn.addEventListener('click', function () {
						document.querySelectorAll('.engineer-extra').forEach(function (el) { el.classList.remove('d-none'); });
						btn.remove();
					});
				})();
			</script>
		<?php endif; ?>
	<?php endif; ?>

	<!-- Expertise of Our Engineers -->
	<?php if (!empty($expertise_cards)): ?>
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

					<?php if (!empty($expertise_sub)): ?>
						<span class="sub-title">
							<?= e($expertise_sub) ?>
						</span>
					<?php endif; ?>

					<h2>
						<?= $expertise_title_html ?? '' /* trusted HTML */ ?>
					</h2>

					<?php if (!empty($expertise_text)): ?>
						<div class="text">
							<?= e($expertise_text) ?>
						</div>
					<?php endif; ?>

				</div>

				<div class="row g-4">

					<?php foreach ($expertise_cards as $card): ?>
						<div class="col-md-4">

							<div class="framework-card">

								<div class="framework-icon">
									<i class="<?= e($card['icon'] ?? 'fas fa-brain') ?>"></i>
								</div>

								<h3><?= e($card['title'] ?? '') ?></h3>

								<p>
									<?= e($card['desc'] ?? '') ?>
								</p>

							</div>

						</div>
					<?php endforeach; ?>

				</div>

			</div>

		</section>
	<?php endif; ?>

	<!-- Foundation Model Expertise -->
	<?php if (!empty($foundation_cards)): ?>
		<section class="industry-solution-section pb-100 section-bg-3">

			<div class="decor-glow decor-glow--left decor-glow--top" aria-hidden="true"></div>

			<div class="container">

				<div class="sec-title text-center mb-70">

					<?php if (!empty($foundation_sub)): ?>
						<span class="sub-title">
							<?= e($foundation_sub) ?>
						</span>
					<?php endif; ?>

					<h2>
						<?= $foundation_title_html ?? '' /* trusted HTML */ ?>
					</h2>

					<?php if (!empty($foundation_text)): ?>
						<div class="text">
							<?= e($foundation_text) ?>
						</div>
					<?php endif; ?>

				</div>

				<div class="row g-4">

					<?php foreach ($foundation_cards as $card): ?>
						<div class="col-lg-4 col-md-6">

							<div class="industry-solution-card">
								<div class="industry-solution-icon">
									<img src="<?= e(media_url($card['image'] ?? '')) ?>" alt="<?= e($card['title'] ?? '') ?>">
								</div>
								<h3><?= e($card['title'] ?? '') ?></h3>
								<p>
									<?= e($card['desc'] ?? '') ?>
								</p>
							</div>
						</div>
					<?php endforeach; ?>

				</div>

			</div>

		</section>
	<?php endif; ?>

	<!-- Our Tech -->
	<?php if (!empty($tech_categories)): ?>
		<section class="tech-stack-section pb-100 section-bg-3">

			<div class="decor-glow decor-glow--left decor-glow--bottom" aria-hidden="true"></div>

			<div class="container">

				<div class="sec-title">
					<span class="sub-title">
						<?= e($tech_sub ?? '') ?>
					</span>
					<h2>
						<?= $tech_title_html ?? '' /* trusted HTML */ ?>
					</h2>
					<div class="text">
						<?= e($tech_text ?? '') ?>
					</div>
				</div>

				<div class="tech-stack-wrapper row g-4">

					<?php foreach ($tech_categories as $cat): ?>
						<div class="col-lg-4 col-md-6 mb-4 d-flex wow fadeInUp">
							<div class="tech-category">
								<span class="tech-category-title">
									<?= e($cat['title']) ?>
								</span>

								<div class="tech-list">
									<?php foreach ($cat['items'] as $item): ?>
										<span class="tech-item">
											<?= e($item) ?>
										</span>
									<?php endforeach; ?>
								</div>

							</div>
						</div>
					<?php endforeach; ?>

				</div>

			</div>

		</section>
	<?php endif; ?>

	<!-- What Our Engineers Build -->
	<?php if (!empty($build_cards)): ?>
		<section class="benefits-section pb-100 section-bg-3">
			<div class="line-shape" aria-hidden="true">
				<img src="<?= asset('images/home-1/features/line-shape.png') ?>" alt="">
			</div>
			<div class="ellipse-shape" aria-hidden="true">
				<img src="<?= asset('images/home-1/features/ellipse-bg.png') ?>" alt="">
			</div>
			<div class="container">

				<div class="sec-title text-center mb-70">
					<?php if (!empty($build_sub)): ?>
						<span class="sub-title"><?= e($build_sub) ?></span>
					<?php endif; ?>

					<h2>
						<?= $build_title_html ?? '' /* trusted HTML */ ?>
					</h2>
				</div>

				<div class="row g-4">

					<?php foreach ($build_cards as $i => $card): ?>
						<div class="col-md-4 wow fadeInUp" data-wow-delay="<?= 0.2 + $i * 0.1 ?>s">
							<div class="benefit-card">
								<div class="benefit-number"><?= e($card['number'] ?? sprintf('%02d', $i + 1)) ?></div>

								<h3><?= e($card['title'] ?? '') ?></h3>

								<p>
									<?= e($card['desc'] ?? '') ?>
								</p>
							</div>
						</div>
					<?php endforeach; ?>
				</div>

			</div>
		</section>
	<?php endif; ?>

	<!-- Engagement Models -->
	<?php if (!empty($engagement_models)): ?>
		<section class="engagement-section pb-100 section-bg section-bg-3">

			<div class="container">

				<div class="sec-title text-center mb-70">

					<?php if (!empty($engagement_sub)): ?>
						<span class="sub-title">
							<?= e($engagement_sub) ?>
						</span>
					<?php endif; ?>

					<h2>
						<?= $engagement_title_html ?? '' /* trusted HTML */ ?>
					</h2>

				</div>

				<div class="row g-4">

					<?php foreach ($engagement_models as $model): ?>
						<div class="col-md-6 wow fadeInUp" data-wow-delay=".2s">

							<div class="engagement-card<?= !empty($model['featured']) ? ' featured' : '' ?>">

								<div class="engagement-icon">
									<i class="<?= e($model['icon'] ?? 'fas fa-brain') ?>"></i>
								</div>

								<h3><?= e($model['title'] ?? '') ?></h3>

								<p>
									<?= e($model['desc'] ?? '') ?>
								</p>

							</div>

						</div>
					<?php endforeach; ?>

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
	<?php endif; ?>

	<!-- Why Hire -->
	<?php if (!empty($why_cards)): ?>
		<section class="why-quantal-section pb-100 section-bg-3">

			<div class="about-vector tm-gsap-animate-circle">
				<img src="<?= asset('images/home-1/about/about-vector.png') ?>" alt="">
			</div>
			<div class="decor-glow decor-glow--right decor-glow--bottom" aria-hidden="true"></div>

			<div class="container">

				<div class="sec-title text-center mb-70">

					<?php if (!empty($why_sub)): ?>
						<span class="sub-title">
							<?= e($why_sub) ?>
						</span>
					<?php endif; ?>

					<h2>
						<?= $why_title_html ?? '' /* trusted HTML */ ?>
					</h2>

				</div>

				<div class="row g-4">

					<?php foreach ($why_cards as $i => $card): ?>
						<div class="col-lg-4 col-md-6 wow fadeInUp">

							<div class="why-card">

								<div class="why-number"><?= e(sprintf('%02d', $i + 1)) ?></div>

								<h3><?= e($card['title'] ?? '') ?></h3>

								<p>
									<?= e($card['desc'] ?? '') ?>
								</p>

							</div>

						</div>
					<?php endforeach; ?>

				</div>

			</div>

		</section>
	<?php endif; ?>

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

	<!-- Industry Section  -->
	<?php if (!empty($industries)): ?>
		<section class="industry-solution-section pb-100 section-bg-3">

			<div class="decor-glow decor-glow--left decor-glow--top" aria-hidden="true"></div>

			<div class="container">

				<div class="sec-title text-center mb-70">

					<?php if (!empty($industries_sub)): ?>
						<span class="sub-title">
							<?= e($industries_sub) ?>
						</span>
					<?php endif; ?>

					<h2>
						<?= $industries_title_html ?? '' /* trusted HTML */ ?>
					</h2>

				</div>

				<div class="row g-4">

					<?php foreach ($industries as $industry): ?>
						<div class="col-lg-4 col-md-6">

							<div class="industry-solution-card">
								<div class="industry-solution-icon">
									<i class="<?= e($industry['icon'] ?? 'fas fa-industry') ?>"></i>
								</div>
								<h3><?= e($industry['title'] ?? '') ?></h3>
								<p>
									<?= e($industry['desc'] ?? '') ?>
								</p>
							</div>
						</div>
					<?php endforeach; ?>

				</div>

			</div>

		</section>
	<?php endif; ?>

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
									<svg width="14" height="15" viewBox="0 0 14 15" fill="none"
										xmlns="http://www.w3.org/2000/svg">
										<path
											d="M6.81319 14.6759C6.83947 14.8971 7.16053 14.8971 7.18681 14.6759L7.40705 12.8197C7.69143 10.4229 9.58112 8.53323 11.9779 8.24884L13.834 8.0286C14.0553 8.00233 14.0553 7.68127 13.834 7.65499L11.9779 7.43475C9.58112 7.15036 7.69143 5.26068 7.40705 2.86391L7.18681 1.00776C7.16053 0.786476 6.83947 0.786476 6.81319 1.00776L6.59296 2.86391C6.30857 5.26068 4.41888 7.15036 2.02209 7.43475L0.165943 7.65499C-0.0553144 7.68127 -0.0553144 8.00233 0.165943 8.0286L2.02209 8.24884C4.41888 8.53323 6.30857 10.4229 6.59296 12.8197L6.81319 14.6759Z"
											fill="currentColor" />
									</svg>
									<span>Featured Projects</span>
								</div>
								<h2 class="title split-text split-in-right">Success Stories That <span> Transform
										Businesses</span></h2>
							</div>
							<a class="theme-btn-main mb-5 mb-xl-0 wow fadeInUp" data-wow-delay=".3s"
								href="<?= url('/success-stories') ?>">
								<span class="theme-btn-arrow-left"> <i class="far fa-long-arrow-right "></i> </span>
								<span class="theme-btn ">View All Case Studies</span>
								<span class="theme-btn-arrow-right"> <i class="far fa-long-arrow-right"></i> </span>
							</a>
							<!-- <h2 class="title-shadow titlt-bottom-top d-none d-xl-block">case studies</h2> -->
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
										$cs_alt = !empty($cs['title']) ? $cs['title'] : 'Success story';
										$cs_url = !empty($cs['url']) ? $cs['url'] : url('/success-stories');
										?>
										<div class="image not-hide-cursor" data-cursor="View<br>Case">
											<a href="<?= attr($cs_url) ?>" class="cursor-hide tp--hover-img"
												data-displacement="<?= attr($cs_image) ?>" data-intensity="0.6" data-speedin="1"
												data-speedout="1">
												<img src="<?= attr($cs_image) ?>" alt="<?= attr($cs_alt) ?>">
											</a>
										</div>

										<div class="content">

											<div class="title-area">

												<h4 class="title">
													<a href="<?= attr($cs_url) ?>"><?= e(truncate_text($cs['title'], 30)) ?></a>
												</h4>

												<p class="text">
													<?= e($cs['category'] ?? '') ?>
												</p>

											</div>

											<a href="<?= attr($cs_url) ?>" class="arrow-icon">
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

	<!-- Founders/team (shared, site-wide) -->
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

	<!-- Related hire pages + related services -->
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
							$svc_url = !empty($item['slug']) ? url('/' . $item['slug']) : '#';
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