<?php

/**
 * Success Story card — the ".case-block" component used by the Home page's
 * "Success Stories That Transform Businesses" section (pages/home.php) and
 * reused as-is (same HTML/classes) by the Success Stories Detail page's
 * "More Success Stories" section (pages/success-stories/single.php), so
 * both places render an identical card rather than maintaining two designs.
 *
 * Expects, before including this file:
 *   $story      array  ['title', 'category', 'image', 'url']
 *   $slot_class string optional — outer wrapper classes (defaults to the
 *                       plain component with no positional style-2/style-3
 *                       variant)
 */

$slot_class = $slot_class ?? 'case-block';
?>
<div class="<?= attr($slot_class) ?>">
    <div class="image not-hide-cursor" data-cursor="View<br>Case">
        <a href="<?= attr($story['url']) ?>" class="cursor-hide tp--hover-img"
            data-displacement="<?= attr($story['image']) ?>" data-intensity="0.6" data-speedin="1" data-speedout="1">
            <img src="<?= attr($story['image']) ?>" alt="<?= attr($story['title']) ?>">
        </a>
    </div>
    <div class="content">
        <div class="title-area">
            <h4 class="title">
                <a href="<?= attr($story['url']) ?>"><?= e(truncate_text($story['title'], 30)) ?></a>
            </h4>
            <p class="text"><?= e($story['category']) ?></p>
        </div>
        <a href="<?= attr($story['url']) ?>" class="arrow-icon">
            <i class="far fa-long-arrow-right"></i>
        </a>
    </div>
</div>