<?php declare(strict_types=1);

ini_set('display_errors', 4);  // never show to browser
ini_set('log_errors', 4);
ini_set('error_log', './php_error.log');
error_reporting(E_ALL);

/**
 * Quantal AI — Front Controller
 *
 * Routes every request to the correct page template using file-path-based routing.
 *
 * Routing logic:
 *   /                        -> ../pages/home.php
 *   /about                   -> ../pages/about.php
 *   /services                -> ../pages/services/index.php  (directory index)
 *   /services/ai-consulting  -> ../pages/services/ai-consulting.php
 *   /blog                    -> ../pages/blog/index.php      (DB-driven listing)
 *   /blog/<slug>             -> ../pages/blog/single.php     (DB-driven single post)
 *   anything else            -> ../pages/404.php (HTTP 404)
 *
 * SEO metadata: for each matched path, the router loads a row from the `pages`
 * table (path = matched URL) and exposes it as $page_seo to header.php and the
 * template. Falls back to defaults defined in the template if no row exists.
 */

// ---------------------------------------------------------------------------
// Paths and bootstrap
// ---------------------------------------------------------------------------
define('ROOT_DIR', __DIR__);
define('PAGES_DIR', ROOT_DIR . '/pages');
define('PARTIALS_DIR', ROOT_DIR . '/partials');
define('CORE_DIR', ROOT_DIR . '/core');
define('CONFIG_DIR', ROOT_DIR . '/config');

// Load config (fail gracefully if missing — site still serves static pages)
if (file_exists(CONFIG_DIR . '/config.php')) {
    require CONFIG_DIR . '/config.php';
} else {
    // Minimal defaults so the site renders even before DB setup
    define('DB_HOST', '');
    define('DB_NAME', '');
    define('DB_USER', '');
    define('DB_PASS', '');
    define('SITE_URL', '');
    define('SITE_NAME', 'Quantal AI');
    define('SITE_DEFAULT_DESCRIPTION', '');
    define('SITE_DEFAULT_OG_IMAGE', '');
}

require CORE_DIR . '/helpers.php';
require CORE_DIR . '/db.php';
require CORE_DIR . '/content-helpers.php';
require CORE_DIR . '/auth.php';
require CORE_DIR . '/csrf.php';
require CORE_DIR . '/mailer.php';
require CORE_DIR . '/seo.php';
require CORE_DIR . '/router.php';
require CORE_DIR . '/sitemap.php';

// Start session before any output so csrf_field() works in footer.php
// (footer is rendered after header has already sent output — session_start()
// cannot be called then, so we start it here while headers are still unsent).
auth_session_start();

// ---------------------------------------------------------------------------
// Resolve the request
// ---------------------------------------------------------------------------
$request_path = router_normalize_path($_SERVER['REQUEST_URI'] ?? '/');

// /sitemap.xml is served directly here — XML output, no HTML header/footer.
if ($request_path === '/sitemap.xml') {
    sitemap_serve();
    exit;
}

$resolved = router_resolve($request_path);

// Legacy URL redirects (e.g. old /hire-ai-engineers/{slug} -> /hire/{slug})
// come back as a 'redirect' key instead of a template — no other route
// returns this shape, so this is the only place it needs handling.
if (isset($resolved['redirect'])) {
    $qs = $_SERVER['QUERY_STRING'] ?? '';
    $target = url($resolved['redirect']) . ($qs !== '' ? '?' . $qs : '');
    header('Location: ' . $target, true, $resolved['status'] ?? 301);
    exit;
}

// ---------------------------------------------------------------------------
// Build the SEO context
// ---------------------------------------------------------------------------
$page_seo = seo_load_for_path($resolved['canonical_path']);

// Hint variables — null so the template's `??` defaults fire. The template runs
// first (via output buffering below) and can set $page_title / $active_page /
// etc., then header.php picks them up.
$page_title = null;
$active_page = $resolved['active_page'] ?? '';
$body_class = '';
$canonical = $page_seo['canonical'] ?? (defined('SITE_URL') && SITE_URL ? rtrim(SITE_URL, '/') . $resolved['canonical_path'] : '');

// ---------------------------------------------------------------------------
// Render
//
// We capture the template's output into a buffer FIRST so the template can set
// $page_title etc. before header.php runs. Then we render header, output the
// buffered content, and render footer.
// ---------------------------------------------------------------------------
if ($resolved['status'] === 404) {
    http_response_code(404);
}

ob_start();
require $resolved['template'];
$page_body = ob_get_clean();

require PARTIALS_DIR . '/header.php';
echo $page_body;
require PARTIALS_DIR . '/footer.php';
