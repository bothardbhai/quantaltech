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

// Mega menus (Services + Hire with Us): subcategory tabs, each holding its
// published pages. Subcategories are managed in admin/service-subcategories.php
// and admin/hire-subcategories.php. Grouping only — every link is still the
// page's own /services/{slug} or /hire/{slug} URL. If there are no subcategory
// groups yet (or the lookup fails, e.g. migrations not run), the flat
// dropdowns below are used unchanged.
//
// Returns groups normalized for render_mega_nav_item():
//   [['name' => ..., 'items' => [['href', 'title', 'desc'], ...]], ...]
$nav_mega_groups = static function (string $loader, string $url_prefix) use ($pdo): array {
    if (!$pdo || !function_exists($loader)) {
        return [];
    }
    try {
        $groups = $loader($pdo);
    } catch (\Throwable $e) {
        return [];
    }
    return array_map(static function ($g) use ($url_prefix) {
        return [
            'name' => $g['name'],
            'items' => array_map(static fn($row) => [
                'href' => $url_prefix . $row['slug'],
                'title' => $row['name'],
                'desc' => mb_strimwidth(trim(strip_tags((string) ($row['excerpt'] ?? ''))), 0, 120, '…'),
            ], $g['items']),
        ];
    }, $groups);
};
$service_mega_groups = $nav_mega_groups('get_services_mega_menu', '/services/');
$hire_mega_groups = $nav_mega_groups('get_hire_mega_menu', '/hire/');

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
        'mega' => $service_mega_groups,
        'mega_all' => ['href' => '/services', 'label' => 'View All Services'],
        'mega_aria' => 'Service categories',
    ],
    [
        'label' => 'Resources',
        'href' => '',
        // 'href'  => '#',
        'key' => 'resources',
        'children' => [
            ['label' => 'Blogs', 'href' => '/blog', 'key' => 'blog'],
            ['label' => 'Success Stories', 'href' => '/success-stories', 'key' => 'resources'],
            ['label' => 'Podcasts', 'href' => '/podcast', 'key' => 'resources'],
            // ['label' => 'Terms of Service', 'href' => '/terms-of-service', 'key' => 'terms'],
            // ['label' => 'Refund & Cancellation Policy', 'href' => '/refund-policy', 'key' => 'refund'],
        ],
    ],
    [
        'label' => 'Hire with Us',
        'href' => '',
        'key' => 'hire',
        'children' => $hire_menu_items,
        'mega' => $hire_mega_groups,
        'mega_all' => ['href' => '/hire-ai-engineers', 'label' => 'View All Hiring Options'],
        'mega_aria' => 'Hiring categories',
    ],
    [
        'label' => 'Contact Us',
        'href' => '/contact',
        'key' => 'contact',
    ],
];

/**
 * Render a mega menu <li> (Services, Hire with Us).
 *
 * $item['mega']      — groups: [['name', 'items' => [['href','title','desc'], ...]], ...]
 * $item['mega_all']  — ['href', 'label'] for the "View all" link
 * $item['mega_aria'] — accessible name of the tab list
 *
 * Child order matters: script.js clones this markup into the mobile and
 * sticky menus and its mobile toggle uses $(dropdown-btn).prev('ul'), so the
 * <ul> (mobile list) must stay the LAST child. The .qt-mega panel is the
 * desktop view (styled in css/mega-menu.css, tabs driven by js/mega-menu.js)
 * and is hidden inside .mobile-menu.
 */
function render_mega_nav_item(array $item, string $active): void
{
    $is_active = ($item['key'] ?? '') === $active;
    $classes = ['dropdown', 'has-mega'];
    if ($is_active) {
        array_unshift($classes, 'current');
    }
    $top_href = $item['href'] ? url($item['href']) : false;
    $all_href = url($item['mega_all']['href'] ?? ($item['href'] ?: '/'));
    $all_label = $item['mega_all']['label'] ?? 'View All';
    ?>
    <li class="<?= implode(' ', $classes) ?>">
        <?php if ($top_href): ?>
            <a href="<?= attr($top_href) ?>"><?= e($item['label']) ?></a>
        <?php else: ?>
            <a><?= e($item['label']) ?></a>
        <?php endif; ?>

        <div class="qt-mega">
            <div class="qt-mega__tabs" role="tablist" aria-label="<?= attr($item['mega_aria'] ?? $item['label']) ?>">
                <?php foreach ($item['mega'] as $i => $group): ?>
                    <button type="button" class="qt-mega__tab<?= $i === 0 ? ' is-active' : '' ?>" role="tab"
                        data-mega-tab="<?= (int) $i ?>" aria-selected="<?= $i === 0 ? 'true' : 'false' ?>"
                        tabindex="<?= $i === 0 ? '0' : '-1' ?>">
                        <span><?= e($group['name']) ?></span>
                        <i class="fa fa-angle-right" aria-hidden="true"></i>
                    </button>
                <?php endforeach; ?>
            </div>
            <div class="qt-mega__body">
                <?php foreach ($item['mega'] as $i => $group): ?>
                    <div class="qt-mega__panel<?= $i === 0 ? ' is-active' : '' ?>" role="tabpanel"
                        data-mega-panel="<?= (int) $i ?>">
                        <div class="qt-mega__grid">
                            <?php foreach ($group['items'] as $link): ?>
                                <a class="qt-mega__item" href="<?= attr(url($link['href'])) ?>">
                                    <span class="qt-mega__title"><?= e($link['title']) ?></span>
                                    <?php if ($link['desc'] !== ''): ?>
                                        <span class="qt-mega__desc"><?= e($link['desc']) ?></span>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <!-- <div class="qt-mega__footer">
                    <a href="<?= attr($all_href) ?>"><?= e($all_label) ?> <i class="fa fa-arrow-right" aria-hidden="true"></i></a>
                </div> -->
            </div>
        </div>

        <ul class="qt-mega-mobile">
            <?php foreach ($item['mega'] as $group): ?>
                <li class="dropdown">
                    <a><?= e($group['name']) ?></a>
                    <ul>
                        <?php foreach ($group['items'] as $link): ?>
                            <li><a href="<?= attr(url($link['href'])) ?>"><?= e($link['title']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </li>
            <?php endforeach; ?>
            <li><a href="<?= attr($all_href) ?>"><?= e($all_label) ?></a></li>
        </ul>
    </li>
    <?php
}

/**
 * Render a single nav <li> recursively.
 */
function render_nav_item(array $item, string $active): void
{
    if (!empty($item['mega'])) {
        render_mega_nav_item($item, $active);
        return;
    }

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