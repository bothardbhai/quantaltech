<?php
/**
 * Admin — Services (full CRUD)
 */

require __DIR__ . '/bootstrap.php';

$pdo  = db();
if (!$pdo) { die('Database connection failed.'); }

$action = $_GET['action'] ?? 'list';
$user   = auth_user();

// ── helpers ──────────────────────────────────────────────────────────────────

function svc_slugify(string $t): string
{
    $t = strtolower(trim($t));
    if (function_exists('iconv')) {
        $c = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $t);
        if ($c !== false) $t = $c;
    }
    $t = preg_replace('/[^a-z0-9]+/', '-', $t) ?? '';
    return trim($t, '-') ?: 'service';
}

function svc_unique_slug(PDO $pdo, string $base, ?int $excl = null): string
{
    $slug = $base; $i = 2;
    while (true) {
        $stmt = $pdo->prepare('SELECT id FROM services WHERE slug = :s' . ($excl ? ' AND id != :id' : ''));
        $p = [':s' => $slug];
        if ($excl) $p[':id'] = $excl;
        $stmt->execute($p);
        if (!$stmt->fetch()) return $slug;
        $slug = $base . '-' . $i++;
    }
}

// ── DELETE ───────────────────────────────────────────────────────────────────

if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_or_die();
    $del_id = (int) ($_POST['id'] ?? 0);
    if ($del_id > 0) {
        $pdo->prepare('DELETE FROM services WHERE id = :id')->execute([':id' => $del_id]);
        flash('success', 'Service deleted.');
    }
    header('Location: ' . ADMIN_URL . '/services.php'); exit;
}

// ── EDIT / NEW ───────────────────────────────────────────────────────────────

