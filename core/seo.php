<?php
/**
 * SEO — load page metadata from the DB by canonical path.
 *
 * Returns an associative array with normalized keys. Templates and the header
 * partial read from this. Stage 2 admin populates the `pages` table.
 */

declare(strict_types=1);

/**
 * Load SEO metadata for a given URL path. Returns an array even if no row
 * exists — keys default to empty strings.
 *
 * @return array{
 *   title: string,
 *   meta_description: string,
 *   meta_keywords: string,
 *   og_image: string,
 *   canonical: string,
 *   schema_json: string
 * }
 */
function seo_load_for_path(string $path): array
{
    $defaults = [
        'title'            => '',
        'meta_description' => '',
        'meta_keywords'    => '',
        'og_image'         => '',
        'canonical'        => '',
        'schema_json'      => '',
    ];

    if ($path === '') {
        return $defaults;
    }

    $pdo = db();
    if ($pdo === null) {
        return $defaults;
    }

    try {
        $stmt = $pdo->prepare(
            'SELECT title, meta_description, meta_keywords, og_image, canonical, schema_json
             FROM pages
             WHERE path = :path AND is_published = 1
             LIMIT 1'
        );
        $stmt->execute([':path' => $path]);
        $row = $stmt->fetch();
        if ($row) {
            return array_merge($defaults, $row);
        }
    } catch (PDOException $e) {
        // Table may not exist yet (pre-Stage-2). Silently fall through to defaults.
        error_log('SEO lookup failed for ' . $path . ': ' . $e->getMessage());
    }

    return $defaults;
}

/**
 * Site-wide defaults from the settings table (or constants if DB is empty).
 */
function seo_site_defaults(): array
{
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }

    $defaults = [
        'site_name'    => defined('SITE_NAME') ? SITE_NAME : 'Quantal AI',
        'description'  => defined('SITE_DEFAULT_DESCRIPTION') ? SITE_DEFAULT_DESCRIPTION : '',
        'og_image'     => defined('SITE_DEFAULT_OG_IMAGE') ? SITE_DEFAULT_OG_IMAGE : '',
        'organization' => '', // JSON-LD Organization schema, set in admin > settings
    ];

    $pdo = db();
    if ($pdo !== null) {
        try {
            $stmt = $pdo->query('SELECT `key`, `value` FROM settings');
            if ($stmt) {
                foreach ($stmt->fetchAll() as $row) {
                    $defaults[$row['key']] = $row['value'];
                }
            }
        } catch (PDOException $e) {
            // settings table may not exist yet
        }
    }

    return $cached = $defaults;
}
