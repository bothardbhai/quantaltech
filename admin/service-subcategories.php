<?php
/**
 * Admin — Service Subcategories (full CRUD)
 *
 * Grouping master for Services (Build AI, AI Consulting, AI Applications...)
 * used to organize services in the admin and the frontend navigation/mega
 * menu. See db/migrations/2026-11-02-001-create-service-subcategories.sql.
 *
 * IMPORTANT: a subcategory is grouping only. Its slug is an internal
 * identifier and never becomes part of a service URL, slug, canonical or
 * route — services stay at /services/{services.slug}.
 *
 * Same shape as admin/success-story-categories.php.
 */

require __DIR__ . '/bootstrap.php';

$pdo = db();
if (!$pdo) { die('Database connection failed.'); }

$action = $_GET['action'] ?? 'list';

// ── helpers ──────────────────────────────────────────────────────────────────

function ssub_slugify(string $t): string
{
    $t = strtolower(trim($t));
    if (function_exists('iconv')) {
        $c = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $t);
        if ($c !== false) $t = $c;
    }
    $t = preg_replace('/[^a-z0-9]+/', '-', $t) ?? '';
    return trim($t, '-') ?: 'subcategory';
}

function ssub_unique_slug(PDO $pdo, string $base, ?int $excl = null): string
{
    $slug = $base; $i = 2;
    while (true) {
        $stmt = $pdo->prepare('SELECT id FROM service_subcategories WHERE slug = :s' . ($excl ? ' AND id != :id' : ''));
        $p = [':s' => $slug];
        if ($excl) $p[':id'] = $excl;
        $stmt->execute($p);
        if (!$stmt->fetch()) return $slug;
        $slug = $base . '-' . $i++;
    }
}

// ── DELETE ───────────────────────────────────────────────────────────────────
// Services are never deleted here. A subcategory still assigned to services
// is refused (services must be reassigned first); the FK
// fk_services_subcategory (ON DELETE RESTRICT) is the database-level backstop.

if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_or_die();
    $del_id = (int) ($_POST['id'] ?? 0);
    if ($del_id > 0) {
        $cnt = $pdo->prepare('SELECT COUNT(*) FROM services WHERE subcategory_id = :id');
        $cnt->execute([':id' => $del_id]);
        $in_use = (int) $cnt->fetchColumn();

        if ($in_use > 0) {
            flash('error', "Cannot delete: $in_use service" . ($in_use === 1 ? ' is' : 's are')
                . ' still assigned to this subcategory. Reassign ' . ($in_use === 1 ? 'it' : 'them')
                . ' to another subcategory first (or set the subcategory to inactive).');
        } else {
            try {
                $pdo->prepare('DELETE FROM service_subcategories WHERE id = :id')->execute([':id' => $del_id]);
                flash('success', 'Subcategory deleted.');
            } catch (PDOException $e) {
                error_log('Delete service subcategory error: ' . $e->getMessage());
                flash('error', 'Cannot delete this subcategory because services are still assigned to it. Reassign them first.');
            }
        }
    }
    header('Location: ' . ADMIN_URL . '/service-subcategories.php'); exit;
}

// ── EDIT / NEW ───────────────────────────────────────────────────────────────

