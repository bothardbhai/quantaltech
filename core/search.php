<?php
/**
 * Site-wide search.
 *
 * Combines two sources into one ranked result set:
 *   1. Statically-routed page templates (services, about, contact, etc.),
 *      discovered on disk via router_discover_pages() so new pages are
 *      picked up automatically with zero code changes. Titles/meta come
 *      from the `pages` SEO table when the admin has filled it in, and
 *      fall back to the small label map below otherwise.
 *   2. Database-driven content: blog posts, success stories, podcasts
 *      (webinars table — see pages/podcast/index.php).
 *
 * See SEARCH.md for how to add new searchable content.
 */

declare(strict_types=1);

/**
 * Trim, collapse whitespace, and cap length. Never trust raw $_GET input.
 */
function search_normalize_query(string $q): string
{
    $q = trim($q);
    $q = preg_replace('/\s+/u', ' ', $q) ?? $q;
    return mb_substr($q, 0, 80);
}

/**
 * Escape LIKE wildcards in user input so `%` / `_` in a search term are
 * treated literally rather than as SQL wildcards.
 */
function search_like_term(string $term): string
{
    $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term);
    return '%' . $escaped . '%';
}

/**
 * Strip HTML/entities down to a plain-text excerpt for display and for
 * matching against rich-text fields (body_html, challenge_html, ...).
 */
function search_plain_text(string $html, int $len = 160): string
{
    $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
    if (mb_strlen($text) > $len) {
        $text = mb_substr($text, 0, $len) . '…';
    }
    return $text;
}

/**
 * Human-readable label for a content type key.
 */
function search_type_label(string $type): string
{
    $labels = [
        'page'          => 'Page',
        'service'       => 'Service',
        'blog'          => 'Blog',
        'success_story' => 'Success Story',
        'podcast'       => 'Podcast',
    ];
    return $labels[$type] ?? 'Page';
}

/**
 * Fallback title/type labels for statically-routed pages that don't have
 * an SEO row in the `pages` table yet.
 *
 * ---> To add a new static page to search: nothing to do — it's picked up
 * automatically from /pages via router_discover_pages(). Add an entry
 * here only if you want a nicer title/type than the auto-generated one.
 */
function search_static_page_labels(): array
{
    return [
        '/'                        => ['title' => 'Home', 'type' => 'page'],
        '/about'                   => ['title' => 'About Us', 'type' => 'page'],
        '/contact'                 => ['title' => 'Contact Us', 'type' => 'page'],
        '/hire-ai-engineers'       => ['title' => 'Hire AI Engineers', 'type' => 'page'],
        '/terms-of-service'        => ['title' => 'Terms of Service', 'type' => 'page'],
        '/refund-policy'           => ['title' => 'Refund & Cancellation Policy', 'type' => 'page'],
        '/blog'                    => ['title' => 'Blog', 'type' => 'page'],
        '/success-stories'         => ['title' => 'Success Stories', 'type' => 'page'],
        '/podcast'                 => ['title' => 'Podcasts', 'type' => 'page'],
        '/services'                => ['title' => 'AI Solutions', 'type' => 'service'],
        '/services/voice-ai'       => ['title' => 'Voice AI', 'type' => 'service'],
        '/services/text'           => ['title' => 'Text AI', 'type' => 'service'],
        '/services/image'          => ['title' => 'Image / Document AI', 'type' => 'service'],
        '/services/process-auto'  => ['title' => 'Process Automation', 'type' => 'service'],
        '/services/ai-engineering' => ['title' => 'AI Engineering', 'type' => 'service'],
    ];
}

/**
 * Turn a path segment ("hire-ai-engineers") into a Title Case guess when no
 * explicit label exists anywhere.
 */
function search_title_from_slug(string $path): string
{
    $slug = trim($path, '/');
    if ($slug === '') {
        return 'Home';
    }
    $last = basename($slug);
    return ucwords(str_replace(['-', '_'], ' ', $last));
}

/**
 * Score a piece of haystack text against the query. Returns 0 if no match.
 */
function search_score_field(string $haystack, string $needle, int $weight): int
{
    if ($haystack === '' || $needle === '') {
        return 0;
    }
    $haystack = mb_strtolower($haystack);
    if (!str_contains($haystack, $needle)) {
        return 0;
    }
    // Bonus for a match at the very start (e.g. title starts with the query).
    return str_starts_with($haystack, $needle) ? $weight + 3 : $weight;
}

/**
 * Paths returned by router_discover_pages() that are real files but not
 * real pages: POST-only JSON handlers, the 404/thank-you/search templates
 * themselves, and any stray "copy"/backup file (basename with a space —
 * router_discover_pages() has no way to know those aren't meant to be
 * public, so we filter them out here instead).
 */
