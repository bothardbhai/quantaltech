<?php

/**
 * Numbered, arrow-connected step cards (the "Proposed Architecture" look) —
 * a numbered card per step, joined by arrows on desktop/tablet and a vertical
 * timeline on mobile. Styled entirely by the .ss-architecture rules in
 * assets/css/style.css. Used by the Architecture section and, when an admin
 * picks "Flow cards", the Workflow section of pages/success-stories/single.php.
 *
 * Expects, before including this file:
 *   $flow_nodes  array  each: ['icon' => 'fas fa-x' (optional), 'title', 'subtitle' (optional),
 *                       'items' => string[] or newline-separated string (optional)]
 */

if (empty($flow_nodes)) {
    return;
}
?>
<div class="ss-architecture">
    <?php foreach ($flow_nodes as $i => $node):
        $items = $node['items'] ?? [];
        if (is_string($items)) {
            $items = array_values(array_filter(array_map('trim', explode("\n", $items))));
        }
        ?>
        <div class="ss-arch-item wow fadeInUp" data-wow-delay="<?= 0.1 + $i * 0.15 ?>s">
            <div class="ss-arch-number"><?= $i + 1 ?></div>
            <div class="ss-arch-connector"></div>
            <div class="ss-arch-card">
                <?php if (!empty($node['icon'])): ?>
                    <div class="ss-arch-icon"><i class="<?= attr($node['icon']) ?>"></i></div>
                <?php endif; ?>
                <h4><?= e($node['title'] ?? '') ?></h4>
                <?php if (!empty($node['subtitle'])): ?>
                    <span class="ss-arch-subtitle"><?= e($node['subtitle']) ?></span>
                <?php endif; ?>
                <?php if (!empty($items)): ?>
                    <ul class="ss-arch-list">
                        <?php foreach ($items as $item): ?>
                            <li><?= e($item) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
        <?php if ($i < count($flow_nodes) - 1): ?>
            <div class="ss-arch-arrow<?= $i === 2 ? ' ss-arch-arrow--break' : '' ?>" aria-hidden="true">
                <div class="ss-arch-arrow-spacer"></div>
                <div class="ss-arch-arrow-icon"><i class="far fa-long-arrow-right"></i></div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
