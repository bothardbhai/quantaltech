<?php
/**
 * Router — resolves a request URI to a template file under /pages.
 *
 * The cardinal rule: the file path under /pages IS the URL. The admin only
 * ever attaches SEO metadata to file paths it discovers on disk.
 *
 * Aliases:
 *   /                  -> /pages/home.php
 *
 * Directory index resolution:
 *   /services          -> /pages/services/index.php  (if exists)
 *
 * Blog routing (handled here, but templates live at /pages/blog/index.php and
 * /pages/blog/single.php — the latter sets a $slug variable from the URL).
 */

declare(strict_types=1);

/**
 * Normalize a request URI into a clean path:
 *   - drops the query string
 *   - decodes percent-encoding
 *   - strips trailing slash (except for "/")
 *   - rejects ".." traversal
 *   - returns paths starting with "/"
 */
function router_normalize_path(string $uri): string
{
    // Drop query string
    $path = parse_url($uri, PHP_URL_PATH) ?? '/';
    $path = rawurldecode($path);

    // Remove the configured base path when the app is served from a subdirectory.
    // Example: BASE_URL = http://localhost/quantal__php -> request /quantal__php/about
    // becomes /about for routing.
    if (defined('BASE_URL') && BASE_URL !== '') {
        $base_path = parse_url(BASE_URL, PHP_URL_PATH) ?: '';
        if ($base_path !== '' && str_starts_with($path, $base_path)) {
            $path = substr($path, strlen($base_path));
            if ($path === '') {
                $path = '/';
            }
        }
    }

    // Reject traversal attempts
    if (str_contains($path, '..')) {
        return '/__invalid__';
    }

    // Collapse repeated slashes
    $path = preg_replace('#/+#', '/', $path);

    // Strip trailing slash unless root
    if ($path !== '/' && str_ends_with($path, '/')) {
        $path = rtrim($path, '/');
    }

    return $path;
}

/**
 * Resolve a normalized path to a template file.
 *
 * @return array{
 *   template: string,        // absolute path to the template file to include
 *   canonical_path: string,  // the URL path this resolved to (for SEO lookups)
 *   active_page: string,     // hint for the nav menu's active state
 *   status: int              // 200 or 404
 * }
 */
function router_resolve(string $path): array
{
    // Root alias
    if ($path === '/') {
        $template = PAGES_DIR . '/home.php';
        if (is_file($template)) {
            return [
                'template'       => $template,
                'canonical_path' => '/',
                'active_page'    => 'home',
                'status'         => 200,
            ];
        }
        return router_404();
    }

    // Blog routes — handled by dedicated templates
    if ($path === '/blog') {
        $template = PAGES_DIR . '/blog/index.php';
        if (is_file($template)) {
            return [
                'template'       => $template,
                'canonical_path' => '/blog',
                'active_page'    => 'blog',
                'status'         => 200,
            ];
        }
    }
    if (str_starts_with($path, '/blog/')) {
        $slug = substr($path, strlen('/blog/'));
        $slug = trim($slug, '/');
        if ($slug !== '' && preg_match('/^[a-z0-9\-]+$/', $slug)) {
            $template = PAGES_DIR . '/blog/single.php';
            if (is_file($template)) {
                // Make slug available to the template
                $GLOBALS['blog_slug'] = $slug;
                return [
                    'template'       => $template,
                    'canonical_path' => '/blog/' . $slug,
                    'active_page'    => 'blog',
                    'status'         => 200,
                ];
            }
        }
    }

    // Success Stories routes
    if ($path === '/success-stories') {
        $template = PAGES_DIR . '/success-stories/index.php';
        if (is_file($template)) {
            return [
                'template'       => $template,
                'canonical_path' => '/success-stories',
                'active_page'    => 'resources',
                'status'         => 200,
            ];
        }
    }
    if (str_starts_with($path, '/success-stories/')) {
        $slug = substr($path, strlen('/success-stories/'));
        $slug = trim($slug, '/');
        if ($slug !== '' && preg_match('/^[a-z0-9\-]+$/', $slug)) {
            $template = PAGES_DIR . '/success-stories/single.php';
            if (is_file($template)) {
                // Make slug available to the template
                $GLOBALS['success_story_slug'] = $slug;
                return [
                    'template'       => $template,
                    'canonical_path' => '/success-stories/' . $slug,
                    'active_page'    => 'resources',
                    'status'         => 200,
                ];
            }
        }
    }

    // Webinar routes
    if ($path === '/webinar') {
        $template = PAGES_DIR . '/webinar/index.php';
        if (is_file($template)) {
            return [
                'template'       => $template,
                'canonical_path' => '/webinar',
                'active_page'    => 'resources',
                'status'         => 200,
            ];
        }
    }

    // File-path routing for everything else
    $clean = ltrim($path, '/');

    // Validate: only letters, numbers, dashes, underscores, slashes
    if (!preg_match('#^[a-z0-9][a-z0-9\-_/]*$#i', $clean)) {
        return router_404();
    }

    // Reject any path segment that starts with `_` — those are private templates
    // (_legal.php, _subservice.php, etc.) included by other pages, not URL-accessible.
    foreach (explode('/', $clean) as $segment) {
        if ($segment !== '' && $segment[0] === '_') {
            return router_404();
        }
    }

    // Try /pages/<clean>.php first, then /pages/<clean>/index.php
    $candidates = [
        PAGES_DIR . '/' . $clean . '.php',
        PAGES_DIR . '/' . $clean . '/index.php',
    ];

    foreach ($candidates as $template) {
        if (is_file($template) && router_path_within(PAGES_DIR, $template)) {
            return [
                'template'       => $template,
                'canonical_path' => $path,
                'active_page'    => router_active_from_path($path),
                'status'         => 200,
            ];
        }
    }

    return router_404();
}

