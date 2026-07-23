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
