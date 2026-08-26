<?php
/**
 * Content Management Helpers
 * 
 * Provides CRUD functions for webinars, success_stories, and services
 */

declare(strict_types=1);

/**
 * Get all webinars with optional filters
 */
function get_webinars(\PDO $pdo, array $filters = []): array
{
    $where = [];
    $params = [];

    if (!empty($filters['status'])) {
        $where[] = "status = ?";
        $params[] = $filters['status'];
    }

    if (!empty($filters['search'])) {
        $where[] = "(title LIKE ? OR speaker_name LIKE ?)";
        $search = '%' . $filters['search'] . '%';
        $params[] = $search;
        $params[] = $search;
    }

    if (isset($filters['published_only']) && $filters['published_only']) {
        $where[] = "status = 'published' AND published_at IS NOT NULL";
    }

    $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    $order = $filters['order'] ?? 'scheduled_at DESC';

    $sql = "SELECT * FROM webinars $where_clause ORDER BY $order";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll() ?: [];
}

/**
 * Get single webinar by ID or slug
 */
function get_webinar(\PDO $pdo, int|string $identifier): ?array
{
    $field = is_numeric($identifier) ? 'id' : 'slug';
    $stmt = $pdo->prepare("SELECT * FROM webinars WHERE $field = ? LIMIT 1");
    $stmt->execute([$identifier]);
    $result = $stmt->fetch();

    return $result ?: null;
}

/**
 * Create or update a webinar
 */
