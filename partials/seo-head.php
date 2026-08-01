<?php

/**
 * SEO head partial — outputs <title>, meta tags, Open Graph, Twitter Card,
 * canonical, and JSON-LD schema.
 *
 * Reads from $page_seo (set by index.php from DB) with template-level overrides
 * via $page_title, $canonical, $page_description.
 */
$site_defaults = seo_site_defaults();

// Resolve each value with proper fallback chain:
//   template variable -> DB value -> site default -> empty
$_title = first_nonempty(
    $page_title ?? '',
    $page_seo['title'] ?? '',
    $site_defaults['site_name'] ?? ''
);

$_description = first_nonempty(
    $page_description ?? '',
    $page_seo['meta_description'] ?? '',
    $site_defaults['description'] ?? ''
);

$_keywords = first_nonempty(
    $page_keywords ?? '',
    $page_seo['meta_keywords'] ?? ''
);

$_og_image = first_nonempty(
    $page_og_image ?? '',
    $page_seo['og_image'] ?? '',
    $site_defaults['og_image'] ?? ''
);

$_canonical = first_nonempty(
    $canonical ?? '',
    $page_seo['canonical'] ?? ''
);

// Template-level override -> DB value. (Unlike title/description/canonical
// above, schema_json/robots previously had NO template-level override at
// all, so a page with no matching `pages` table row — e.g. every service
// detail page — could never surface its own JSON-LD or robots directive.)
$_schema_json = first_nonempty(
    $page_schema_json ?? '',
    $page_seo['schema_json'] ?? ''
);

$_robots = first_nonempty(
    $page_robots ?? '',
    $page_seo['robots'] ?? ''
);

// Build absolute URL for og:image if a relative path was provided
if ($_og_image !== '' && !preg_match('#^https?://#', $_og_image) && defined('SITE_URL') && SITE_URL) {
    $_og_image = rtrim(SITE_URL, '/') . '/' . ltrim($_og_image, '/');
}
?>
<title><?= e($_title) ?></title>
<?php if ($_description !== ''): ?>
<meta name="description" content="<?= attr($_description) ?>">
<?php endif; ?>
<?php if ($_keywords !== ''): ?>
<meta name="keywords" content="<?= attr($_keywords) ?>">
<?php endif; ?>
<?php if ($_canonical !== ''): ?>
<link rel="canonical" href="<?= attr($_canonical) ?>">
<?php endif; ?>
<?php if ($_robots !== ''): ?>
<meta name="robots" content="<?= attr($_robots) ?>">
<?php endif; ?>

<!-- Open Graph -->
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= attr($site_defaults['site_name']) ?>">
<meta property="og:title" content="<?= attr($_title) ?>">
<?php if ($_description !== ''): ?>
<meta property="og:description" content="<?= attr($_description) ?>">
<?php endif; ?>
<?php if ($_canonical !== ''): ?>
<meta property="og:url" content="<?= attr($_canonical) ?>">
<?php endif; ?>
<?php if ($_og_image !== ''): ?>
<meta property="og:image" content="<?= attr($_og_image) ?>">
<?php endif; ?>

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= attr($_title) ?>">
<?php if ($_description !== ''): ?>
<meta name="twitter:description" content="<?= attr($_description) ?>">
<?php endif; ?>
<?php if ($_og_image !== ''): ?>
<meta name="twitter:image" content="<?= attr($_og_image) ?>">
<?php endif; ?>

<?php
// Per-page JSON-LD schema (admin-controlled)
if ($_schema_json !== '') {
    echo jsonld($_schema_json) . "\n";
}
// Site-wide Organization schema (admin-controlled, from settings)
if (!empty($site_defaults['organization'])) {
    echo jsonld($site_defaults['organization']) . "\n";
}
?>
