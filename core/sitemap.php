<?php
/**
 * Sitemap — single source of truth for /sitemap.xml (public) and
 * Admin > Sitemap > Regenerate (manual refresh). Both entry points call the
 * exact same functions here; there is no separate admin-side generator.
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
 * Build the full set of indexable URLs for the site.
 *
 * @return array<string, ?string> Map of absolute URL => lastmod (DB datetime
 *                                 string or null), already de-duplicated by
 *                                 URL and sorted for stable output.
 */
function sitemap_collect_urls(): array
{
    $pdo  = db();
    $base = defined('SITE_URL') && SITE_URL ? rtrim(SITE_URL, '/') : (defined('BASE_URL') ? rtrim(BASE_URL, '/') : '');

    /** @var array<string, ?string> $urls loc => lastmod */
    $urls = [];

    $add = static function (string $loc_or_path, ?string $lastmod = null) use (&$urls, $base): void {
        $loc = preg_match('#^https?://#i', $loc_or_path)
            ? $loc_or_path
            : $base . '/' . ltrim($loc_or_path, '/');
        // Root path shouldn't end with a stray trailing slash beyond the domain.
        if ($loc !== $base . '/' && str_ends_with($loc, '/')) {
            $loc = rtrim($loc, '/');
        }
        $urls[$loc] = $lastmod; // keyed by URL => automatic de-duplication
    };

    // -------------------------------------------------------------------
    // 1) Static, file-based pages (router_discover_pages() is the same
    //    "what pages exist" logic admin/pages.php already uses).
    // -------------------------------------------------------------------
    $seo_rows = [];
    if ($pdo) {
        try {
            $stmt = $pdo->query('SELECT path, is_published, canonical, updated_at FROM pages');
            foreach ($stmt as $row) {
                $seo_rows[$row['path']] = $row;
            }
        } catch (PDOException $e) {
            // `pages` table not migrated yet — every static page falls back
            // to "no metadata row" handling below.
        }
    }

    foreach (router_discover_pages() as $path) {
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
        $add($loc, $row['updated_at'] ?? null);
    }

    if (!$pdo) {
        ksort($urls);
        return $urls;
    }

    // -------------------------------------------------------------------
    // 2) Blog posts — /blog/{slug} (mirrors pages/blog/single.php's own
    //    published + published_at gate).
    // -------------------------------------------------------------------
    try {
        $stmt = $pdo->query(
            "SELECT slug, updated_at FROM posts
             WHERE status = 'published' AND (published_at IS NULL OR published_at <= NOW())"
        );
        foreach ($stmt as $r) {
            $add('/blog/' . $r['slug'], $r['updated_at']);
        }
    } catch (PDOException $e) {
        // posts table missing — skip
    }

    // -------------------------------------------------------------------
    // 3) Services — /services/{slug} (Service Master; mirrors
    //    router_service_exists()'s status='published' gate).
    // -------------------------------------------------------------------
    try {
        $stmt = $pdo->query("SELECT slug, robots, canonical, updated_at FROM services WHERE status = 'published'");
        foreach ($stmt as $r) {
            if (stripos((string) $r['robots'], 'noindex') !== false) {
                continue;
            }
            $loc = ($r['canonical'] ?? '') !== '' ? $r['canonical'] : '/services/' . $r['slug'];
            $add($loc, $r['updated_at']);
        }
    } catch (PDOException $e) {
        // services table missing — skip
    }

    // -------------------------------------------------------------------
    // 4) Hire Master — /hire-ai-engineers/{slug} (mirrors
    //    router_hire_page_exists()'s status='published' gate).
    // -------------------------------------------------------------------
    try {
        $stmt = $pdo->query("SELECT slug, robots, canonical, updated_at FROM hire_pages WHERE status = 'published'");
        foreach ($stmt as $r) {
            if (stripos((string) $r['robots'], 'noindex') !== false) {
                continue;
            }
            $loc = ($r['canonical'] ?? '') !== '' ? $r['canonical'] : '/hire-ai-engineers/' . $r['slug'];
            $add($loc, $r['updated_at']);
        }
    } catch (PDOException $e) {
        // hire_pages table missing — skip
    }

    // -------------------------------------------------------------------
    // 5) Success Stories — /success-stories/{slug} (the hub page itself,
    //    /success-stories, is a static page already covered in step 1).
    // -------------------------------------------------------------------
    try {
        $stmt = $pdo->query("SELECT slug, robots, canonical, updated_at FROM success_stories WHERE status = 'published'");
        foreach ($stmt as $r) {
            if (stripos((string) $r['robots'], 'noindex') !== false) {
                continue;
            }
            $loc = ($r['canonical'] ?? '') !== '' ? $r['canonical'] : '/success-stories/' . $r['slug'];
            $add($loc, $r['updated_at']);
        }
    } catch (PDOException $e) {
        // success_stories table missing — skip
    }

    // -------------------------------------------------------------------
    // 6) Webinars / Podcast — pages/podcast/index.php (the only live URL,
    //    already included in step 1) currently renders a placeholder with
    //    its DB query disabled, so there are no public per-webinar detail
    //    URLs to add yet. Wire in a `SELECT slug, updated_at FROM webinars
    //    WHERE status = 'published'` loop here (mirroring Services/Hire
    //    above) once /podcast/{slug} detail pages actually ship.
    // -------------------------------------------------------------------

    ksort($urls);
    return $urls;
}

/**
 * Render a URL map (as returned by sitemap_collect_urls()) into a sitemap
 * XML document string.
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
        echo sitemap_render_xml(sitemap_collect_urls());
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
