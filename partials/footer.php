<?php

/**
 * Footer partial — site footer + theme JS + closing tags.
 *
 * Markup is the theme's original `<footer class="footer-section fix bg-cover">`
 * block (lines 1327-1411 of theme/index.html). Only content (logo path,
 * email/phone/address, links) is swapped to Quantal real values.
 *
 * Closes the page-wrapper that header.php opens (theme leaves dangling).
 */
?>

<a href="https://wa.me/13158093225?text=Hello%20Quantal%20AI,%20I%20would%20like%20to%20know%20more%20about%20your%20services."
   class="whatsapp-float"
   target="_blank"
   rel="noopener noreferrer"
   aria-label="Chat on WhatsApp">
    <i class="fab fa-whatsapp"></i>
</a>

<!-- Main Footer -->
<footer class="footer-section fix bg-cover" style="background-image: url('<?= asset('images/home-1/footer-line.png') ?>');">
  <div class="container">
    <div class="footer-top-wrapper">
      <div class="row">
        <div class="col-6 logo-section">
          <div>
            <a href="<?= url('/') ?>" class="footer-logo wow fadeInUp brand-logo" data-wow-delay=".3s">
            <img class="img-fluid" src="<?= asset('images/brain-logo.png') ?>" alt="<?= attr(SITE_NAME) ?>">
            <span class="brand-title">Quantal AI</span>
            </a>
          </div>
          <div class="widget-title mb-25 wow fadeInUp" data-wow-delay=".3s">
            <p class="">With 15+ years of combined expertise, Quantal AI helps businesses move beyond AI experimentation building production-ready agents, automation, and intelligent applications that deliver real, measurable outcomes.</p>
          </div>
        
        </div>
      <div class="col-6 lets-talk-content wow fadeInUp" data-wow-delay=".5s">
        <h2 class="title" style="text-align: end;">
          Let&rsquo;s Talk <a href="<?= url('/contact') ?>" class="arrow-icon"><i class="fa-regular fa-arrow-up-right"></i></a>
          <span>Work Together</span>
        </h2>
      </div>
      </div>
    </div>
    <div class="footer-widget-wrapper">
      <div class="footer-vec d-none d-xxl-block">
        <img src="<?= asset('images/home-1/footer-vec.png') ?>" alt="img" class="tm-gsap-animate-circle">
      </div>
      <div class="row g-4 justify-content-between align-items-start align-items-xl-end">
        <div class="col-xl-5 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay=".3s">
          <div class="footer-contact-items">
            <h4 class="font-size-24 text-white font-weight-500">Contact Us</h4>
            <p><a href="mailto:contact@quantaltech.ai">contact@quantaltech.ai</a></p>
            <h4 class="call-title"><a href="tel:+13158093225">+1 315 809 3225</a></h4>
            <!--<p>H 7 Sickanagar, V P Road, Mumbai — 400004</p>-->
            <p>430 Park Avenue, New York, NY — 10022</p>
            
            <div class="social-icon">
              <a href="https://www.linkedin.com/company/quantal-ai" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
              <a href="https://www.youtube.com/@QuantaltechAI" target="_blank" rel="noopener" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
              <a href="https://www.instagram.com/quantaltech.ai/" target="_blank" rel="noopener" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
              <a href="https://www.upwork.com/agencies/1866102204539548169/" target="_blank" rel="noopener" aria-label="UpWork"><i class="fa-brands fa-upwork"></i></a>
              <a href="https://www.glassdoor.co.in/Overview/Working-at-Quantal-AI-EI_IE10895843.11,21.htm" target="_blank" rel="noopener" aria-label="GlassDoor"><i class="fa-regular fa-quotes"></i></a>
            </div>
            <h5 class="get-title">Get the latest AI engineering insights</h5>
            <form id="newsletter-form" action="<?= url('/newsletter') ?>" method="post">
              <?= csrf_field() ?>
              <input type="email" name="email" id="footer-email" placeholder="Enter your email" required>
              <button type="submit" aria-label="Subscribe">
                <i class="fa-sharp fa-regular fa-paper-plane"></i>
              </button>
            </form>
            <p id="newsletter-msg" style="display:none;margin-top:8px;font-size:13px;"></p>
          </div>
        </div>
        <div class="col-xl-2 d-none d-xl-block"></div>
        <div class="col-xl-4 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay=".5s">
          <div class="footer-list-area">
            <div class="footer-widget-items">
              <div class="widget-title mb-25">
                <h4 class="font-size-24 text-white font-weight-500">Quick Link</h4>
              </div>
              <ul class="user-links">
                <li><a href="<?= url('/') ?>">Home</a></li>
                <li><a href="<?= url('/about') ?>">About</a></li>
                <li><a href="<?= url('/hire') ?>">Hire with Us</a></li>
                <li><a href="<?= url('/blog') ?>">Resources</a></li>
                <li><a href="<?= url('/contact') ?>">Contact Us</a></li>
              </ul>
            </div>
            <div class="footer-widget-items">
              <div class="widget-title mb-25">
                <h4 class="font-size-24 text-white font-weight-500">Our Solutions</h4>
              </div>
              <ul class="user-links">
                <li><a href="<?= url('/services') ?>">All Services</a></li>
                <li><a href="<?= url('/services/voice') ?>">Voice AI</a></li>
                <li><a href="<?= url('/services/text') ?>">Text AI</a></li>
                <li><a href="<?= url('/services/image') ?>">Image / Document AI</a></li>
                <li><a href="<?= url('/services/process-auto') ?>">Process Automation</a></li>
              </ul>
            </div>
          </div>
        </div>
        <div class="col-xl-1 d-none d-xl-block"></div>
      </div>
    </div>
    <!--<span style="display:inline-block">GSTIN: 27AACCU9781D1Z0</span>-->
    <div class="footer-bottom wow fadeInUp" data-wow-delay=".3s">
      <p>&copy; Copyright <?= date('Y') ?> by UltraGenius Tech Pvt Ltd : All Rights Reserved.</p>
      <ul class="footer-menu">
        <li><a href="<?= url('/terms-of-service') ?>">Terms &amp; Condition</a></li>
        <li><a href="<?= url('/refund-policy') ?>">Refund Policy</a></li>
        <li><a href="<?= url('/contact') ?>">Contact us</a></li>
      </ul>
    </div>
  </div>