if ($action === 'new' || $action === 'edit') {
    $id  = (int) ($_GET['id'] ?? 0);
    $sub = null;

    if ($action === 'edit' && $id > 0) {
        $sub = get_service_subcategory($pdo, $id);
        if (!$sub) {
            flash('error', 'Subcategory not found.');
            header('Location: ' . ADMIN_URL . '/service-subcategories.php'); exit;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_verify_or_die();

        $name     = trim((string) ($_POST['name'] ?? ''));
        $desc     = trim((string) ($_POST['description'] ?? ''));
        $sort_raw = trim((string) ($_POST['sort_order'] ?? '1'));

        $errors = [];
        if ($name === '') $errors[] = 'Subcategory Name is required.';
        if (mb_strlen($name) > 100) $errors[] = 'Subcategory Name must be 100 characters or fewer.';
        if (!ctype_digit($sort_raw) || (int) $sort_raw < 1) $errors[] = 'Display Order must be a whole number of 1 or more.';

        if ($errors) {
            foreach ($errors as $e) flash('error', $e);
            $sub = array_merge((array) $sub, $_POST, ['id' => $sub['id'] ?? null]);
        } else {
            $slug_raw  = trim((string) ($_POST['slug'] ?? ''));
            $slug_base = $slug_raw !== '' ? ssub_slugify($slug_raw) : ssub_slugify($name);
            $slug      = ssub_unique_slug($pdo, $slug_base, isset($sub['id']) ? (int) $sub['id'] : null);
            $status    = ($_POST['status'] ?? '') === 'inactive' ? 'inactive' : 'active';

            $cols = [
                'name'        => $name,
                'slug'        => $slug,
                'description' => $desc !== '' ? $desc : null,
                'sort_order'  => (int) $sort_raw,
                'status'      => $status,
            ];
            $bind = [];
            foreach ($cols as $k => $v) $bind[":$k"] = $v;

            if (!empty($sub['id'])) {
                $sets = implode(', ', array_map(fn($k) => "`$k` = :$k", array_keys($cols)));
                $stmt = $pdo->prepare("UPDATE service_subcategories SET $sets WHERE id = :where_id");
                $bind[':where_id'] = (int) $sub['id'];
                $stmt->execute($bind);
                flash('success', 'Subcategory updated.');
            } else {
                $keys = implode(', ', array_map(fn($k) => "`$k`", array_keys($cols)));
                $phs  = implode(', ', array_map(fn($k) => ":$k", array_keys($cols)));
                $stmt = $pdo->prepare("INSERT INTO service_subcategories ($keys) VALUES ($phs)");
                $stmt->execute($bind);
                flash('success', 'Subcategory created.');
            }
            header('Location: ' . ADMIN_URL . '/service-subcategories.php'); exit;
        }
    }

    $is_edit = !empty($sub['id']);
    $f = array_merge([
        'id' => null, 'name' => '', 'slug' => '', 'description' => '', 'status' => 'active', 'sort_order' => 1,
    ], (array) $sub);

    $admin_page_title = $is_edit ? 'Edit Service Subcategory' : 'New Service Subcategory';
    $admin_active     = 'service-subcategories';
    require __DIR__ . '/_header.php';
    ?>

    <div class="admin-page-header">
        <div>
            <h1><?= $is_edit ? 'Edit Service Subcategory' : 'New Service Subcategory' ?></h1>
        </div>
        <a href="<?= ADMIN_URL ?>/service-subcategories.php" class="admin-btn admin-btn--ghost">Back to list</a>
    </div>

    <form method="post" class="admin-form">
        <?= csrf_field() ?>
        <div class="admin-card" style="max-width:560px;">
            <div class="admin-card__body">
                <div class="form-row">
                    <label for="name">Subcategory Name *</label>
                    <input type="text" id="name" name="name" required maxlength="100" value="<?= attr((string) $f['name']) ?>" placeholder="e.g. Build AI">
                    <div class="help">Shown as a column heading in the Services navigation menu.</div>
                </div>
                <div class="form-row">
                    <label for="slug">Slug</label>
                    <input type="text" id="slug" name="slug" maxlength="120" value="<?= attr((string) $f['slug']) ?>" placeholder="auto-generated">
                    <div class="help">Internal identifier only — it is never used in service URLs.</div>
                </div>
                <div class="form-row">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3"><?= e((string) $f['description']) ?></textarea>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div class="form-row">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="active"   <?= $f['status'] === 'active'   ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= $f['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label for="sort_order">Display Order</label>
                        <input type="number" id="sort_order" name="sort_order" min="1" value="<?= attr((string) $f['sort_order']) ?>">
                    </div>
                </div>
                <button type="submit" class="admin-btn" style="margin-top:10px;">
                    <?= $is_edit ? 'Update Subcategory' : 'Create Subcategory' ?>
                </button>
            </div>
        </div>
    </form>

    <script>
    (function () {
        var nameEl = document.getElementById('name');
        var slugEl = document.getElementById('slug');
        if (!nameEl || !slugEl) return;
        var touched = slugEl.value.trim() !== '';
        slugEl.addEventListener('input', function () { touched = slugEl.value.trim() !== ''; });
        nameEl.addEventListener('input', function () {
            if (touched) return;
            slugEl.value = nameEl.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').substring(0, 120);
        });
    })();
    </script>

    <?php
    require __DIR__ . '/_footer.php';
    exit;
}

// ── LIST ─────────────────────────────────────────────────────────────────────

$rows = $pdo->query(
    'SELECT c.*, (SELECT COUNT(*) FROM services s WHERE s.subcategory_id = c.id) AS service_count
     FROM service_subcategories c
     ORDER BY c.sort_order ASC, c.name ASC'
)->fetchAll();

$unassigned = (int) $pdo->query('SELECT COUNT(*) FROM services WHERE subcategory_id IS NULL')->fetchColumn();

$admin_page_title = 'Service Subcategories';
$admin_active     = 'service-subcategories';
require __DIR__ . '/_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>Service Subcategories</h1>
        <div class="subtitle">
            <?= count($rows) ?> subcategor<?= count($rows) === 1 ? 'y' : 'ies' ?>
            &middot; <?= $unassigned ?> service<?= $unassigned === 1 ? '' : 's' ?> without a subcategory
        </div>
    </div>
    <div>
        <a href="<?= ADMIN_URL ?>/services.php" class="admin-btn admin-btn--ghost">All Services</a>
        <a href="?action=new" class="admin-btn">New Subcategory</a>
    </div>
</div>

<div class="admin-card">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Order</th>
                <th>Name</th>
                <th>Slug</th>
                <th>Status</th>
                <th>Services</th>
                <th class="col-actions">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="6" class="empty-state">
                    <h3>No subcategories yet</h3>
                    <a href="?action=new" class="admin-btn">New Subcategory</a>
                </td></tr>
            <?php else: ?>
                <?php foreach ($rows as $r): $count = (int) $r['service_count']; ?>
                <tr>
                    <td><?= (int) $r['sort_order'] ?></td>
                    <td>
                        <a href="?action=edit&amp;id=<?= (int) $r['id'] ?>"><?= e($r['name']) ?></a>
                        <?php if (!empty($r['description'])): ?>
                            <div class="text-muted" style="font-size:12px;"><?= e(mb_strimwidth((string) $r['description'], 0, 90, '…')) ?></div>
                        <?php endif; ?>
                    </td>
                    <td><code class="text-mono"><?= e($r['slug']) ?></code></td>
                    <td><span class="pill pill--<?= attr($r['status']) ?>"><?= e($r['status']) ?></span></td>
                    <td><?= $count ?></td>
                    <td class="col-actions">
                        <a href="?action=edit&amp;id=<?= (int) $r['id'] ?>" class="admin-btn admin-btn--small">Edit</a>
                        <form method="post" action="?action=delete" style="display:inline;"
                              onsubmit="return confirm(<?= attr(json_encode($count > 0
                                  ? "This subcategory still has $count service(s) assigned and cannot be deleted until they are reassigned. Continue anyway?"
                                  : 'Delete this subcategory? This cannot be undone.')) ?>);">
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
