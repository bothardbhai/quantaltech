<?php
/** Based on theme template - content swapped to Quantal AI. */
$page_title = 'Contact Us - Quantal AI';
$active_page = 'contact';
?>

<!-- Start main-content -->
<section class="page-banner news-banner" style="padding:120px 0 80px;background:#1d2327;color:#fff;text-align:center;">
    <div class="container">
        <h1 style="color:#fff;font-size:36px;margin:25px 0 12px;line-height:1.2;">Get in Touch</h1>
        <p style="opacity:0.75;margin:0;font-size:14px;">
            <a href="<?= url('/') ?>" style="color:#72aee6;">Home</a> &nbsp;/&nbsp;
            <span>Contact</span>
        </p>
    </div>
</section>
<!-- end main-content --> 
<!--Contact Details Start-->
<section class="contact-details my-5">
	<div class="container ">
		<div class="row">
		<div class="col-lg-6">
			<div class="section-title mb-30">
				<div class="sub-title">
				<svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path
					d="M6.81319 14.6759C6.83947 14.8971 7.16053 14.8971 7.18681 14.6759L7.40705 12.8197C7.69143 10.4229 9.58112 8.53323 11.9779 8.24884L13.834 8.0286C14.0553 8.00233 14.0553 7.68127 13.834 7.65499L11.9779 7.43475C9.58112 7.15036 7.69143 5.26068 7.40705 2.86391L7.18681 1.00776C7.16053 0.786476 6.83947 0.786476 6.81319 1.00776L6.59296 2.86391C6.30857 5.26068 4.41888 7.15036 2.02209 7.43475L0.165943 7.65499C-0.0553144 7.68127 -0.0553144 8.00233 0.165943 8.0286L2.02209 8.24884C4.41888 8.53323 6.30857 10.4229 6.59296 12.8197L6.81319 14.6759Z"
					fill="currentColor" />
				</svg>

				<span>Get in Touch</span>
				</div>
				<h2 class="title split-text split-in-right">
				Talk to an AI Expert
				</h2>
			</div>
			<!-- Contact Form -->
			<div id="contact-msg" class="contact-msg" style="display:none;"></div>
			<form id="contact_form" name="contact_form" action="<?= url('/contact-submit') ?>" method="post">
			<?= csrf_field() ?>
			<div class="row">
				<div class="col-sm-6">
				<div class="mb-3">
					<input name="form_name" class="form-control" type="text" placeholder="Enter Name" required>
				</div>
				</div>
				<div class="col-sm-6">
				<div class="mb-3">
					<input name="form_phone" class="form-control" type="text" placeholder="Enter Phone">
				</div>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-12">
				<div class="mb-3">
					<input name="form_email" class="form-control required email" type="email" placeholder="Enter Email" required>
				</div>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-12">
				<div class="mb-3">
					<input name="form_subject" class="form-control required" type="text" placeholder="Enter Subject" required>
				</div>
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
			<!-- Contact Form Validation-->
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
						if (data.success) { 
							form.reset(); 
							
							if (data.redirect) {
								window.location.href = data.redirect;
							}
						}
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
		<div class="col-lg-6">
			<div class="contact-details__right">
				<div class="section-title mb-30">
					<div class="sub-title">
					<svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path
						d="M6.81319 14.6759C6.83947 14.8971 7.16053 14.8971 7.18681 14.6759L7.40705 12.8197C7.69143 10.4229 9.58112 8.53323 11.9779 8.24884L13.834 8.0286C14.0553 8.00233 14.0553 7.68127 13.834 7.65499L11.9779 7.43475C9.58112 7.15036 7.69143 5.26068 7.40705 2.86391L7.18681 1.00776C7.16053 0.786476 6.83947 0.786476 6.81319 1.00776L6.59296 2.86391C6.30857 5.26068 4.41888 7.15036 2.02209 7.43475L0.165943 7.65499C-0.0553144 7.68127 -0.0553144 8.00233 0.165943 8.0286L2.02209 8.24884C4.41888 8.53323 6.30857 10.4229 6.59296 12.8197L6.81319 14.6759Z"
						fill="currentColor" />
					</svg>
					<span>Quantal AI (UltraGenius Tech Private Limited)</span>
					</div>
					<h2 class="title split-text split-in-right">
					Bringing AI expertise into your business
					</h2>
					<div class="text mt-3">We are always ready to help you and answer your questions. Drop us a line, give us a call, or visit our New York - we&rsquo;d love to hear about your AI project.</div>
				</div>
				<ul class="list-unstyled contact-details__info">
					<li class="d-block d-sm-flex align-items-sm-center ">
					<div class="icon">
						<span class="lnr-icon-phone-plus"></span>
					</div>
					<div class="text ml-xs--0 mt-xs-10">
						<h4>Call us</h4>
						<a href="tel:+13158093225">+1 315 809 3225</a>
					</div>
					</li>
					<li class="d-block d-sm-flex align-items-sm-center ">
					<div class="icon">
						<span class="lnr-icon-envelope1"></span>
					</div>
					<div class="text ml-xs--0 mt-xs-10">
						<h4>Email us</h4>
						<a href="mailto:contact@quantaltech.ai">contact@quantaltech.ai</a>
					</div>
					</li>
					<!--
					<li class="d-block d-sm-flex align-items-sm-center ">
					<div class="icon">
						<span class="lnr-icon-location"></span>
					</div>
					<div class="text ml-xs--0 mt-xs-10">
						<h4>Office</h4>
						<small>
							H 7 Sickanagar, V P Road, Mumbai &mdash; 400004, Maharashtra India
						</small>
					</div>
					</li>
					-->
					<li class="d-block d-sm-flex align-items-sm-center ">
					<div class="icon">
						<span class="lnr-icon-location"></span>
					</div>
					<div class="text ml-xs--0 mt-xs-10">
						<h4>Office</h4>
						<small>
							430 Park Avenue, New York, NY &mdash; 10022
						</small>
					</div>
					</li>
				</ul>
			</div>
		</div>
		</div>
	</div>
</section>
<!--Contact Details End-->

<!-- Map Section-->
<section class="map-section">
	<iframe class="map w-100" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3022.0622140700634!2d-73.9745517015516!3d40.76065635800788!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c258fb29a9ff9b%3A0x371eecb3cd1c6159!2s430%20Park%20Ave%2C%20New%20York%2C%20NY%2010022%2C%20USA!5e0!3m2!1sen!2sin!4v1784705295946!5m2!1sen!2sin" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>
</section>
<!--End Map Section-->

 <!-- Main Footer -->
