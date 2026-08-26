<?php
/**
 * Sitemap — single source of truth for /sitemap.xml (public) and
 * Admin > Sitemap > Regenerate (manual refresh). Both entry points call
 * sitemap_generate() and nothing else; there is no separate admin-side
 * generator.
 *
 * Design:
 *   - Always generated live from the DB + on-disk pages, on every request.
 *     No cache file / cache table, so it can never go stale — content edits
 *     (publish, unpublish, delete, noindex) show up on the very next load.
 *   - Only lean columns are selected per table (no MEDIUMTEXT/JSON content
 *     fields), so this stays cheap even as content grows.
 *   - Indexability mirrors the existing SEO system exactly: the `pages`
 *     table's `is_published` flag for file-based pages (core/seo.php,
 *     admin/pages.php), and each content table's own `status`/`robots`/
 *     `canonical` columns for Services/Hire/Success Stories (admin/services.php
 *     etc.). No second/parallel SEO system is introduced.
 *
 * Two kinds of URLs, collected separately (sitemap_generate()'s `sources`
 * breakdown keeps them visibly distinct), then merged into one deduplicated
 * map:
 *   - STATIC / CMS pages: every real, resolvable file under /pages (via the
 *     router's own router_discover_pages()/router_resolve()), annotated
 *     with — not replaced by — the `pages` table's SEO metadata when a row
 *     exists for that exact path. A `pages` row is metadata for a page that
 *     already exists on disk; it is never itself an extra URL source (a
 *     `pages` row with no matching on-disk route cannot be a real, live
 *     URL, so it is correctly never sitemapped).
 *   - DYNAMIC detail pages: one row per published record in each content
 *     master (posts, services, hire_pages, success_stories), mapped to that
 *     master's actual frontend route as implemented in core/router.php —
 *     never assumed.
 */

declare(strict_types=1);

/**
 * A handful of on-disk pages exist purely as internal utility endpoints
 * (form handlers, search, 404, thank-you). router_discover_pages() finds
 * them like any other template, but they should never appear in the sitemap
 * unless an admin has explicitly given them a `pages` row with
 * is_published = 1 (i.e. deliberately opted them in via the existing
 * Pages & SEO screen).
 */
const SITEMAP_UTILITY_PATHS = ['/404', '/contact-submit', '/search', '/thank-you', '/newsletter'];

/**
 * Turn a path or already-absolute URL into a final absolute sitemap <loc>.
 */
function sitemap_absolute_url(string $loc_or_path): string
{
    $base = defined('SITE_URL') && SITE_URL ? rtrim(SITE_URL, '/') : (defined('BASE_URL') ? rtrim(BASE_URL, '/') : '');
    $loc = preg_match('#^https?://#i', $loc_or_path) ? $loc_or_path : $base . '/' . ltrim($loc_or_path, '/');
    if ($loc !== $base . '/' && str_ends_with($loc, '/')) {
        $loc = rtrim($loc, '/');
    }
    return $loc;
}

/**
 * STATIC / CMS pages — every on-disk page template that actually resolves
 * to a live 200 response, cross-referenced against the `pages` table
 * (Pages & SEO) for is_published / canonical / updated_at. This is the
 * ONLY place static pages come from; the `pages` table never adds URLs of
 * its own beyond what's really routable, since a metadata row for a
 * path with no matching template can't be a real page.
 *
 * @return array{items: array<string, ?string>, on_disk: int, pages_table_total: int, pages_table_published: int}
 */
function sitemap_source_static_pages(): array
{
    $pdo = db();

    $seo_rows = [];
    $pages_table_total = 0;
    $pages_table_published = 0;
    if ($pdo) {
        try {
            $stmt = $pdo->query('SELECT path, is_published, canonical, updated_at FROM pages');
            foreach ($stmt as $row) {
                $seo_rows[$row['path']] = $row;
                $pages_table_total++;
                if ($row['is_published']) {
                    $pages_table_published++;
                }
            }
        } catch (PDOException $e) {
            // `pages` table not migrated yet — every static page falls back
            // to "no metadata row" handling below.
        }
    }

    $on_disk = router_discover_pages();
    $items = [];

    foreach ($on_disk as $path) {
        // Confirm the path is genuinely live under the site's real routing
        // rules (catches stray/renamed files that no longer resolve, e.g. a
        // filename router_resolve()'s own validation would 404 on).
        if (router_resolve($path)['status'] !== 200) {
            continue;
        }

        $row = $seo_rows[$path] ?? null;

        if ($row !== null) {
            if (!$row['is_published']) {
                continue; // explicitly unpublished in Pages & SEO
            }
        } elseif (in_array($path, SITEMAP_UTILITY_PATHS, true)) {
            continue; // internal utility page, never indexed unless opted in above
        }

        $loc = ($row['canonical'] ?? '') !== '' ? $row['canonical'] : $path;
        $items[sitemap_absolute_url($loc)] = $row['updated_at'] ?? null;
    }

    return [
        'items'                  => $items,
        'on_disk'                => count($on_disk),
        'pages_table_total'      => $pages_table_total,
        'pages_table_published'  => $pages_table_published,
    ];
}

