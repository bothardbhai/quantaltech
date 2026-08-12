<?php

/**
 * Service Master bridge — loads a `services` row by slug and maps it onto
 * the exact variable contract `_subservice.php` expects (see that file's
 * doc block), then includes it. Reached only when no literal
 * pages/services/{slug}.php file exists for the URL (core/router.php checks
 * that first) and a published row matches the slug.
 *
 * This keeps _subservice.php a pure presentation template that doesn't care
 * whether its data came from a hand-written PHP file (like the old
 * ai-engineering.php) or the database — both populate the same variables.
 */

$slug = $GLOBALS['service_slug'] ?? '';
$pdo = db();
$service = $slug !== '' && $pdo ? get_service($pdo, $slug) : null;

if (!$service || $service['status'] !== 'published') {
    http_response_code(404);
    require PAGES_DIR . '/404.php';
    return;
}

/**
 * Decode a JSON column into an array, tolerating null/invalid JSON.
 */
function svc_json(?string $raw): array
{
    if (!$raw) {
        return [];
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

$current_slug = $service['slug'];
$page_label   = $service['page_label'] !== '' ? $service['page_label'] : $service['title'];
$crumb        = $service['crumb'] !== '' ? $service['crumb'] : $service['name'];
$active_page  = 'services';

// --- Hero ---
$service_tag        = $service['hero_tag'];
$service_title_html = $service['hero_title_html'] ?? '';
$service_desc       = $service['hero_desc'] ?? '';

// --- Powered-by platform strip ---
$platform_title = $service['platform_title'];
$platforms      = svc_json($service['platforms_json']);

// --- Impact stats ---
$impact_stats = svc_json($service['impact_stats_json']);

// --- Service overview ---
$overview_sub        = $service['overview_sub'];
$overview_title_html = $service['overview_title_html'] ?? '';
$overview_paragraphs = svc_json($service['overview_paragraphs_json']);
$overview_btn_text   = $service['overview_btn_text'];
// features_json holds overview feature cards; legacy rows may still hold a
// flat string array from before this column was repurposed — normalize both
// shapes to ['icon','title','desc'].
$overview_features = array_map(
    static function ($item) {
        if (is_array($item)) {
            return [
                'icon' => $item['icon'] ?? '',
                'title' => $item['title'] ?? '',
                'desc' => $item['description'] ?? ($item['desc'] ?? ''),
            ];
        }
        return ['icon' => '', 'title' => (string) $item, 'desc' => ''];
    },
    svc_json($service['features_json'])
);

// --- Benefit cards ---
$benefits_sub        = $service['benefits_sub'];
$benefits_title_html = $service['benefits_title_html'] ?? '';
$benefits_text       = $service['benefits_text'] ?? '';
$benefit_cards       = svc_json($service['benefit_cards_json']);

// --- Services grid ---
$grid_sub        = $service['grid_sub'];
$grid_title_html = $service['grid_title_html'] ?? '';
$grid_text       = $service['grid_text'] ?? '';
$grid_services   = svc_json($service['grid_services_json']);

// --- What you get ---
$whatyouget_sub        = $service['whatyouget_sub'];
$whatyouget_title_html = $service['whatyouget_title_html'] ?? '';
$whatyouget_text       = $service['whatyouget_text'] ?? '';
$whatyouget_cards      = svc_json($service['whatyouget_cards_json']);

// --- Industries ---
$industries_sub        = $service['industries_sub'];
$industries_title_html = $service['industries_title_html'] ?? '';
$industries_text       = $service['industries_text'] ?? '';
$industries            = svc_json($service['industries_json']);

// --- Framework ---
$framework_sub        = $service['framework_sub'];
$framework_title_html = $service['framework_title_html'] ?? '';
$framework_text       = $service['framework_text'] ?? '';
$framework_steps      = svc_json($service['framework_steps_json']);

// --- Why Choose Us ---
$why_sub        = $service['why_sub'];
$why_title_html = $service['why_title_html'] ?? '';
$why_text       = $service['why_text'] ?? '';
$why_cards      = svc_json($service['why_cards_json']);

// --- Engagement models ---
$engagement_sub        = $service['engagement_sub'];
$engagement_title_html = $service['engagement_title_html'] ?? '';
$engagement_text       = $service['engagement_text'] ?? '';
$engagement_models     = svc_json($service['engagement_models_json']);

// --- Process timeline ---
$process_sub        = $service['process_sub'];
$process_title_html = $service['process_title_html'] ?? '';
$process_text       = $service['process_text'] ?? '';
$process_steps      = svc_json($service['process_steps_json']);

// --- Mid CTA ---
$cta_tag        = $service['cta_tag'];
$cta_title_html = $service['cta_title_html'] ?? '';
$cta_text       = $service['cta_text'] ?? '';

// --- Case studies / Success Stories — no longer manually picked per
// service; always the 4 most recently published Success Stories (see
// admin/success-stories.php, the single source of truth for these). ---
$cs_sub        = $service['cs_sub'];
$cs_title_html = $service['cs_title_html'] ?? '';
$cs_text       = $service['cs_text'] ?? '';
$case_studies  = [];
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

// --- Tech stack ---
$tech_sub        = $service['tech_sub'];
$tech_title_html = $service['tech_title_html'] ?? '';
$tech_text       = $service['tech_text'] ?? '';
$tech_categories = svc_json($service['tech_categories_json']);

// --- Security & compliance ---
$security_sub        = $service['security_sub'];
$security_title_html = $service['security_title_html'] ?? '';
$security_text       = $service['security_text'] ?? '';
$security_cards      = svc_json($service['security_cards_json']);

// --- Related services (stored as IDs; resolve to label/slug here) ---
$related_sub          = $service['related_sub'];
$related_title_html   = $service['related_title_html'] ?? '';
$related_text         = $service['related_text'] ?? '';
$related_group_title  = $service['related_group_title'];
$related_items = $pdo
    ? array_map(
        static fn(array $s) => [
            'label' => $s['name'],
            'title' => $s['title'],
            'slug' => $s['slug'],
            'excerpt' => $s['excerpt'],
            'featured_image' => $s['featured_image'],
        ],
        get_services_by_ids($pdo, svc_json($service['related_service_ids_json']))
    )
    : [];

// --- Knowledge hub (stored as post IDs; resolve to image/title/link here) ---
$blog_sub        = $service['blog_sub'];
$blog_title_html = $service['blog_title_html'] ?? '';
$blog_text       = $service['blog_text'] ?? '';
$blog_posts = $pdo
    ? array_map(
        static fn(array $p) => [
            'image'    => media_url($p['featured_image']),
            'category' => $p['category_name'] ?? '',
            'title'    => $p['title'],
            'desc'     => $p['excerpt'],
            'link'     => url('/blog/' . $p['slug']),
        ],
        get_blog_posts_by_ids($pdo, svc_json($service['blog_post_ids_json']))
    )
    : [];

// --- FAQ ---
$faq_intro = $service['faq_intro'] ?? '';
$faqs      = svc_json($service['faqs_json']);

// --- Final CTA (bottom-of-page band) ---
$final_cta_title_html = $service['final_cta_title_html'] ?? '';
$final_cta_desc       = $service['final_cta_desc'] ?? '';
$final_cta_btn_text   = $service['final_cta_btn_text'];
$final_cta_btn_url    = $service['final_cta_btn_url'];

// --- Sidebar service nav (all published services, not just this one) ---
$service_links = [];
if ($pdo) {
    $stmt = $pdo->prepare(
        "SELECT slug, name FROM services WHERE status = 'published' ORDER BY service_number ASC, name ASC"
    );
    $stmt->execute();
    foreach ($stmt->fetchAll() ?: [] as $row) {
        $service_links[$row['slug']] = $row['name'];
    }
}

// --- SEO (template-level overrides read by partials/seo-head.php) ---
$page_title       = $service['meta_title'] !== '' ? $service['meta_title'] : $service['title'] . ' - ' . SITE_NAME;
$page_description = $service['meta_description'] !== '' ? $service['meta_description'] : $service['excerpt'];
$page_keywords    = $service['meta_keywords'];
$page_og_image    = $service['og_image'] !== '' ? $service['og_image'] : $service['featured_image'];
$canonical        = $service['canonical'] !== '' ? $service['canonical'] : (defined('SITE_URL') ? rtrim(SITE_URL, '/') : '') . '/services/' . $service['slug'];
$page_schema_json = $service['schema_json'];
$page_robots      = $service['robots'];

require __DIR__ . '/_subservice.php';