function search_excluded_paths(): array
{
    return ['/404', '/contact-submit', '/newsletter', '/search', '/thank-you'];
}

/**
 * Search statically-routed pages (about, contact, services/*, ...).
 * Reads the small set of page templates from disk — cheap enough to do on
 * every request for a site this size, no separate index file to maintain.
 */
function search_static_pages(string $query): array
{
    $needle = mb_strtolower($query);
    $labels = search_static_page_labels();
    $excluded = search_excluded_paths();
    $results = [];

    foreach (router_discover_pages() as $path) {
        if (in_array($path, $excluded, true) || str_contains(basename($path), ' ')) {
            continue;
        }
        $label = $labels[$path] ?? null;
        $seo   = seo_load_for_path($path === '/' ? '/' : $path);

        $title = $seo['title'] !== '' ? $seo['title'] : ($label['title'] ?? search_title_from_slug($path));
        $type  = $label['type'] ?? (str_starts_with($path, '/services') ? 'service' : 'page');

        $score = 0;
        $score += search_score_field($title, $needle, 5);
        $score += search_score_field($path, $needle, 3);
        $score += search_score_field($seo['meta_description'], $needle, 2);
        $score += search_score_field($seo['meta_keywords'], $needle, 2);

        if ($score <= 0) {
            continue;
        }

        $results[] = [
            'title'   => $title,
            'type'    => $type,
            'url'     => url($path),
            'excerpt' => $seo['meta_description'] !== '' ? search_plain_text($seo['meta_description'], 160) : '',
            'score'   => $score,
        ];
    }

    return $results;
}

/**
 * Search published blog posts.
 */
function search_blog_posts(\PDO $pdo, string $query, int $limit = 20): array
{
    $like = search_like_term($query);
    $needle = mb_strtolower($query);

    try {
        $stmt = $pdo->prepare(
            "SELECT slug, title, excerpt, body_html, meta_title, meta_description, meta_keywords
             FROM posts
             WHERE status = 'published'
               AND (published_at IS NULL OR published_at <= NOW())
               AND (title LIKE ? OR slug LIKE ? OR excerpt LIKE ? OR meta_title LIKE ?
                    OR meta_description LIKE ? OR meta_keywords LIKE ? OR body_html LIKE ?)
             ORDER BY published_at DESC
             LIMIT $limit"
        );
        $stmt->execute([$like, $like, $like, $like, $like, $like, $like]);
        $rows = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('search_blog_posts failed: ' . $e->getMessage());
        return [];
    }

    $results = [];
    foreach ($rows as $row) {
        $title = $row['meta_title'] !== '' ? $row['meta_title'] : $row['title'];
        $score = 0;
        $score += search_score_field($title, $needle, 5);
        $score += search_score_field($row['slug'], $needle, 3);
        $score += search_score_field($row['excerpt'], $needle, 2);
        $score += search_score_field($row['meta_description'], $needle, 2);
        $score += search_score_field($row['meta_keywords'], $needle, 1);
        $score += search_score_field(search_plain_text($row['body_html'], 4000), $needle, 1);

        $excerpt = $row['meta_description'] !== '' ? $row['meta_description']
            : ($row['excerpt'] !== '' ? $row['excerpt'] : search_plain_text($row['body_html']));

        $results[] = [
            'title'   => $row['title'],
            'type'    => 'blog',
            'url'     => url('/blog/' . $row['slug']),
            'excerpt' => search_plain_text($excerpt, 160),
            'score'   => $score,
        ];
    }

    return $results;
}

/**
 * Search published success stories / case studies.
 */
function search_success_stories(\PDO $pdo, string $query, int $limit = 20): array
{
    $like = search_like_term($query);
    $needle = mb_strtolower($query);

    try {
        $stmt = $pdo->prepare(
            "SELECT slug, title, excerpt, challenge_html, solution_html, results_html,
                    company_name, industry, meta_title, meta_description, meta_keywords
             FROM success_stories
             WHERE status = 'published'
               AND (published_at IS NULL OR published_at <= NOW())
               AND (title LIKE ? OR slug LIKE ? OR excerpt LIKE ? OR company_name LIKE ?
                    OR industry LIKE ? OR meta_title LIKE ? OR meta_description LIKE ?
                    OR meta_keywords LIKE ? OR challenge_html LIKE ? OR solution_html LIKE ?
                    OR results_html LIKE ?)
             ORDER BY published_at DESC
             LIMIT $limit"
        );
        $stmt->execute([$like, $like, $like, $like, $like, $like, $like, $like, $like, $like, $like]);
        $rows = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('search_success_stories failed: ' . $e->getMessage());
        return [];
    }

    $results = [];
    foreach ($rows as $row) {
        $title = $row['meta_title'] !== '' ? $row['meta_title'] : $row['title'];
        $body = $row['challenge_html'] . ' ' . $row['solution_html'] . ' ' . $row['results_html'];

        $score = 0;
        $score += search_score_field($title, $needle, 5);
        $score += search_score_field($row['slug'], $needle, 3);
        $score += search_score_field($row['company_name'], $needle, 3);
        $score += search_score_field($row['excerpt'], $needle, 2);
        $score += search_score_field($row['industry'], $needle, 2);
        $score += search_score_field($row['meta_description'], $needle, 2);
        $score += search_score_field($row['meta_keywords'], $needle, 1);
        $score += search_score_field(search_plain_text($body, 4000), $needle, 1);

        $excerpt = $row['meta_description'] !== '' ? $row['meta_description']
            : ($row['excerpt'] !== '' ? $row['excerpt'] : search_plain_text($body));

        $results[] = [
            'title'   => $row['title'],
            'type'    => 'success_story',
            'url'     => url('/success-stories/' . $row['slug']),
            'excerpt' => search_plain_text($excerpt, 160),
            'score'   => $score,
        ];
    }

    return $results;
}