/**
 * Blog — /blog/{slug} for every published post (mirrors
 * pages/blog/single.php's own published + published_at gate). Does NOT
 * touch post_categories/post_tags/post_faqs/categories/tags — those are
 * relationship/taxonomy tables with no public detail URL of their own.
 *
 * @return array<string, ?string> loc => lastmod
 */
function sitemap_source_posts(): array
{
    $pdo = db();
    $items = [];
    if (!$pdo) {
        return $items;
    }
    try {
        $stmt = $pdo->query(
            "SELECT slug, updated_at FROM posts
             WHERE status = 'published' AND (published_at IS NULL OR published_at <= NOW())"
        );
        foreach ($stmt as $r) {
            $items[sitemap_absolute_url('/blog/' . $r['slug'])] = $r['updated_at'];
        }
    } catch (PDOException $e) {
        // posts table missing — skip
    }
    return $items;
}

/**
 * Services (Service Master) — /services/{slug} for every published,
 * non-noindex row (mirrors router_service_exists()'s own gate).
 *
 * @return array<string, ?string> loc => lastmod
 */
function sitemap_source_services(): array
{
    $pdo = db();
    $items = [];
    if (!$pdo) {
        return $items;
    }
    try {
        $stmt = $pdo->query("SELECT slug, robots, canonical, updated_at FROM services WHERE status = 'published'");
        foreach ($stmt as $r) {
            if (stripos((string) $r['robots'], 'noindex') !== false) {
                continue;
            }
            $loc = ($r['canonical'] ?? '') !== '' ? $r['canonical'] : '/services/' . $r['slug'];
            $items[sitemap_absolute_url($loc)] = $r['updated_at'];
        }
    } catch (PDOException $e) {
        // services table missing — skip
    }
    return $items;
}

/**
 * Hire Master — /hire/{slug} for every published, non-noindex row (mirrors
 * router_hire_page_exists()'s own gate).
 *
 * @return array<string, ?string> loc => lastmod
 */
function sitemap_source_hire_pages(): array
{
    $pdo = db();
    $items = [];
    if (!$pdo) {
        return $items;
    }
    try {
        $stmt = $pdo->query("SELECT slug, robots, canonical, updated_at FROM hire_pages WHERE status = 'published'");
        foreach ($stmt as $r) {
            if (stripos((string) $r['robots'], 'noindex') !== false) {
                continue;
            }
            $loc = ($r['canonical'] ?? '') !== '' ? $r['canonical'] : '/hire/' . $r['slug'];
            $items[sitemap_absolute_url($loc)] = $r['updated_at'];
        }
    } catch (PDOException $e) {
        // hire_pages table missing — skip
    }
    return $items;
}

/**
 * Success Stories — /success-stories/{slug} for every published,
 * non-noindex row. The /success-stories hub itself is a static page,
 * handled by sitemap_source_static_pages(). Does NOT touch
 * success_story_categories — a filter taxonomy, not a public detail page.
 *
 * @return array<string, ?string> loc => lastmod
 */
function sitemap_source_success_stories(): array
{
    $pdo = db();
    $items = [];
    if (!$pdo) {
        return $items;
    }
    try {
        $stmt = $pdo->query("SELECT slug, robots, canonical, updated_at FROM success_stories WHERE status = 'published'");
        foreach ($stmt as $r) {
            if (stripos((string) $r['robots'], 'noindex') !== false) {
                continue;
            }
            $loc = ($r['canonical'] ?? '') !== '' ? $r['canonical'] : '/success-stories/' . $r['slug'];
            $items[sitemap_absolute_url($loc)] = $r['updated_at'];
        }
    } catch (PDOException $e) {
        // success_stories table missing — skip
    }
    return $items;
}

/**
 * Podcast — /podcast/{slug} for every published, non-noindex row in the
 * `podcasts` table. The /podcast hub itself is a static page, handled by
 * sitemap_source_static_pages(). (Historically this sourced the `webinars`
 * table before a real /podcast/{slug} detail route existed — see
 * pages/podcast/single.php and core/router.php's Podcast routes block.)
 *
 * @return array<string, ?string> loc => lastmod
 */
function sitemap_source_webinars(): array
{
    $pdo = db();
    $items = [];
    if (!$pdo) {
        return $items;
    }
    try {
        $stmt = $pdo->query("SELECT slug, robots, canonical, updated_at FROM podcasts WHERE status = 'published'");
        foreach ($stmt as $r) {
            if (stripos((string) $r['robots'], 'noindex') !== false) {
                continue;
            }
            $loc = ($r['canonical'] ?? '') !== '' ? $r['canonical'] : '/podcast/' . $r['slug'];
            $items[sitemap_absolute_url($loc)] = $r['updated_at'];
        }
    } catch (PDOException $e) {
        // podcasts table missing — skip
    }
    return $items;
}

