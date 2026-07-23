<?php
/**
 * Shared legal / policy page renderer.
 *
 * Uses theme page-title banner + a services-details-style content container
 * so typography (h3, p.text, blockquote) matches the rest of the site.
 *
 * Each calling file (terms-of-service.php, refund-policy.php) sets these:
 *
 *   $banner_title  — string, shown in the page-title banner
 *   $crumb_label   — string, breadcrumb leaf
 *   $intro_para    — string, leading paragraph (no heading)
 *   $sections      — array of section dicts:
 *       [
 *         'h'      => 'Section heading',         // shown as <h3>
 *         'paras'  => ['p1', 'p2', ...],         // <p class="text"> lines
 *         'list'   => ['item1', 'item2', ...],   // optional <ul>
 *         'note'   => 'Pull-out quote / note',   // optional <blockquote>
 *         'sub'    => [ ['h'=>'Sub h4', 'paras'=>[...], 'list'=>[...]] ]  // optional sub-sections
 *       ]
 *
 *   $contact_block — array (optional):
 *       [ 'email' => 'x@y.com', 'phone' => '+91 ...', 'address_html' => '...' ]
 *   $last_updated  — date string shown under banner title
 *
 * The router rejects URL paths starting with `_`, and discover_pages skips
 * underscore-prefixed files — so this template file is never served directly.
 */
?>

<!-- Start main-content -->
<section class="page-banner news-banner" style="padding:120px 0 80px;background:#1d2327;color:#fff;text-align:center;">
    <div class="container">
        <h1 style="color:#fff;font-size:36px;margin:0 0 14px;line-height:1.2;"><?= e($banner_title) ?></h1>
        <p style="opacity:0.75;margin:0;font-size:14px;">
            <a href="<?= url('/') ?>" style="color:#72aee6;">Home</a> &nbsp;/&nbsp;
            <span><?= e($crumb_label) ?></span>
        </p>
    </div>
</section>
<!-- end main-content -->

<!-- Start Policy Content -->
<section class="services-details pt-120 pb-90">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-10 col-lg-11">
                <div class="services-details__content">

                    <?php if (!empty($last_updated)): ?>
                        <p class="text" style="font-size:13px;color:#888;margin-bottom:24px;">
                            <em>Last updated: <?= e($last_updated) ?></em>
                        </p>
                    <?php endif; ?>

                    <?php if (!empty($intro_para)): ?>
                        <p class="text"><?= e($intro_para) ?></p>
                    <?php endif; ?>

                    <?php foreach ($sections as $i => $section): ?>
                        <h3 class="<?= $i === 0 ? 'mt-4' : 'mt-5' ?>"><?= e($section['h']) ?></h3>

                        <?php if (!empty($section['paras'])):
                            foreach ($section['paras'] as $p): ?>
                                <p class="text"><?= e($p) ?></p>
                            <?php endforeach;
                        endif; ?>

                        <?php if (!empty($section['list'])): ?>
                            <ul class="text" style="padding-left:24px;line-height:1.9;">
                                <?php foreach ($section['list'] as $li): ?>
                                    <li><?= e($li) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>

                        <?php if (!empty($section['note'])): ?>
                            <blockquote class="blockquote-one"><?= e($section['note']) ?></blockquote>
                        <?php endif; ?>

                        <?php if (!empty($section['sub'])):
                            foreach ($section['sub'] as $sub): ?>
                                <h4 class="mt-4" style="font-size:18px;font-weight:600;"><?= e($sub['h']) ?></h4>
                                <?php if (!empty($sub['paras'])):
                                    foreach ($sub['paras'] as $p): ?>
                                        <p class="text"><?= e($p) ?></p>
                                    <?php endforeach;
                                endif; ?>
                                <?php if (!empty($sub['list'])): ?>
                                    <ul class="text" style="padding-left:24px;line-height:1.9;">
                                        <?php foreach ($sub['list'] as $li): ?>
                                            <li><?= e($li) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            <?php endforeach;
                        endif; ?>
                    <?php endforeach; ?>

                    <?php if (!empty($contact_block)): ?>
                        <div class="service-details-help mt-5" style="background:#fafbff;border:1px solid #eaeaea;padding:24px 28px;border-radius:10px;">
                            <h3 class="help-title" style="font-size:20px;color:#1d2327;margin-bottom:14px;">Contact Information</h3>
                            <p class="text" style="margin:6px 0;">
                                <strong>Email:</strong>
                                <a href="mailto:<?= attr($contact_block['email']) ?>"><?= e($contact_block['email']) ?></a>
                            </p>
                            <p class="text" style="margin:6px 0;">
                                <strong>Phone:</strong>
                                <a href="tel:<?= attr(preg_replace('/[^+0-9]/', '', $contact_block['phone'])) ?>"><?= e($contact_block['phone']) ?></a>
                            </p>
                            <?php if (!empty($contact_block['address_html'])): ?>
                                <p class="text" style="margin:6px 0;"><?= $contact_block['address_html'] ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- End Policy Content -->