if ($action === 'new' || $action === 'edit') {
    $id      = (int) ($_GET['id'] ?? 0);
    $service = null;

    if ($action === 'edit' && $id > 0) {
        $s = $pdo->prepare('SELECT * FROM services WHERE id = :id');
        $s->execute([':id' => $id]);
        $service = $s->fetch();
        if (!$service) {
            flash('error', 'Service not found.');
            header('Location: ' . ADMIN_URL . '/services.php'); exit;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_verify_or_die();

        $title  = trim($_POST['title'] ?? '');
        $name   = trim($_POST['name']  ?? '');
        $errors = [];
        if ($title === '') $errors[] = 'Title is required.';
        if ($name  === '') $errors[] = 'Name is required.';

        $features_list = [];
        if (!empty($_POST['features_raw'])) {
            $features_list = array_values(array_filter(array_map('trim', explode("\n", $_POST['features_raw']))));
        }

        if ($errors) {
            foreach ($errors as $e) flash('error', $e);
            $service = array_merge((array) $service, $_POST);
            $service['_features_list'] = $features_list;
        } else {
            $slug_raw  = trim($_POST['slug'] ?? '');
            $slug_base = $slug_raw !== '' ? svc_slugify($slug_raw) : svc_slugify($title);
            $slug      = svc_unique_slug($pdo, $slug_base, $service['id'] ?? null);
            $status    = in_array($_POST['status'] ?? '', ['draft', 'published', 'archived'], true)
                            ? $_POST['status'] : 'draft';
            $pub_at    = $status === 'published'
                            ? (($service['published_at'] ?? null) ?: date('Y-m-d H:i:s'))
                            : null;

            $cols = [
                'slug'             => $slug,
                'name'             => $name,
                'title'            => $title,
                'excerpt'          => trim($_POST['excerpt'] ?? ''),
                'description'      => trim($_POST['description'] ?? ''),
                'service_number'   => max(1, (int) ($_POST['service_number'] ?? 1)),
                'icon_class'       => trim($_POST['icon_class'] ?? ''),
                'featured_image'   => trim($_POST['featured_image'] ?? ''),
                'featured_alt'     => trim($_POST['featured_alt'] ?? ''),
                'overview_html'    => trim($_POST['overview_html'] ?? ''),
                'features_html'    => trim($_POST['features_html'] ?? ''),
                'use_cases_html'   => trim($_POST['use_cases_html'] ?? ''),
                'benefits_html'    => trim($_POST['benefits_html'] ?? ''),
                'features_json'    => !empty($features_list) ? json_encode($features_list) : null,
                'meta_title'       => trim($_POST['meta_title'] ?? ''),
                'meta_description' => trim($_POST['meta_description'] ?? ''),
                'meta_keywords'    => trim($_POST['meta_keywords'] ?? ''),
                'og_image'         => trim($_POST['og_image'] ?? ''),
                'schema_json'      => trim($_POST['schema_json'] ?? ''),
                'status'           => $status,
                'published_at'     => $pub_at,
                'display_on_home'  => isset($_POST['display_on_home']) ? 1 : 0,
                'author_id'        => $user['id'],
            ];

            $bind = [];
            foreach ($cols as $k => $v) $bind[":$k"] = $v;

            if (isset($service['id'])) {
                $sets = implode(', ', array_map(fn($k) => "`$k` = :$k", array_keys($cols)));
                $stmt = $pdo->prepare("UPDATE services SET $sets WHERE id = :where_id");
                $bind[':where_id'] = $service['id'];
                $stmt->execute($bind);
                flash('success', 'Service updated.');
                header('Location: ' . ADMIN_URL . '/services.php?action=edit&id=' . $service['id']); exit;
            } else {
                $keys = implode(', ', array_map(fn($k) => "`$k`", array_keys($cols)));
                $phs  = implode(', ', array_map(fn($k) => ":$k", array_keys($cols)));
                $stmt = $pdo->prepare("INSERT INTO services ($keys) VALUES ($phs)");
                $stmt->execute($bind);
                $newId = (int) $pdo->lastInsertId();
                flash('success', 'Service created.');
                header('Location: ' . ADMIN_URL . '/services.php?action=edit&id=' . $newId); exit;
            }
        }
    }

    // Resolve features list for the form
    $features_list = [];
    if (!empty($service['_features_list'])) {
        $features_list = $service['_features_list'];
    } elseif (!empty($service['features_json'])) {
        $features_list = json_decode($service['features_json'], true) ?? [];
    }

    $f = array_merge([
        'id' => null, 'slug' => '', 'name' => '', 'title' => '', 'excerpt' => '',
        'description' => '', 'service_number' => 1, 'icon_class' => '',
        'featured_image' => '', 'featured_alt' => '',
        'overview_html' => '', 'features_html' => '', 'use_cases_html' => '', 'benefits_html' => '',
        'meta_title' => '', 'meta_description' => '', 'meta_keywords' => '', 'og_image' => '',
        'schema_json' => '', 'status' => 'draft', 'display_on_home' => 1, 'published_at' => null,
    ], (array) $service);

    $admin_page_title = $service ? 'Edit Service' : 'New Service';
    $admin_active     = 'services';
    require __DIR__ . '/_header.php';
    ?>

    <div class="admin-page-header">
        <div>
            <h1><?= $service ? 'Edit Service' : 'New Service' ?></h1>
            <?php if ($service): ?>
                <div class="subtitle">Slug: <code class="text-mono"><?= e($f['slug']) ?></code></div>
            <?php endif; ?>
        </div>
        <a href="<?= ADMIN_URL ?>/services.php" class="admin-btn admin-btn--ghost">Back to list</a>
    </div>

    <form method="post" class="admin-form">
        <?= csrf_field() ?>
        <div style="display:grid;grid-template-columns:minmax(0,1fr) 300px;gap:24px;align-items:flex-start;">

            <!-- left column -->
            <div>
                <div class="admin-card">
                    <div class="admin-card__body">
                        <div class="form-row">
                            <label for="name">Service Name *</label>
                            <input type="text" id="name" name="name" required value="<?= attr($f['name']) ?>" placeholder="e.g. Voice AI">
                        </div>
                        <div class="form-row">
                            <label for="title">Display Title *</label>
                            <input type="text" id="title" name="title" required value="<?= attr($f['title']) ?>">
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                            <div class="form-row">
                                <label for="slug">Slug</label>
                                <input type="text" id="slug" name="slug" value="<?= attr($f['slug']) ?>" placeholder="auto-generated">
                            </div>
                            <div class="form-row">
                                <label for="service_number">Display Order</label>
                                <input type="number" id="service_number" name="service_number" min="1" value="<?= (int) $f['service_number'] ?>">
                            </div>
                        </div>
                        <div class="form-row">
                            <label for="excerpt">Short Description *</label>
                            <textarea id="excerpt" name="excerpt" rows="2" required><?= e($f['excerpt']) ?></textarea>
                        </div>
                        <div class="form-row">
                            <label for="description">Full Description</label>
                            <textarea id="description" name="description" rows="4"><?= e($f['description']) ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="admin-card">
                    <div class="admin-card__head">Service Content</div>
                    <div class="admin-card__body">
                        <div class="form-row">
                            <label for="overview_html">Overview / Introduction</label>
                            <textarea id="overview_html" name="overview_html" rows="4"><?= e($f['overview_html']) ?></textarea>
                        </div>
                        <div class="form-row">
                            <label for="features_html">Features &amp; Capabilities</label>
                            <textarea id="features_html" name="features_html" rows="4"><?= e($f['features_html']) ?></textarea>
                        </div>
                        <div class="form-row">
                            <label for="use_cases_html">Use Cases</label>
                            <textarea id="use_cases_html" name="use_cases_html" rows="4"><?= e($f['use_cases_html']) ?></textarea>
                        </div>
                        <div class="form-row">
                            <label for="benefits_html">Benefits</label>
                            <textarea id="benefits_html" name="benefits_html" rows="4"><?= e($f['benefits_html']) ?></textarea>
                        </div>
                        <div class="form-row">
                            <label for="features_raw">Key Features List <span class="text-muted">(one per line, stored as JSON)</span></label>
                            <textarea id="features_raw" name="features_raw" rows="5"
                                placeholder="Real-time processing&#10;Multi-language support&#10;99.9% uptime"><?= e(implode("\n", $features_list)) ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="admin-card">
                    <div class="admin-card__head">SEO</div>
                    <div class="admin-card__body">
                        <div class="form-row">
                            <label for="meta_title">Meta Title</label>
                            <input type="text" id="meta_title" name="meta_title" maxlength="255" value="<?= attr($f['meta_title']) ?>">
                        </div>
                        <div class="form-row">
                            <label for="meta_description">Meta Description</label>
                            <textarea id="meta_description" name="meta_description" rows="2" maxlength="320"><?= e($f['meta_description']) ?></textarea>
                        </div>
                        <div class="form-row">
                            <label for="meta_keywords">Keywords</label>
                            <input type="text" id="meta_keywords" name="meta_keywords" value="<?= attr($f['meta_keywords']) ?>">
                        </div>
                        <div class="form-row">
                            <label for="og_image">OG Image</label>
                            <input type="text" id="og_image" name="og_image" value="<?= attr($f['og_image']) ?>">
                        </div>
                        <div class="form-row">
                            <label for="schema_json">JSON-LD Schema</label>
                            <textarea id="schema_json" name="schema_json" rows="4" class="json-textarea"><?= e($f['schema_json']) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- right column -->
            <div>
                <div class="admin-card">
                    <div class="admin-card__head">Publish</div>
                    <div class="admin-card__body">
                        <div class="form-row">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="draft"     <?= $f['status'] === 'draft'     ? 'selected' : '' ?>>Draft</option>
                                <option value="published" <?= $f['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                                <option value="archived"  <?= $f['status'] === 'archived'  ? 'selected' : '' ?>>Archived</option>
                            </select>
                        </div>
                        <div class="form-row">
                            <label>
                                <input type="checkbox" name="display_on_home" <?= $f['display_on_home'] ? 'checked' : '' ?>>
                                Show on Homepage
                            </label>
                        </div>
                        <?php if ($f['published_at']): ?>
                            <div class="text-muted" style="font-size:12px;">Published <?= e(substr((string) $f['published_at'], 0, 16)) ?></div>
                        <?php endif; ?>
                        <button type="submit" class="admin-btn" style="width:100%;margin-top:10px;">
                            <?= $service ? 'Update Service' : 'Create Service' ?>
                        </button>
                        <a href="<?= ADMIN_URL ?>/services.php" class="admin-btn admin-btn--ghost"
                           style="width:100%;text-align:center;margin-top:8px;display:block;">Cancel</a>
                    </div>
                </div>

                <div class="admin-card">
                    <div class="admin-card__head">Media &amp; Icon</div>
                    <div class="admin-card__body">
                        <div class="form-row">
                            <label for="icon_class">Icon Class</label>
                            <input type="text" id="icon_class" name="icon_class"
                                value="<?= attr($f['icon_class']) ?>" placeholder="flaticon-voice or fas fa-microphone">
                        </div>
                        <div class="form-row">
                            <label for="featured_image">Featured Image URL</label>
                            <input type="text" id="featured_image" name="featured_image" value="<?= attr($f['featured_image']) ?>">
                        </div>
                        <?php if (!empty($f['featured_image'])): ?>
                            <img src="<?= attr($f['featured_image']) ?>"
                                 style="max-width:100%;border-radius:4px;border:1px solid var(--admin-border);" alt="">
                        <?php endif; ?>
                        <div class="form-row" style="margin-top:10px;">
                            <label for="featured_alt">Alt Text</label>
                            <input type="text" id="featured_alt" name="featured_alt" value="<?= attr($f['featured_alt']) ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <?php
    require __DIR__ . '/_footer.php';
    exit;
}

// ── LIST ─────────────────────────────────────────────────────────────────────

$rows = $pdo->query(
    'SELECT id, slug, name, title, service_number, status, display_on_home, updated_at
     FROM services ORDER BY service_number ASC, id ASC LIMIT 200'
)->fetchAll();

$admin_page_title = 'Services';
$admin_active     = 'services';
require __DIR__ . '/_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>Services</h1>
        <div class="subtitle"><?= count($rows) ?> service<?= count($rows) === 1 ? '' : 's' ?></div>
    </div>
    <a href="?action=new" class="admin-btn">New Service</a>
</div>

<div class="admin-card">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Order</th>
                <th>Name</th>
                <th>Title</th>
                <th>Status</th>
                <th>Homepage</th>
                <th class="col-actions">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="6" class="empty-state">
                    <h3>No services yet</h3>
                    <a href="?action=new" class="admin-btn">New Service</a>
                </td></tr>
            <?php else: ?>
                <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= (int) $r['service_number'] ?></td>
                    <td><a href="?action=edit&amp;id=<?= (int) $r['id'] ?>"><?= e($r['name']) ?></a></td>
                    <td><?= e($r['title']) ?></td>
                    <td><span class="pill pill--<?= attr($r['status']) ?>"><?= e($r['status']) ?></span></td>
                    <td><?= $r['display_on_home'] ? '✓' : '' ?></td>
                    <td class="col-actions">
                        <a href="?action=edit&amp;id=<?= (int) $r['id'] ?>" class="admin-btn admin-btn--small">Edit</a>
                        <form method="post" action="?action=delete" style="display:inline;"
                              onsubmit="return confirm('Delete this service permanently?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                            <button type="submit" class="admin-btn admin-btn--danger admin-btn--small">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