</footer>

</div><!-- /.page-wrapper (opened in header.php; theme leaves this dangling — fixed here) -->

<!-- Theme JS -->
<script src="<?= asset('js/jquery.js') ?>"></script>
<script src="<?= asset('js/popper.min.js') ?>"></script>
<script src="<?= asset('js/bootstrap.min.js') ?>"></script>
<script src="<?= asset('js/jquery.fancybox.js') ?>"></script>
<script src="<?= asset('js/jquery-ui.js') ?>"></script>
<script src="<?= asset('js/three.js') ?>"></script>
<script src="<?= asset('js/gsap.js') ?>"></script>
<script src="<?= asset('js/hover-effect.umd.js') ?>"></script>
<script src="<?= asset('js/gsap-scroll-to-plugin.js') ?>"></script>
<script src="<?= asset('js/gsap-scroll-smoother.js') ?>"></script>
<script src="<?= asset('js/gsap-scroll-trigger.js') ?>"></script>
<script src="<?= asset('js/gsap-split-text.js') ?>"></script>
<script src="<?= asset('js/gsap-custom.js') ?>"></script>
<script src="<?= asset('js/split-type.min.js') ?>"></script>
<script src="<?= asset('js/parallaxie.js') ?>"></script>
<script src="<?= asset('js/jquery.magnific-popup.min.js') ?>"></script>
<script src="<?= asset('js/wow.js') ?>"></script>
<script src="<?= asset('js/bxslider.js') ?>"></script>
<script src="<?= asset('js/nice-select.min.js') ?>"></script>
<script src="<?= asset('js/knob.js') ?>"></script>
<script src="<?= asset('js/appear.js') ?>"></script>
<script src="<?= asset('js/slick.js') ?>"></script>
<script src="<?= asset('js/swiper.min.js') ?>"></script>
<script src="<?= asset('js/mixitup.js') ?>"></script>
<script src="<?= asset('js/script.js') ?>"></script>
<script src="<?= asset('js/sticky-service-help.js') ?>"></script>
<script>window.QUANTAL_SEARCH_API = <?= json_encode(rtrim(BASE_URL, '/') . '/api/search.php', JSON_UNESCAPED_SLASHES) ?>;</script>
<script src="<?= asset('js/live-search.js') ?>"></script>
<script>
(function () {
    var form = document.getElementById('newsletter-form');
    var msg  = document.getElementById('newsletter-msg');
    if (!form || !msg) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var btn = form.querySelector('button[type=submit]');
        btn.disabled = true;

        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            msg.textContent   = data.message;
            msg.style.display = 'block';
            msg.style.color   = data.success ? '#4ade80' : '#f87171';
            if (data.success) { form.reset(); }
        })
        .catch(function () {
            msg.textContent   = 'Something went wrong. Please try again.';
            msg.style.display = 'block';
            msg.style.color   = '#f87171';
        })
        .finally(function () { btn.disabled = false; });
    });
})();
</script>

</body>
</html>
