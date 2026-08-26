<?php

/**
 * Podcast episode card — the ".pd-card" component used by the Podcast main
 * page's "Watch All Episodes" grid and reused as-is by the Podcast Detail
 * page's "Watch More Episodes" section, mirroring how
 * partials/success-story-card.php's ".case-block" is shared between the
 * Success Stories listing and detail pages.
 *
 * Expects, before including this file:
 *   $episode     array  ['title', 'guest_name', 'guest_designation',
 *                         'thumbnail', 'url', 'publish_date', 'video_id']
 *   $slot_class  string optional — outer wrapper classes (defaults to the
 *                        plain component with no positional style-2/style-3
 *                        variant)
 */

$slot_class = $slot_class ?? 'pd-card';
?>
<div class="<?= attr($slot_class) ?>">
    <div class="pd-card-image">
        <a href="<?= attr($episode['url']) ?>">
            <img src="<?= attr($episode['thumbnail']) ?>" alt="<?= attr($episode['title']) ?>">
        </a>
        <?php if (!empty($episode['video_id'])): ?>
            <!-- <button type="button" class="pd-play-btn" data-video-id="<?= attr($episode['video_id']) ?>"
                aria-label="Play episode: <?= attr($episode['title']) ?>">
                <i class="fas fa-play"></i>
            </button> -->
        <?php endif; ?>
    </div>
    <div class="content">
        <div class="title-area">
            <h4 class="title">
                <a href="<?= attr($episode['url']) ?>"><?= e(truncate_text($episode['title'], 55)) ?></a>
            </h4>
            <p class="text">
                <?= e($episode['guest_name']) ?>
                <?php if (!empty($episode['guest_designation'])): ?>
                    <span class="pd-guest-designation"><?= e($episode['guest_designation']) ?></span>
                <?php endif; ?>
            </p>
            <?php if (!empty($episode['publish_date'])): ?>
                <p class="pd-card-date"><i class="fa-light fa-calendar-days"></i> <?= e($episode['publish_date']) ?></p>
            <?php endif; ?>
        </div>
        <a href="<?= attr($episode['url']) ?>" class="arrow-icon">
            <i class="far fa-long-arrow-right"></i>
        </a>
    </div>
</div>