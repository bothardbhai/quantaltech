<?php

/**
 * Podcast Final CTA — reuses the exact same ".contact-details" component,
 * markup, backend endpoint (/contact-submit), CSRF field, and AJAX submit
 * JS as pages/success-stories/index.php's CTA section (lines 275-402), per
 * the spec's explicit instruction that both the main Podcast page and every
 * episode detail page share one identical CTA. Only the copy and the form
 * field set differ (First Name / Last Name / Company Name / Email /
 * Message instead of Name / Phone / Email / Subject / Message) — see the
 * additive form_first_name/form_last_name/form_company handling added to
 * pages/contact-submit.php. Included verbatim on both podcast pages, not
 * duplicated, so there is exactly one place to update this component.
 */
?>
<section class="contact-details pb-100 dark-bg">
    <div class="decor-glow decor-glow--left decor-glow--top" aria-hidden="true"></div>
    <div class="decor-glow decor-glow--right decor-glow--bottom" aria-hidden="true"></div>
    <div class="container">
        <div class="row">

            <!-- Left: CTA content -->
            <div class="col-lg-6">
                <div class="section-title mb-30">
                    <div class="sub-title">
                        <svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M6.81319 14.6759C6.83947 14.8971 7.16053 14.8971 7.18681 14.6759L7.40705 12.8197C7.69143 10.4229 9.58112 8.53323 11.9779 8.24884L13.834 8.0286C14.0553 8.00233 14.0553 7.68127 13.834 7.65499L11.9779 7.43475C9.58112 7.15036 7.69143 5.26068 7.40705 2.86391L7.18681 1.00776C7.16053 0.786476 6.83947 0.786476 6.81319 1.00776L6.59296 2.86391C6.30857 5.26068 4.41888 7.15036 2.02209 7.43475L0.165943 7.65499C-0.0553144 7.68127 -0.0553144 8.00233 0.165943 8.0286L2.02209 8.24884C4.41888 8.53323 6.30857 10.4229 6.59296 12.8197L6.81319 14.6759Z"
                                fill="currentColor" />
                        </svg>
                        <span>Let&rsquo;s Talk</span>
                    </div>
                    <h2 class="title split-text split-in-right">Turn Your AI Vision Into a <span>Production-Ready
                            Reality</span></h2>
                    <div class="text mt-3">Our team helps businesses move beyond AI exploration and build systems
                        that deliver real, measurable outcomes. Let&rsquo;s talk about what that looks like for you.
                    </div>
                </div>
                <ul class="list-unstyled contact-details__info">
                    <li class="d-block d-sm-flex align-items-sm-center">
                        <div class="icon">
                            <span class="lnr-icon-phone-plus"></span>
                        </div>
                        <div class="text ml-xs--0 mt-xs-10">
                            <h4>Call us</h4>
                            <a href="tel:+13158093225">+1 315 809 3225</a>
                        </div>
                    </li>
                    <li class="d-block d-sm-flex align-items-sm-center">
                        <div class="icon">
                            <span class="lnr-icon-envelope1"></span>
                        </div>
                        <div class="text ml-xs--0 mt-xs-10">
                            <h4>Email us</h4>
                            <a href="mailto:contact@quantaltech.ai">contact@quantaltech.ai</a>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Right: existing Contact Form (shared backend/JS, podcast-specific field set) -->
            <div class="col-lg-6">
                <div id="pd-contact-msg" class="contact-msg" style="display:none;"></div>
                <form id="pd_contact_form" name="pd_contact_form" action="<?= url('/contact-submit') ?>" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="form_subject" value="Podcast Page Inquiry">
                    <input type="hidden" name="form_type" value="podcast">
                    <input type="hidden" name="page_url" value="<?= attr(current_url()) ?>">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="mb-3"><input name="form_first_name" class="form-control" type="text"
                                    placeholder="First Name" required></div>
                        </div>
                        <div class="col-sm-6">
                            <div class="mb-3"><input name="form_last_name" class="form-control" type="text"
                                    placeholder="Last Name" required></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="mb-3"><input name="form_company" class="form-control" type="text"
                                    placeholder="Company Name"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="mb-3"><input name="form_email" class="form-control required email"
                                    type="email" placeholder="Email Address" required></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <textarea name="form_message" class="form-control required" rows="7"
                            placeholder="Message" required></textarea>
                    </div>
                    <div class="mb-5 theme-btn-main">
                        <input name="form_botcheck" type="hidden" value="">
                        <button type="submit" id="pd-contact-submit-btn"
                            class="theme-btn btn-style-one transform"><span class="btn-title">Submit</span></button>
                    </div>
                </form>
                <script>
                    (function () {
                        var form = document.getElementById('pd_contact_form');
                        var msgEl = document.getElementById('pd-contact-msg');
                        var btn = document.getElementById('pd-contact-submit-btn');
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
