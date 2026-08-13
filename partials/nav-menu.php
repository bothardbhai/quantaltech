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

// Services dropdown is data-driven: every published Service Master entry
// becomes a menu item automatically, in the same order as the Services
// listing page (service_number). No hardcoded per-service links here — add
// a service in the admin and it appears in this menu on the next request.
// Defensive try/catch mirrors core/seo.php's pattern: nav-menu.php renders
// on every single page, so a DB hiccup here must degrade to "no dropdown
// items" rather than break the whole site's navigation.
$service_menu_items = [];
$pdo = function_exists('db') ? db() : null;
if ($pdo) {
    try {
        foreach (get_services($pdo, ['status' => 'published']) as $svc) {
            $service_menu_items[] = [
                'label' => $svc['name'],
                'href' => '/services/' . $svc['slug'],
                'key' => 'services',
            ];
        }
    } catch (\Throwable $e) {
        $service_menu_items = [];
    }
}

// Hire dropdown is data-driven the same way: every published Hire Master
// entry (admin/hire.php, `hire_pages` table) becomes a menu item automatically,
// ordered by sort_order (the same ordering the Hire Master admin list and hub
// grid use). No hardcoded per-role links here — add a hire page in the admin
// and it appears in this menu on the next request.
$hire_menu_items = [];
if ($pdo) {
    try {
        foreach (get_hire_pages($pdo, ['status' => 'published']) as $hire) {
            $hire_menu_items[] = [
                'label' => $hire['name'],
                'href' => '/hire/' . $hire['slug'],
                'key' => 'hire',
            ];
        }
    } catch (\Throwable $e) {
        $hire_menu_items = [];
    }
}

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
        'children' => $service_menu_items,
    ],
    [
        'label' => 'Resources',
        'href' => '',
        // 'href'  => '#',
        'key' => 'resources',
        'children' => [
            ['label' => 'Blogs', 'href' => '/blog', 'key' => 'blog'],
            ['label' => 'Success Stories', 'href' => '/success-stories', 'key' => 'resources'],
            // ['label' => 'Podcasts', 'href' => '/podcast', 'key' => 'resources'],
            // ['label' => 'Terms of Service', 'href' => '/terms-of-service', 'key' => 'terms'],
            // ['label' => 'Refund & Cancellation Policy', 'href' => '/refund-policy', 'key' => 'refund'],
        ],
    ],
    [
        'label' => 'Hire with Us',
        'href' => '',
        'key' => 'hire',
        'children' => $hire_menu_items,
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