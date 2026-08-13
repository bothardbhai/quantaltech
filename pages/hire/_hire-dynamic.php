<?php

/**
 * Hire Master bridge — loads a `hire_pages` row by slug and maps it onto the
 * exact variable contract `_subhire.php` expects (see that file's doc
 * block), then includes it. Reached only when no literal
 * pages/hire/{slug}.php file exists for the URL (core/router.php checks
 * that first) and a published row matches the slug. Detail pages live at
 * /hire/{slug}; the hub page lives separately at /hire-ai-engineers.
 *
 * Mirrors pages/services/_service-dynamic.php 1:1 — same shape, same
 * section list, just resolved from `hire_pages` instead of `services`.
 */

$slug = $GLOBALS['hire_slug'] ?? '';
$pdo = db();
$hire_page = $slug !== '' && $pdo ? get_hire_page($pdo, $slug) : null;

if (!$hire_page || $hire_page['status'] !== 'published') {
    http_response_code(404);
    require PAGES_DIR . '/404.php';
    return;
}

/**
 * Decode a JSON column into an array, tolerating null/invalid JSON.
 */
function hire_json(?string $raw): array
{
    if (!$raw) {
        return [];
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

$current_slug = $hire_page['slug'];
$page_label   = $hire_page['page_label'] !== '' ? $hire_page['page_label'] : $hire_page['title'];
$crumb        = $hire_page['crumb'] !== '' ? $hire_page['crumb'] : $hire_page['name'];
$active_page  = 'hire';

// --- Hero ---
$hero_tag        = $hire_page['hero_tag'];
$hero_title_html = $hire_page['hero_title_html'] ?? '';
$hero_desc       = $hire_page['hero_desc'] ?? '';
$hero_features   = hire_json($hire_page['hero_features_json']);

// --- Impact stats ---
$impact_stats = hire_json($hire_page['impact_stats_json']);

// --- Expertise of Our Engineers ---
$expertise_sub        = $hire_page['expertise_sub'];
$expertise_title_html = $hire_page['expertise_title_html'] ?? '';
$expertise_text       = $hire_page['expertise_text'] ?? '';
$expertise_cards      = hire_json($hire_page['expertise_cards_json']);

// --- What Our Engineers Build ---
$build_sub        = $hire_page['build_sub'];
$build_title_html = $hire_page['build_title_html'] ?? '';
$build_text       = $hire_page['build_text'] ?? '';
$build_cards      = hire_json($hire_page['build_cards_json']);

// --- Engagement models ---
$engagement_sub        = $hire_page['engagement_sub'];
$engagement_title_html = $hire_page['engagement_title_html'] ?? '';
$engagement_text       = $hire_page['engagement_text'] ?? '';
$engagement_models     = hire_json($hire_page['engagement_models_json']);

// --- Why Hire ---
$why_sub        = $hire_page['why_sub'];
$why_title_html = $hire_page['why_title_html'] ?? '';
$why_text       = $hire_page['why_text'] ?? '';
$why_cards      = hire_json($hire_page['why_cards_json']);

// --- Industries ---
$industries_sub        = $hire_page['industries_sub'];
$industries_title_html = $hire_page['industries_title_html'] ?? '';
$industries_text       = $hire_page['industries_text'] ?? '';
$industries            = hire_json($hire_page['industries_json']);

// --- Mid CTA ---
$cta_tag        = $hire_page['cta_tag'];
$cta_title_html = $hire_page['cta_title_html'] ?? '';
$cta_text       = $hire_page['cta_text'] ?? '';

// --- Case studies ---
$cs_sub        = $hire_page['cs_sub'];
$cs_title_html = $hire_page['cs_title_html'] ?? '';
$cs_text       = $hire_page['cs_text'] ?? '';

// Case studies / Success Stories — no longer manually picked per hire page;
// always the 4 most recently published Success Stories (see
// admin/success-stories.php, the single source of truth for these).
$case_studies = [];
if ($pdo) {
    $cs_category_map = [];
    foreach (get_success_story_categories($pdo, []) as $cs_cat) {
        $cs_category_map[(int) $cs_cat['id']] = $cs_cat['name'];
    }
    foreach (array_slice(get_success_stories($pdo, ['status' => 'published', 'order' => 'created_at DESC, id DESC']), 0, 4) as $cs_story) {
        $case_studies[] = [
            'title'    => $cs_story['title'],
            'category' => $cs_category_map[(int) ($cs_story['category_id'] ?? 0)] ?? '',
            'image'    => $cs_story['featured_image'],
            'url'      => url('/success-stories/' . $cs_story['slug']),
        ];
    }
}

// --- Related hire pages + related services (stored as IDs; resolve here) ---
$related_sub        = $hire_page['related_sub'];
$related_title_html = $hire_page['related_title_html'] ?? '';
$related_text       = $hire_page['related_text'] ?? '';
$related_items = $pdo
    ? array_merge(
        array_map(
            static fn(array $h) => [
                'label' => $h['role_label'] !== '' ? $h['role_label'] : $h['name'],
                'title' => $h['title'],
                'slug' => 'hire/' . $h['slug'],
                'excerpt' => $h['excerpt'],
                'featured_image' => $h['featured_image'],
                'is_hire' => true,
            ],
            get_hire_pages_by_ids($pdo, hire_json($hire_page['related_hire_ids_json']))
        ),
        array_map(
            static fn(array $s) => [
                'label' => $s['name'],
                'title' => $s['title'],
                'slug' => 'services/' . $s['slug'],
                'excerpt' => $s['excerpt'],
                'featured_image' => $s['featured_image'],
                'is_hire' => false,
            ],
            get_services_by_ids($pdo, hire_json($hire_page['related_service_ids_json']))
        )
    )
    : [];

// --- Knowledge hub (stored as post IDs; resolve to image/title/link here) ---
$blog_sub        = $hire_page['blog_sub'];
$blog_title_html = $hire_page['blog_title_html'] ?? '';
$blog_text       = $hire_page['blog_text'] ?? '';
$blog_posts = $pdo
    ? array_map(
        static fn(array $p) => [
            'image'    => media_url($p['featured_image']),
            'category' => $p['category_name'] ?? '',
            'title'    => $p['title'],
            'desc'     => $p['excerpt'],
            'link'     => url('/blog/' . $p['slug']),
        ],
        get_blog_posts_by_ids($pdo, hire_json($hire_page['blog_post_ids_json']))
    )
    : [];

// --- FAQ ---
$faq_intro = $hire_page['faq_intro'] ?? '';
$faqs      = hire_json($hire_page['faqs_json']);

// --- Final CTA (bottom-of-page band) ---
$final_cta_title_html = $hire_page['final_cta_title_html'] ?? '';
$final_cta_desc       = $hire_page['final_cta_desc'] ?? '';
$final_cta_btn_text   = $hire_page['final_cta_btn_text'];
$final_cta_btn_url    = $hire_page['final_cta_btn_url'];

// --- Sidebar hire-role nav (all published hire pages, not just this one) ---
$hire_links = [];
if ($pdo) {
    $stmt = $pdo->prepare(
        "SELECT slug, name FROM hire_pages WHERE status = 'published' ORDER BY sort_order ASC, name ASC"
    );
    $stmt->execute();
    foreach ($stmt->fetchAll() ?: [] as $row) {
        $hire_links[$row['slug']] = $row['name'];
    }
}

// --- SEO (template-level overrides read by partials/seo-head.php) ---
$page_title       = $hire_page['meta_title'] !== '' ? $hire_page['meta_title'] : $hire_page['title'] . ' - ' . SITE_NAME;
$page_description = $hire_page['meta_description'] !== '' ? $hire_page['meta_description'] : $hire_page['excerpt'];
$page_keywords    = $hire_page['meta_keywords'];
$page_og_image    = $hire_page['og_image'] !== '' ? $hire_page['og_image'] : $hire_page['featured_image'];
$canonical        = $hire_page['canonical'] !== '' ? $hire_page['canonical'] : (defined('SITE_URL') ? rtrim(SITE_URL, '/') : '') . '/hire/' . $hire_page['slug'];
$page_schema_json = $hire_page['schema_json'];
$page_robots      = $hire_page['robots'];

require __DIR__ . '/_subhire.php';
