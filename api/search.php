<?php
/**
 * Live search AJAX endpoint.
 *
 * GET /api/search.php?q=<term>
 *
 * Returns JSON: { "query": "...", "count": N, "results": [ {title, type,
 * type_label, url, excerpt}, ... ] }
 *
 * Read-only, public, no authentication — mirrors api/content.php.
 */

declare(strict_types=1);

define('PAGES_DIR', dirname(__DIR__) . '/pages');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/router.php';
require_once __DIR__ . '/../core/seo.php';
require_once __DIR__ . '/../core/search.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$query = is_string($_GET['q'] ?? null) ? $_GET['q'] : '';

try {
    $result = search_site(db(), $query, 8);
    echo json_encode([
        'query'   => $result['query'],
        'count'   => count($result['results']),
        'results' => $result['results'],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    error_log('Search API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['query' => '', 'count' => 0, 'results' => [], 'error' => 'Search failed']);
}
