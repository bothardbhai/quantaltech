<?php
/**
 * Public API — Webinars, Success Stories, and Services
 * 
 * JSON API endpoints for frontend consumption
 * No authentication required (public data only)
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../core/content-helpers.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = db();
if (!$pdo) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Determine endpoint and action
$path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$parts = explode('/', $path);
$endpoint = $parts[array_key_last($parts)] ?? '';
$action = $_GET['action'] ?? ($_GET['type'] ?? '');

try {
    // Webinars API
    if ($endpoint === 'webinars') {
        if ($action === 'list' || $action === '') {
            $filters = [
                'published_only' => true,
                'order'          => 'scheduled_at ASC',
                'status'         => 'published',
            ];
            
            if (!empty($_GET['filter'])) {
                if ($_GET['filter'] === 'upcoming') {
                    // Only future webinars
                    $webinars = array_filter(get_webinars($pdo, $filters), 
                        fn($w) => strtotime($w['scheduled_at']) > time()
                    );
                } elseif ($_GET['filter'] === 'past') {
                    // Only past webinars
                    $webinars = array_filter(get_webinars($pdo, $filters),
                        fn($w) => strtotime($w['scheduled_at']) <= time()
                    );
                } else {
                    $webinars = get_webinars($pdo, $filters);
                }
            } else {
                $webinars = get_webinars($pdo, $filters);
            }

            echo json_encode([
                'success' => true,
                'count'   => count($webinars),
                'data'    => array_values($webinars),
            ]);
        } elseif ($action === 'detail' && !empty($_GET['slug'])) {
            $webinar = get_webinar($pdo, sanitize_slug($_GET['slug']));
            if ($webinar && $webinar['status'] === 'published') {
                echo json_encode([
                    'success' => true,
                    'data'    => $webinar,
                ]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Webinar not found']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
        }
    }
    
    // Success Stories API
    elseif ($endpoint === 'success-stories') {
        if ($action === 'list' || $action === '') {
            $filters = [
                'published_only' => true,
                'order'          => 'published_at DESC, id DESC',
                'status'         => 'published',
            ];
            
            if (!empty($_GET['featured'])) {
                $filters['featured_only'] = true;
            }
            
            if (!empty($_GET['industry'])) {
                $filters['industry'] = sanitize_text($_GET['industry']);
            }

            $stories = get_success_stories($pdo, $filters);

            echo json_encode([
                'success' => true,
                'count'   => count($stories),
                'data'    => array_values($stories),
            ]);
        } elseif ($action === 'detail' && !empty($_GET['slug'])) {
            $story = get_success_story($pdo, sanitize_slug($_GET['slug']));
            if ($story && $story['status'] === 'published') {
                echo json_encode([
                    'success' => true,
                    'data'    => $story,
                ]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Success story not found']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
        }
    }
    
    // Services API
    elseif ($endpoint === 'services') {
        if ($action === 'list' || $action === '') {
            $filters = [
                'status'          => 'published',
                'display_on_home' => true,
            ];
            
            $services = get_services($pdo, $filters);

            echo json_encode([
                'success' => true,
                'count'   => count($services),
                'data'    => array_values($services),
            ]);
        } elseif ($action === 'detail' && !empty($_GET['slug'])) {
            $service = get_service($pdo, sanitize_slug($_GET['slug']));
            if ($service && $service['status'] === 'published') {
                echo json_encode([
                    'success' => true,
                    'data'    => $service,
                ]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Service not found']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
        }
    }
    
    else {
        http_response_code(404);
        echo json_encode(['error' => 'Unknown endpoint']);
    }

} catch (Exception $e) {
    error_log('API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