/**
 * Build the complete sitemap: collects every source above, merges them
 * into one de-duplicated URL map (keyed by final absolute URL — the same
 * URL from two sources, e.g. a canonical override pointing at another
 * entry, collapses automatically), and reports a per-source diagnostic
 * breakdown so it's possible to see exactly how many URLs each content
 * type contributed. The diagnostics are for Admin/dev visibility only —
 * sitemap_render_xml() never emits them into the public XML.
 *
 * @return array{
 *   urls: array<string, ?string>,
 *   sources: array<string, int>,
 *   raw_total: int,
 *   final_total: int
 * }
 */
function sitemap_generate(): array
{
    $static = sitemap_source_static_pages();

    $dynamic_sources = [
        'blog_posts'      => sitemap_source_posts(),
        'services'        => sitemap_source_services(),
        'hire_pages'      => sitemap_source_hire_pages(),
        'success_stories' => sitemap_source_success_stories(),
        'webinars'        => sitemap_source_webinars(),
    ];

    $urls = $static['items'];
    $raw_total = count($static['items']);

    foreach ($dynamic_sources as $items) {
        $raw_total += count($items);
        $urls += $items; // union; first-seen lastmod wins on a rare loc collision
    }

    ksort($urls);

    $sources = [
        'static_pages_on_disk'       => $static['on_disk'],
        'static_pages_included'      => count($static['items']),
        'pages_table_total'          => $static['pages_table_total'],
        'pages_table_published'      => $static['pages_table_published'],
        'blog_posts'                 => count($dynamic_sources['blog_posts']),
        'services'                   => count($dynamic_sources['services']),
        'hire_pages'                 => count($dynamic_sources['hire_pages']),
        'success_stories'            => count($dynamic_sources['success_stories']),
        'webinars'                   => count($dynamic_sources['webinars']),
    ];

    return [
        'urls'        => $urls,
        'sources'     => $sources,
        'raw_total'   => $raw_total,
        'final_total' => count($urls),
    ];
}

/**
 * Render a URL map (loc => lastmod) into a sitemap XML document string.
 * Takes only the plain URL map — never the diagnostics — so there is no
 * path by which internal counts could leak into the public sitemap.xml.
 *
 * @param array<string, ?string> $urls loc => lastmod (DB datetime or null)
 */
function sitemap_render_xml(array $urls): string
{
    $xml = new XMLWriter();
    $xml->openMemory();
    $xml->setIndent(true);
    $xml->setIndentString('    ');
    $xml->startDocument('1.0', 'UTF-8');
    $xml->startElement('urlset');
    $xml->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

    foreach ($urls as $loc => $lastmod) {
        $xml->startElement('url');
        $xml->writeElement('loc', $loc);
        $ts = $lastmod ? strtotime((string) $lastmod) : false;
        if ($ts !== false) {
            $xml->writeElement('lastmod', date('c', $ts));
        }
        $xml->endElement();
    }

    $xml->endElement();
    $xml->endDocument();
    return $xml->outputMemory();
}

/**
 * Serve /sitemap.xml: sets the XML content type, generates the document
 * from live data, and exits. Called directly from the front controller
 * (index.php) before any HTML template/header/footer rendering happens.
 */
function sitemap_serve(): void
{
    // Belt-and-suspenders: this is a machine-consumed endpoint, so a stray
    // notice/warning must never corrupt the XML output.
    $previous_display_errors = ini_set('display_errors', '0');

    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    header('Content-Type: application/xml; charset=UTF-8');

    try {
        echo sitemap_render_xml(sitemap_generate()['urls']);
    } catch (Throwable $e) {
        error_log('Sitemap generation failed: ' . $e->getMessage());
        http_response_code(500);
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
           . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>';
    }

    if ($previous_display_errors !== false) {
        ini_set('display_errors', $previous_display_errors);
    }
}

/**
 * Record an informational "last regenerated" timestamp + URL count in the
 * existing `settings` key/value table (core/seo.php's seo_site_defaults()
 * reads the same table). This is purely a status readout for Admin >
 * Sitemap — /sitemap.xml itself is always generated live regardless, so
 * there is nothing here that can go stale.
 */
function sitemap_record_generation(int $url_count): void
{
    $pdo = db();
    if (!$pdo) {
        return;
    }
    try {
        $stmt = $pdo->prepare(
            'INSERT INTO settings (`key`, `value`) VALUES (:k, :v)
             ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)'
        );
        $stmt->execute([':k' => 'sitemap_last_generated_at', ':v' => date('Y-m-d H:i:s')]);
        $stmt->execute([':k' => 'sitemap_last_url_count', ':v' => (string) $url_count]);
    } catch (PDOException $e) {
        error_log('Sitemap status save failed: ' . $e->getMessage());
    }
}