/**
 * Search published podcast episodes (podcasts table).
 */
function search_podcasts(\PDO $pdo, string $query, int $limit = 20): array
{
    $like = search_like_term($query);
    $needle = mb_strtolower($query);

    try {
        $stmt = $pdo->prepare(
            "SELECT slug, title, short_description, description_html, guest_name,
                    meta_title, meta_description, meta_keywords
             FROM podcasts
             WHERE status = 'published'
               AND (title LIKE ? OR slug LIKE ? OR short_description LIKE ? OR guest_name LIKE ?
                    OR meta_title LIKE ? OR meta_description LIKE ? OR meta_keywords LIKE ?
                    OR description_html LIKE ?)
             ORDER BY publish_date DESC
             LIMIT $limit"
        );
        $stmt->execute([$like, $like, $like, $like, $like, $like, $like, $like]);
        $rows = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('search_podcasts failed: ' . $e->getMessage());
        return [];
    }

    $results = [];
    foreach ($rows as $row) {
        $title = $row['meta_title'] !== '' ? $row['meta_title'] : $row['title'];
        $body = $row['short_description'] . ' ' . $row['description_html'];

        $score = 0;
        $score += search_score_field($title, $needle, 5);
        $score += search_score_field($row['slug'], $needle, 3);
        $score += search_score_field($row['guest_name'], $needle, 2);
        $score += search_score_field($row['short_description'], $needle, 2);
        $score += search_score_field($row['meta_description'], $needle, 2);
        $score += search_score_field($row['meta_keywords'], $needle, 1);
        $score += search_score_field(search_plain_text($body, 4000), $needle, 1);

        $excerpt = $row['meta_description'] !== '' ? $row['meta_description']
            : ($row['short_description'] !== '' ? $row['short_description'] : search_plain_text($body));

        $results[] = [
            'title'   => $row['title'],
            'type'    => 'podcast',
            'url'     => url('/podcast/' . $row['slug']),
            'excerpt' => search_plain_text($excerpt, 160),
            'score'   => $score,
        ];
    }

    return $results;
}

/**
 * Run a full site search and return a ranked, deduplicated, limited result
 * set ready for JSON/HTML output.
 *
 * @return array{query: string, results: array<int, array{title:string,type:string,type_label:string,url:string,excerpt:string}>}
 */
function search_site(?\PDO $pdo, string $rawQuery, int $limit = 8): array
{
    $query = search_normalize_query($rawQuery);

    if (mb_strlen($query) < 2) {
        return ['query' => $query, 'results' => []];
    }

    $all = search_static_pages($query);

    if ($pdo !== null) {
        $all = array_merge(
            $all,
            search_blog_posts($pdo, $query),
            search_success_stories($pdo, $query),
            search_podcasts($pdo, $query)
        );
    }

    usort($all, function (array $a, array $b): int {
        if ($a['score'] !== $b['score']) {
            return $b['score'] <=> $a['score'];
        }
        return strcasecmp($a['title'], $b['title']);
    });

    // Dedupe by URL (a page and its SEO row can't collide, but just in case).
    $seen = [];
    $deduped = [];
    foreach ($all as $item) {
        if (isset($seen[$item['url']])) {
            continue;
        }
        $seen[$item['url']] = true;
        $deduped[] = $item;
    }

    $limited = array_slice($deduped, 0, max(1, $limit));

    $out = array_map(function (array $item): array {
        return [
            'title'      => $item['title'],
            'type'       => $item['type'],
            'type_label' => search_type_label($item['type']),
            'url'        => $item['url'],
            'excerpt'    => $item['excerpt'],
        ];
    }, $limited);

    return ['query' => $query, 'results' => $out];
}