function save_webinar(\PDO $pdo, array $data): array
{
    $id = $data['id'] ?? null;
    $now = date('Y-m-d H:i:s');

    // Validate required fields
    $required = ['slug', 'title', 'excerpt', 'speaker_name', 'scheduled_at'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            return ['success' => false, 'error' => "Missing required field: $field"];
        }
    }

    try {
        if ($id) {
            // Update
            $updates = [];
            $params = [];
            foreach ($data as $key => $value) {
                if ($key !== 'id' && $key !== 'created_at') {
                    $updates[] = "$key = ?";
                    $params[] = $value;
                }
            }
            $updates[] = "updated_at = ?";
            $params[] = $now;
            $params[] = $id;

            $sql = "UPDATE webinars SET " . implode(', ', $updates) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            return ['success' => true, 'id' => $id, 'message' => 'Webinar updated'];
        } else {
            // Create
            $data['created_at'] = $now;
            $data['updated_at'] = $now;

            $cols = array_keys($data);
            $placeholders = array_fill(0, count($cols), '?');

            $sql = "INSERT INTO webinars (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array_values($data));

            $new_id = (int) $pdo->lastInsertId();
            return ['success' => true, 'id' => $new_id, 'message' => 'Webinar created'];
        }
    } catch (PDOException $e) {
        error_log('Save webinar error: ' . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Delete a webinar
 */
function delete_webinar(\PDO $pdo, int $id): array
{
    try {
        $stmt = $pdo->prepare("DELETE FROM webinars WHERE id = ?");
        $stmt->execute([$id]);

        return ['success' => true, 'message' => 'Webinar deleted'];
    } catch (PDOException $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Get all success stories with optional filters
 */
function get_success_stories(\PDO $pdo, array $filters = []): array
{
    $where = [];
    $params = [];

    if (!empty($filters['status'])) {
        $where[] = "status = ?";
        $params[] = $filters['status'];
    }

    if (!empty($filters['search'])) {
        $where[] = "(title LIKE ? OR company_name LIKE ?)";
        $search = '%' . $filters['search'] . '%';
        $params[] = $search;
        $params[] = $search;
    }

    if (!empty($filters['industry'])) {
        $where[] = "industry = ?";
        $params[] = $filters['industry'];
    }

    if (isset($filters['featured_only']) && $filters['featured_only']) {
        $where[] = "featured = 1";
    }

    if (isset($filters['published_only']) && $filters['published_only']) {
        $where[] = "status = 'published' AND published_at IS NOT NULL";
    }

    if (!empty($filters['category_id'])) {
        $where[] = "category_id = ?";
        $params[] = (int) $filters['category_id'];
    }

    if (!empty($filters['exclude_id'])) {
        $where[] = "id != ?";
        $params[] = (int) $filters['exclude_id'];
    }

    $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    $order = $filters['order'] ?? 'published_at DESC, id DESC';

    $sql = "SELECT * FROM success_stories $where_clause ORDER BY $order";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll() ?: [];
}

/**
 * Get published success stories by ID, preserving the order of $ids
 * (mirrors get_hire_pages_by_ids()).
 */
function get_success_stories_by_ids(\PDO $pdo, array $ids): array
{
    $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
    if (empty($ids)) {
        return [];
    }
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM success_stories WHERE id IN ($placeholders) AND status = 'published'");
    $stmt->execute($ids);

    $by_id = [];
    foreach ($stmt->fetchAll() ?: [] as $row) {
        $by_id[(int) $row['id']] = $row;
    }
    $ordered = [];
    foreach ($ids as $id) {
        if (isset($by_id[$id])) {
            $ordered[] = $by_id[$id];
        }
    }
    return $ordered;
}

/**
 * Get success story categories (the filter/taxonomy master for the
 * /success-stories listing page and the admin category picker).
 */
function get_success_story_categories(\PDO $pdo, array $filters = []): array
{
    $where = [];
    $params = [];

    if (!empty($filters['status'])) {
        $where[] = "status = ?";
        $params[] = $filters['status'];
    }

    $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    $sql = "SELECT * FROM success_story_categories $where_clause ORDER BY sort_order ASC, name ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll() ?: [];
}

/**
 * Get single success story by ID or slug
 */
function get_success_story(\PDO $pdo, int|string $identifier): ?array
{
    $field = is_numeric($identifier) ? 'id' : 'slug';
    $stmt = $pdo->prepare("SELECT * FROM success_stories WHERE $field = ? LIMIT 1");
    $stmt->execute([$identifier]);
    $result = $stmt->fetch();

    return $result ?: null;
}

/**
 * Create or update a success story
 */
function save_success_story(\PDO $pdo, array $data): array
{
    $id = $data['id'] ?? null;
    $now = date('Y-m-d H:i:s');

    // Validate required fields
    $required = ['slug', 'title', 'excerpt', 'company_name', 'industry'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            return ['success' => false, 'error' => "Missing required field: $field"];
        }
    }

    try {
        if ($id) {
            // Update
            $updates = [];
            $params = [];
            foreach ($data as $key => $value) {
                if ($key !== 'id' && $key !== 'created_at') {
                    $updates[] = "$key = ?";
                    $params[] = $value;
                }
            }
            $updates[] = "updated_at = ?";
            $params[] = $now;
            $params[] = $id;

            $sql = "UPDATE success_stories SET " . implode(', ', $updates) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            return ['success' => true, 'id' => $id, 'message' => 'Success story updated'];
        } else {
            // Create
            $data['created_at'] = $now;
            $data['updated_at'] = $now;

            $cols = array_keys($data);
            $placeholders = array_fill(0, count($cols), '?');

            $sql = "INSERT INTO success_stories (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array_values($data));

            $new_id = (int) $pdo->lastInsertId();
            return ['success' => true, 'id' => $new_id, 'message' => 'Success story created'];
        }
    } catch (PDOException $e) {
        error_log('Save success story error: ' . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Delete a success story
 */
function delete_success_story(\PDO $pdo, int $id): array
{
    try {
        $stmt = $pdo->prepare("DELETE FROM success_stories WHERE id = ?");
        $stmt->execute([$id]);

        return ['success' => true, 'message' => 'Success story deleted'];
    } catch (PDOException $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Get all podcast episodes with optional filters
 */
function get_podcasts(\PDO $pdo, array $filters = []): array
{
    $where = [];
    $params = [];

    if (!empty($filters['status'])) {
        $where[] = "status = ?";
        $params[] = $filters['status'];
    }

    if (!empty($filters['search'])) {
        $where[] = "(title LIKE ? OR guest_name LIKE ?)";
        $search = '%' . $filters['search'] . '%';
        $params[] = $search;
        $params[] = $search;
    }

    if (isset($filters['featured_only']) && $filters['featured_only']) {
        $where[] = "featured = 1";
    }

    if (isset($filters['published_only']) && $filters['published_only']) {
        $where[] = "status = 'published' AND published_at IS NOT NULL";
    }

    if (!empty($filters['exclude_id'])) {
        $where[] = "id != ?";
        $params[] = (int) $filters['exclude_id'];
    }

    $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    $order = $filters['order'] ?? 'publish_date DESC, id DESC';

    $sql = "SELECT * FROM podcasts $where_clause ORDER BY $order";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll() ?: [];
}

/**
 * Get single podcast episode by ID or slug
 */
function get_podcast(\PDO $pdo, int|string $identifier): ?array
{
    $field = is_numeric($identifier) ? 'id' : 'slug';
    $stmt = $pdo->prepare("SELECT * FROM podcasts WHERE $field = ? LIMIT 1");
    $stmt->execute([$identifier]);
    $result = $stmt->fetch();

    return $result ?: null;
}

/**
 * Create or update a podcast episode
 */
function save_podcast(\PDO $pdo, array $data): array
{
    $id = $data['id'] ?? null;
    $now = date('Y-m-d H:i:s');

    $required = ['slug', 'title', 'guest_name'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            return ['success' => false, 'error' => "Missing required field: $field"];
        }
    }

    try {
        if ($id) {
            $updates = [];
            $params = [];
            foreach ($data as $key => $value) {
                if ($key !== 'id' && $key !== 'created_at') {
                    $updates[] = "$key = ?";
                    $params[] = $value;
                }
            }
            $updates[] = "updated_at = ?";
            $params[] = $now;
            $params[] = $id;

            $sql = "UPDATE podcasts SET " . implode(', ', $updates) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            return ['success' => true, 'id' => $id, 'message' => 'Podcast episode updated'];
        } else {
            $data['created_at'] = $now;
            $data['updated_at'] = $now;

            $cols = array_keys($data);
            $placeholders = array_fill(0, count($cols), '?');

            $sql = "INSERT INTO podcasts (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array_values($data));

            $new_id = (int) $pdo->lastInsertId();
            return ['success' => true, 'id' => $new_id, 'message' => 'Podcast episode created'];
        }
    } catch (PDOException $e) {
        error_log('Save podcast error: ' . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Delete a podcast episode
 */
function delete_podcast(\PDO $pdo, int $id): array
{
    try {
        $stmt = $pdo->prepare("DELETE FROM podcasts WHERE id = ?");
        $stmt->execute([$id]);

        return ['success' => true, 'message' => 'Podcast episode deleted'];
    } catch (PDOException $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Shape a raw `podcasts` row into the flat structure partials/podcast-card.php
 * (and the Featured Episode / episode hero sections) expect — one place to
 * derive the thumbnail (thumbnail_override, else the YouTube hqdefault) and
 * the formatted publish date, shared by pages/podcast/index.php and
 * pages/podcast/single.php so both render episodes identically.
 */
function podcast_card_data(array $row): array
{
    $video_id = youtube_id_from_url($row['youtube_url'] ?? '');
    $thumbnail = !empty($row['thumbnail_override'])
        ? media_url($row['thumbnail_override'])
        : ($video_id ? "https://img.youtube.com/vi/{$video_id}/hqdefault.jpg" : '');

    return [
        'title' => $row['title'],
        'guest_name' => $row['guest_name'],
        'guest_designation' => $row['guest_designation'] ?? '',
        'guest_company' => $row['guest_company'] ?? '',
        'guest_company_logo' => !empty($row['guest_company_logo']) ? media_url($row['guest_company_logo']) : '',
        'thumbnail' => $thumbnail,
        'video_id' => $video_id,
        'url' => url('/podcast/' . $row['slug']),
        'publish_date' => !empty($row['publish_date']) ? date('M j, Y', strtotime((string) $row['publish_date'])) : '',
    ];
}

/**
 * Get all services
 */
function get_services(\PDO $pdo, array $filters = []): array
{
    $where = [];
    $params = [];

    if (!empty($filters['status'])) {
        $where[] = "status = ?";
        $params[] = $filters['status'];
    }

    if (isset($filters['display_on_home']) && $filters['display_on_home']) {
        $where[] = "display_on_home = 1";
    }

    $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    $order = 'service_number ASC';

    $sql = "SELECT * FROM services $where_clause ORDER BY $order";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll() ?: [];
}

/**
 * Get single service by ID or slug
 */
function get_service(\PDO $pdo, int|string $identifier): ?array
{
    $field = is_numeric($identifier) ? 'id' : 'slug';
    $stmt = $pdo->prepare("SELECT * FROM services WHERE $field = ? LIMIT 1");
    $stmt->execute([$identifier]);
    $result = $stmt->fetch();

    return $result ?: null;
}

/**
 * Create or update a service
 */
function save_service(\PDO $pdo, array $data): array
{
    $id = $data['id'] ?? null;
    $now = date('Y-m-d H:i:s');

    // Validate required fields
    $required = ['slug', 'name', 'title', 'excerpt'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            return ['success' => false, 'error' => "Missing required field: $field"];
        }
    }

    try {
        if ($id) {
            // Update
            $updates = [];
            $params = [];
            foreach ($data as $key => $value) {
                if ($key !== 'id' && $key !== 'created_at') {
                    $updates[] = "$key = ?";
                    $params[] = $value;
                }
            }
            $updates[] = "updated_at = ?";
            $params[] = $now;
            $params[] = $id;

            $sql = "UPDATE services SET " . implode(', ', $updates) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            return ['success' => true, 'id' => $id, 'message' => 'Service updated'];
        } else {
            // Create
            $data['created_at'] = $now;
            $data['updated_at'] = $now;

            $cols = array_keys($data);
            $placeholders = array_fill(0, count($cols), '?');

            $sql = "INSERT INTO services (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array_values($data));

            $new_id = (int) $pdo->lastInsertId();
            return ['success' => true, 'id' => $new_id, 'message' => 'Service created'];
        }
    } catch (PDOException $e) {
        error_log('Save service error: ' . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Delete a service
 */
function delete_service(\PDO $pdo, int $id): array
{
    try {
        $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
        $stmt->execute([$id]);

        return ['success' => true, 'message' => 'Service deleted'];
    } catch (PDOException $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Fetch published services by ID, in the caller-specified order. Used to
 * resolve a service's Related Services picker (which stores IDs, not URLs)
 * into real rows at render time.
 */
function get_services_by_ids(\PDO $pdo, array $ids): array
{
    $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
    if (empty($ids)) {
        return [];
    }
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id IN ($placeholders) AND status = 'published'");
    $stmt->execute($ids);

    $by_id = [];
    foreach ($stmt->fetchAll() ?: [] as $row) {
        $by_id[(int) $row['id']] = $row;
    }
    $ordered = [];
    foreach ($ids as $id) {
        if (isset($by_id[$id])) {
            $ordered[] = $by_id[$id];
        }
    }
    return $ordered;
}

/**
 * Fetch published blog posts by ID, each with its first category name
 * resolved, in the caller-specified order. Used to resolve a service's
 * Knowledge Hub picker (which stores post IDs, not titles/images) into real
 * rows at render time.
 */
function get_blog_posts_by_ids(\PDO $pdo, array $ids): array
{
    $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
    if (empty($ids)) {
        return [];
    }
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare(
        "SELECT p.id, p.slug, p.title, p.excerpt, p.featured_image,
                (SELECT c.name FROM post_categories pc
                 JOIN categories c ON c.id = pc.category_id
                 WHERE pc.post_id = p.id ORDER BY c.name ASC LIMIT 1) AS category_name
         FROM posts p
         WHERE p.id IN ($placeholders) AND p.status = 'published'"
    );
    $stmt->execute($ids);

    $by_id = [];
    foreach ($stmt->fetchAll() ?: [] as $row) {
        $by_id[(int) $row['id']] = $row;
    }
    $ordered = [];
    foreach ($ids as $id) {
        if (isset($by_id[$id])) {
            $ordered[] = $by_id[$id];
        }
    }
    return $ordered;
}

/**
 * Get all hire pages (Hire Master — mirrors get_services())
 */
function get_hire_pages(\PDO $pdo, array $filters = []): array
{
    $where = [];
    $params = [];

    if (!empty($filters['status'])) {
        $where[] = "status = ?";
        $params[] = $filters['status'];
    }

    if (isset($filters['display_on_hub']) && $filters['display_on_hub']) {
        $where[] = "display_on_hub = 1";
    }

    $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    $order = 'sort_order ASC';

    $sql = "SELECT * FROM hire_pages $where_clause ORDER BY $order";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll() ?: [];
}

/**
 * Get single hire page by ID or slug (mirrors get_service())
 */
function get_hire_page(\PDO $pdo, int|string $identifier): ?array
{
    $field = is_numeric($identifier) ? 'id' : 'slug';
    $stmt = $pdo->prepare("SELECT * FROM hire_pages WHERE $field = ? LIMIT 1");
    $stmt->execute([$identifier]);
    $result = $stmt->fetch();

    return $result ?: null;
}

/**
 * Create or update a hire page (mirrors save_service())
 */
function save_hire_page(\PDO $pdo, array $data): array
{
    $id = $data['id'] ?? null;
    $now = date('Y-m-d H:i:s');

    // Validate required fields
    $required = ['slug', 'name', 'title', 'excerpt'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            return ['success' => false, 'error' => "Missing required field: $field"];
        }
    }

    try {
        if ($id) {
            // Update
            $updates = [];
            $params = [];
            foreach ($data as $key => $value) {
                if ($key !== 'id' && $key !== 'created_at') {
                    $updates[] = "$key = ?";
                    $params[] = $value;
                }
            }
            $updates[] = "updated_at = ?";
            $params[] = $now;
            $params[] = $id;

            $sql = "UPDATE hire_pages SET " . implode(', ', $updates) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            return ['success' => true, 'id' => $id, 'message' => 'Hire page updated'];
        } else {
            // Create
            $data['created_at'] = $now;
            $data['updated_at'] = $now;

            $cols = array_keys($data);
            $placeholders = array_fill(0, count($cols), '?');

            $sql = "INSERT INTO hire_pages (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array_values($data));

            $new_id = (int) $pdo->lastInsertId();
            return ['success' => true, 'id' => $new_id, 'message' => 'Hire page created'];
        }
    } catch (PDOException $e) {
        error_log('Save hire page error: ' . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Delete a hire page (mirrors delete_service())
 */
function delete_hire_page(\PDO $pdo, int $id): array
{
    try {
        $stmt = $pdo->prepare("DELETE FROM hire_pages WHERE id = ?");
        $stmt->execute([$id]);

        return ['success' => true, 'message' => 'Hire page deleted'];
    } catch (PDOException $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Fetch published hire pages by ID, in the caller-specified order. Used to
 * resolve a hire page's Related Hire Pages picker (mirrors get_services_by_ids()).
 */
function get_hire_pages_by_ids(\PDO $pdo, array $ids): array
{
    $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
    if (empty($ids)) {
        return [];
    }
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM hire_pages WHERE id IN ($placeholders) AND status = 'published'");
    $stmt->execute($ids);

    $by_id = [];
    foreach ($stmt->fetchAll() ?: [] as $row) {
        $by_id[(int) $row['id']] = $row;
    }
    $ordered = [];
    foreach ($ids as $id) {
        if (isset($by_id[$id])) {
            $ordered[] = $by_id[$id];
        }
    }
    return $ordered;
}
