<?php

/**
 * Nav menu — single source of truth for site navigation.
 *
 * The structure here drives both the desktop nav (rendered inline) and the
 * mobile menu (which the theme's JS clones from the desktop markup).
 *
 * To edit the nav: change the array below. Active state is auto-applied based
 * on $active_page (set by the router or template).
 */
$active = $active_page ?? '';

// Define the menu structure once. Each item: label, href, key (for active state),
// and optionally children.
//
// NOTE: Some theme entries (Pricing, FAQ, Testimonial, Team Details, Project Details,
// 404) are kept here for convenience but commented out. Uncomment as content goes live.
$menu = [
    [
        'label' => 'Home',
        'href' => '/',
        'key' => 'home',
    ],
    [
        'label' => 'About Us',
        'href' => '/about',
        'key' => 'about',
    ],
    [
        'label' => 'Services',
        'href' => '/services',
        'key' => 'services',
        'children' => [
            // ['label' => 'All Solutions',         'href' => '/services', 'key' => 'services'],
            ['label' => 'Voice AI', 'href' => '/services/voice-ai', 'key' => 'services'],
            ['label' => 'Text AI', 'href' => '/services/text-ai', 'key' => 'services'],
            ['label' => 'Image / Document AI', 'href' => '/services/image-document-ai', 'key' => 'services'],
            ['label' => 'Process Automation', 'href' => '/services/process-automation', 'key' => 'services'],
            ['label' => 'Ai Engineering', 'href' => '/services/ai-engineering', 'key' => 'services'],
        ],
    ],
    [
        'label' => 'Resources',
        'href' => '',
        // 'href'  => '#',
        'key' => 'resources',
        'children' => [
            ['label' => 'Blogs', 'href' => '/blog', 'key' => 'blog'],
            // ['label' => 'Success Stories', 'href' => '/success-stories', 'key' => 'resources'],
            // ['label' => 'Podcasts', 'href' => '/podcast', 'key' => 'resources'],
            // ['label' => 'Terms of Service', 'href' => '/terms-of-service', 'key' => 'terms'],
            // ['label' => 'Refund & Cancellation Policy', 'href' => '/refund-policy', 'key' => 'refund'],
        ],
    ],
    [
        'label' => 'Hire with Us',
        'href' => '',
        'key' => 'hire',
        'children' => [
            ['label' => 'Hire AI Engineers', 'href' => '/hire-ai-engineers', 'key' => 'hire'],
        ],
    ],
    [
        'label' => 'Contact Us',
        'href' => '/contact',
        'key' => 'contact',
    ],
];

/**
 * Render a single nav <li> recursively.
 */
function render_nav_item(array $item, string $active): void
{
    $is_active = ($item['key'] ?? '') === $active;
    $has_kids = !empty($item['children']);
    $classes = [];
    if ($is_active) {
        $classes[] = 'current';
    }
    if ($has_kids) {
        $classes[] = 'dropdown';
    }
    $class_attr = $classes ? ' class="' . implode(' ', $classes) . '"' : '';
    $href = ($item['href'] ? url($item['href']) : false);
    ?>
    <li<?= $class_attr ?>>
        <?php if ($href): ?>
            <a href="<?= attr($href) ?>"><?= e($item['label']) ?></a>
        <?php else: ?>
            <a><?= e($item['label']) ?></a>
        <?php endif; ?>
                
        <?php if ($has_kids): ?>
            <ul>
                <?php foreach ($item['children'] as $child): ?>
                    <?php render_nav_item($child, $active); ?>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </li>
    <?php
}
?>
<ul class="navigation">
<?php foreach ($menu as $item): ?>
    <?php render_nav_item($item, $active); ?>
<?php endforeach; ?>
</ul>
