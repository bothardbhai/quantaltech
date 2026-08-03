<?php
/**
 * Core helpers — small functions used throughout templates and core.
 */

declare(strict_types=1);

/**
 * Escape for HTML output. Use everywhere user data or DB content lands inside HTML.
 */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Escape for use inside an HTML attribute (same as e() but explicit).
 */
function attr(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Build an absolute-from-root URL to an asset under /public.
 *
 * asset('css/style.css')   -> '/assets/css/style.css'
 * asset('images/logo.png') -> '/assets/images/logo.png'
 */
function asset(string $path): string
{
    return rtrim(BASE_URL, '/') . '/assets/' . ltrim($path, '/');
}

/**
 * Build a site URL.
 *
 * url('/about') -> 'https://www.quantaltech.ai/about' if SITE_URL set, otherwise '/about'
 */
function url(string $path = ''): string
{
    $base = defined('SITE_URL') && SITE_URL ? rtrim(SITE_URL, '/') : '';
    return $base . '/' . ltrim($path, '/');
}

/**
 * Output a JSON-LD script block from an array or already-encoded JSON string.
 */
function jsonld(mixed $data): string
{
    if (is_string($data)) {
        // Trust the admin to have provided valid JSON; still validate
        json_decode($data);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ''; // Invalid JSON — silently drop rather than break the page
        }
        return '<script type="application/ld+json">' . $data . '</script>';
    }
    if (is_array($data) && !empty($data)) {
        return '<script type="application/ld+json">'
            . json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            . '</script>';
    }
    return '';
}

/**
 * Resolve a stored upload/media path (from the `media` table, or
 * posts.featured_image / og_image, etc.) into a URL the browser can load.
 *
 * Stored paths are root-relative (e.g. "/uploads/media/2026-07/file.jpg"),
 * written on the assumption that the app is served from the domain root. When
 * BASE_URL includes a subdirectory (local dev, or a subdirectory deploy), a
 * root-relative <img src> resolves against the domain root instead and 404s —
 * so route it through BASE_URL the same way asset()/url() already do.
 * Already-absolute URLs (http/https) and empty paths pass through unchanged.
 */
function media_url(?string $path): string
{
    $path = (string) $path;
    if ($path === '' || preg_match('#^https?://#i', $path)) {
        return $path;
    }
    $base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
    return $base . '/' . ltrim($path, '/');
}

/**
 * Truthy-coalesce: return the first non-empty string.
 */
function first_nonempty(string ...$candidates): string
{
    foreach ($candidates as $c) {
        if ($c !== '') {
            return $c;
        }
    }
    return '';
}

/**
 * Truncate a description/excerpt for card previews. Strips any HTML tags,
 * decodes entities, collapses/trims whitespace, then cuts to $limit
 * characters — appending "..." only when the source was actually longer.
 * Prefers cutting at the last whole word rather than mid-word, as long as
 * that doesn't throw away more than ~40% of the allowed length (falls back
 * to a hard cut for a single very long word so the limit is still respected).
 * Truncates the underlying string itself (not just the rendered HTML), so
 * the full text never sits hidden in the page source; still run the result
 * through e() at the call site same as any other plain-text field.
 */
function truncate_text(?string $text, int $limit = 85): string
{
    $text = trim(html_entity_decode(strip_tags((string) $text), ENT_QUOTES, 'UTF-8'));
    $text = preg_replace('/\s+/', ' ', $text) ?? $text;
    if (mb_strlen($text) <= $limit) {
        return $text;
    }
    $truncated = mb_substr($text, 0, $limit);
    $last_space = mb_strrpos($truncated, ' ');
    if ($last_space !== false && $last_space >= $limit * 0.6) {
        $truncated = mb_substr($truncated, 0, $last_space);
    }
    return rtrim($truncated) . '...';
}

/**
 * Sanitize admin-authored HTML meant to be rendered raw (not escaped).
 * Strips <script>/<style> blocks, on* event handler attributes, and
 * javascript: URLs. Shared by any admin field that stores trusted-but-should-
 * still-be-defanged HTML (blog body, service section headings, etc.).
 */
function sanitize_html_fragment(string $html): string
{
    // Strip <script> blocks entirely
    $html = preg_replace('#<script\b[^>]*>.*?</script>#is', '', $html) ?? '';
    // Strip <style> blocks
    $html = preg_replace('#<style\b[^>]*>.*?</style>#is', '', $html) ?? '';
    // Remove on* attributes (event handlers)
    $html = preg_replace('#\s+on[a-z]+\s*=\s*"[^"]*"#i', '', $html) ?? '';
    $html = preg_replace("#\s+on[a-z]+\s*=\s*'[^']*'#i", '', $html) ?? '';
    // Block javascript: in href/src
    $html = preg_replace('#(href|src)\s*=\s*"javascript:[^"]*"#i', '$1="#"', $html) ?? '';
    return $html;
}

/**
 * CKEditor's classic build always wraps its root content in a block element
 * (usually a single outer <p>). Fields that get inserted inline (e.g. inside
 * an existing <h2>) need that wrapper removed, or the browser ends up with
 * an invalid block-inside-inline nesting. Only strips the wrapper when the
 * ENTIRE string is one top-level <p>...</p> — leaves multi-paragraph content
 * untouched.
 */
function strip_wrapping_p(string $html): string
{
    $trimmed = trim($html);
    if (preg_match('#^<p>(.*)</p>$#is', $trimmed, $m) && !preg_match('#</p>\s*<p>#i', $trimmed)) {
        return trim($m[1]);
    }
    return $html;
}