/**
 * 404 handler.
 */
function router_404(): array
{
    return [
        'template'       => PAGES_DIR . '/404.php',
        'canonical_path' => '',
        'active_page'    => '',
        'status'         => 404,
    ];
}

/**
 * Defense-in-depth: ensure the resolved template is actually inside PAGES_DIR
 * after path resolution. Catches any edge case the regex missed.
 */
function router_path_within(string $base, string $target): bool
{
    $real_base   = realpath($base);
    $real_target = realpath($target);
    if ($real_base === false || $real_target === false) {
        return false;
    }
    return str_starts_with($real_target, $real_base . DIRECTORY_SEPARATOR)
        || $real_target === $real_base;
}

/**
 * Derive the active-page hint for the nav menu from the URL path.
 *
 *   /about                      -> 'about'
 *   /services                   -> 'services'
 *   /services/ai-consulting     -> 'services'
 *   /blog                       -> 'blog'
 *   /blog/some-post             -> 'blog'
 */
function router_active_from_path(string $path): string
{
    $trimmed = trim($path, '/');
    if ($trimmed === '') {
        return 'home';
    }
    $first = explode('/', $trimmed)[0];
    return strtolower($first);
}

/**
 * Discover all page templates on disk for the admin "page picker".
 * Returns an array of canonical URL paths.
 *
 * Used by Stage 2 admin. Listed here so all routing logic lives in one file.
 */
function router_discover_pages(): array
{
    $found = [];
    $iter  = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(PAGES_DIR, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($iter as $file) {
        if (!$file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }
        $rel = substr($file->getPathname(), strlen(PAGES_DIR));
        $rel = str_replace(DIRECTORY_SEPARATOR, '/', $rel);
        $rel = preg_replace('/\.php$/', '', $rel);

        // Skip per-slug "single" templates — they map to many URLs, not one, so
        // they can't carry a single `pages` row. Listing templates (blog/index,
        // success-stories/index) DO map to one static URL each and are kept.
        if ($rel === '/blog/single' ||
            $rel === '/success-stories/single' ||
            $rel === '/webinar/index') {
            continue;
        }
        // Skip private partials (underscore prefix), alt/ variants, and reference templates
        $basename = basename($rel);
        if (str_starts_with($basename, '_')) {
            continue;
        }
        if (str_starts_with($rel, '/alt/')) {
            continue;
        }

        if ($rel === '/home') {
            $found[] = '/';
        } elseif (str_ends_with($rel, '/index')) {
            $found[] = substr($rel, 0, -strlen('/index'));
        } else {
            $found[] = $rel;
        }
    }
    sort($found);
    return $found;
}
