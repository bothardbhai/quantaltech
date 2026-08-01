<?php

/**
 * Thank You Page
 *
 * Layout matches the reference design: a confirmation message on the left
 * and a numbered "what happens next" timeline on the right.
 *
 * COLOR NOTE: rather than guessing your brand hex codes, this reuses the
 * theme's own already-styled components so it automatically inherits your
 * real site colors:
 *   - .process-number / .process-item / .process-content -> the exact same
 *     purple-square + connector-line timeline used on the service pages
 *     (our-framework-section / process-timeline-section).
 *   - .theme-btn.btn-style-one / .btn-style-border -> your existing buttons.
 * The only custom CSS below (.thankyou-check) just enlarges the icon badge
 * on the left — it does not set any new colors, so it will always match
 * whatever your .process-number color already is.
 *
 * $next_steps - array of ['title' => ..., 'desc' => ...] shown in the
 * right-hand timeline. Defaults to the 4 steps from the reference design;
 * override before including this file if a page needs different steps.
 */
$page_title = !empty($page_seo['title']) ? $page_seo['title'] : 'Thank You — Quantal AI';
$active_page = $active_page ?? '';

$thankyou_title = $thankyou_title ?? 'Thanks';
$thankyou_text = $thankyou_text ?? 'Our team will get back to you within one business day to discuss next steps.';

$next_steps = $next_steps ?? [
    ['title' => 'We Review Your Request', 'desc' => 'Within one business day, our team carefully reviews your submission to understand what you are looking for.'],
    ['title' => 'We Reach Out', 'desc' => 'We get in touch to learn more about your requirements whether you are exploring AI solutions or looking to bring the right talent on board.'],
    ['title' => 'We Share the Right Approach', 'desc' => 'Based on your needs, we recommend the best path forward whether that is a tailored AI solution, a consultation, or matching you with the right professional.'],
    ['title' => 'We Get to Work', 'desc' => 'Once aligned, our team moves quickly to deliver results on time and within budget.'],
];
?>

<style>
    /* Sizing only — no colors set here, so it inherits your real .process-number color */
    .thankyou-check {
        width: 56px;
        height: 56px;
        font-size: 22px;
        margin-bottom: 24px;
    }
    .thankyou-timeline .process-item:last-child {
        margin-bottom: 0;
    }

	.process-item {
		position: relative;
		display: flex;
		align-items: flex-start;
		gap: 40px;
		padding-bottom: 35px;
		margin-bottom: 0px;
		border-bottom: none;
	}

	.process-content h3 {
		color: #fff;
		font-size: 26px;
		margin-bottom: 10px;
	}

	.process-content {
		flex: 1;
		padding-top: 0px;
	}

	.process-content p {
		color: #a8a8a8;
		font-size: 16px;
		line-height: 1.3;
		max-width: 720px;
	}

	.error-page__title-box img{
		width: -webkit-fill-available;
	}
    @media (max-width: 991px) {
        .thankyou-timeline {
            margin-top: 40px;
        }
    }
</style>

<!-- Thank You Section -->
<section class="thankyou-section pt-120 pb-100">
    <div class="container">
        <div class="row align-items-center g-5">

            <!-- Left: confirmation message -->
            <div class="col-lg-5 col-md-12">
                <div class="error-page__title-box">
									<img src="<?= url('/assets/images/resource/thank-you.webp') ?>" alt="">
								</div>

                <!-- <h2 class="sec-title mb-20"><?= e($thankyou_title) ?></h2> -->

                <p class="text mt-3"><?= e($thankyou_text) ?></p>

                <div class="mt-4">
                    <a href="<?= url('/') ?>" class="theme-btn btn-style-one me-3">
                        <span class="btn-title">Back to Home</span>
                    </a>
                    <a href="<?= url('/services') ?>" class="theme-btn btn-style-border">
                        <span class="btn-title">Explore Our Services</span>
                    </a>
                </div>
            </div>

            <!-- Right: what happens next timeline -->
            <div class="col-lg-7 col-md-12">
                <div class="process-timeline thankyou-timeline">
                    <?php foreach ($next_steps as $i => $step): ?>
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

        </div>
    </div>
</section>
<!-- End Thank You Section -->