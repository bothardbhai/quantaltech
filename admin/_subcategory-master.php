<?php
/**
 * Shared admin CRUD for the "subcategory" grouping masters:
 *   - admin/service-subcategories.php  (service_subcategories → services)
 *   - admin/hire-subcategories.php     (hire_subcategories    → hire_pages)
 *
 * The including page defines $subcat_cfg and requires this file (after
 * bootstrap.php). Subcategories are navigation grouping only: their slug is
 * an internal identifier and never part of any page URL/slug/route.
 *
 * $subcat_cfg keys:
 *   table        — subcategory table (whitelisted below)
 *   item_table   — table whose rows carry `subcategory_id`
 *   active       — $admin_active sidebar key
 *   title        — page title, e.g. 'Service Subcategories'
 *   singular     — e.g. 'Service Subcategory'
 *   item_one / item_many — e.g. 'service' / 'services'
 *   items_url / items_label — link back to the item list, e.g. services.php / 'All Services'
 *   example      — placeholder for the name field
 *   nav_label    — where the name appears on the site, for the help text
 */

if (!isset($subcat_cfg) || !is_array($subcat_cfg)) {
    die('Subcategory master is not configured.');
}

// Table names come from the including file, never from the request — still,
// only allow the known pairs.
$subcat_allowed = [
    'service_subcategories' => 'services',
    'hire_subcategories'    => 'hire_pages',
];
if (($subcat_allowed[$subcat_cfg['table']] ?? null) !== $subcat_cfg['item_table']) {
    die('Unknown subcategory table.');
}

$pdo = db();
if (!$pdo) { die('Database connection failed.'); }

$T        = $subcat_cfg['table'];
$IT       = $subcat_cfg['item_table'];
$self_url = ADMIN_URL . '/' . basename($_SERVER['SCRIPT_NAME']);
$singular = $subcat_cfg['singular'];
$one      = $subcat_cfg['item_one'];
$many     = $subcat_cfg['item_many'];
$action   = $_GET['action'] ?? 'list';

// ── helpers ──────────────────────────────────────────────────────────────────

function subcat_slugify(string $t): string
{
    $t = strtolower(trim($t));
    if (function_exists('iconv')) {
        $c = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $t);
        if ($c !== false) $t = $c;
    }
    $t = preg_replace('/[^a-z0-9]+/', '-', $t) ?? '';
    return trim($t, '-') ?: 'subcategory';
}

function subcat_unique_slug(PDO $pdo, string $table, string $base, ?int $excl = null): string
{
    $slug = $base; $i = 2;
    while (true) {
        $stmt = $pdo->prepare("SELECT id FROM `$table` WHERE slug = :s" . ($excl ? ' AND id != :id' : ''));
        $p = [':s' => $slug];
        if ($excl) $p[':id'] = $excl;
        $stmt->execute($p);
        if (!$stmt->fetch()) return $slug;
        $slug = $base . '-' . $i++;
    }
}

// ── DELETE ───────────────────────────────────────────────────────────────────
// Items are never deleted here. A subcategory still assigned to items is
// refused (reassign first); the ON DELETE RESTRICT foreign key is the
// database-level backstop.

if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_or_die();
    $del_id = (int) ($_POST['id'] ?? 0);
    if ($del_id > 0) {
        $cnt = $pdo->prepare("SELECT COUNT(*) FROM `$IT` WHERE subcategory_id = :id");
        $cnt->execute([':id' => $del_id]);
        $in_use = (int) $cnt->fetchColumn();

        if ($in_use > 0) {
            flash('error', "Cannot delete: $in_use " . ($in_use === 1 ? "$one is" : "$many are")
                . ' still assigned to this subcategory. Reassign ' . ($in_use === 1 ? 'it' : 'them')
                . ' to another subcategory first (or set the subcategory to inactive).');
        } else {
            try {
                $pdo->prepare("DELETE FROM `$T` WHERE id = :id")->execute([':id' => $del_id]);
                flash('success', 'Subcategory deleted.');
            } catch (PDOException $e) {
                error_log("Delete $T error: " . $e->getMessage());
                flash('error', "Cannot delete this subcategory because $many are still assigned to it. Reassign them first.");
            }
        }
    }
    header('Location: ' . $self_url); exit;
}

// ── EDIT / NEW ───────────────────────────────────────────────────────────────

if ($action === 'new' || $action === 'edit') {
    $id  = (int) ($_GET['id'] ?? 0);
    $sub = null;

    if ($action === 'edit' && $id > 0) {
        $s = $pdo->prepare("SELECT * FROM `$T` WHERE id = :id LIMIT 1");
        $s->execute([':id' => $id]);
        $sub = $s->fetch() ?: null;
        if (!$sub) {
            flash('error', 'Subcategory not found.');
            header('Location: ' . $self_url); exit;
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
            $slug_base = $slug_raw !== '' ? subcat_slugify($slug_raw) : subcat_slugify($name);
            $slug      = subcat_unique_slug($pdo, $T, $slug_base, isset($sub['id']) ? (int) $sub['id'] : null);
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
                $stmt = $pdo->prepare("UPDATE `$T` SET $sets WHERE id = :where_id");
                $bind[':where_id'] = (int) $sub['id'];
                $stmt->execute($bind);
                flash('success', 'Subcategory updated.');
            } else {
                $keys = implode(', ', array_map(fn($k) => "`$k`", array_keys($cols)));
                $phs  = implode(', ', array_map(fn($k) => ":$k", array_keys($cols)));
                $stmt = $pdo->prepare("INSERT INTO `$T` ($keys) VALUES ($phs)");
                $stmt->execute($bind);
                flash('success', 'Subcategory created.');
            }
            header('Location: ' . $self_url); exit;
        }
    }

    $is_edit = !empty($sub['id']);
    $f = array_merge([
        'id' => null, 'name' => '', 'slug' => '', 'description' => '', 'status' => 'active', 'sort_order' => 1,
    ], (array) $sub);

    $admin_page_title = ($is_edit ? 'Edit ' : 'New ') . $singular;
    $admin_active     = $subcat_cfg['active'];
    require __DIR__ . '/_header.php';
    ?>

    <div class="admin-page-header">
        <div>
            <h1><?= e($admin_page_title) ?></h1>
        </div>
        <a href="<?= attr($self_url) ?>" class="admin-btn admin-btn--ghost">Back to list</a>
    </div>

    <form method="post" class="admin-form">
        <?= csrf_field() ?>
        <div class="admin-card" style="max-width:560px;">
            <div class="admin-card__body">
                <div class="form-row">
                    <label for="name">Subcategory Name *</label>
                    <input type="text" id="name" name="name" required maxlength="100" value="<?= attr((string) $f['name']) ?>" placeholder="<?= attr($subcat_cfg['example']) ?>">
                    <div class="help">Shown as a tab in the <?= e($subcat_cfg['nav_label']) ?> navigation menu.</div>
                </div>
                <div class="form-row">
                    <label for="slug">Slug</label>
                    <input type="text" id="slug" name="slug" maxlength="120" value="<?= attr((string) $f['slug']) ?>" placeholder="auto-generated">
                    <div class="help">Internal identifier only — it is never used in page URLs.</div>
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
    "SELECT c.*, (SELECT COUNT(*) FROM `$IT` i WHERE i.subcategory_id = c.id) AS item_count
     FROM `$T` c
     ORDER BY c.sort_order ASC, c.name ASC"
)->fetchAll();

$unassigned = (int) $pdo->query("SELECT COUNT(*) FROM `$IT` WHERE subcategory_id IS NULL")->fetchColumn();

$admin_page_title = $subcat_cfg['title'];
$admin_active     = $subcat_cfg['active'];
require __DIR__ . '/_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1><?= e($subcat_cfg['title']) ?></h1>
        <div class="subtitle">
            <?= count($rows) ?> subcategor<?= count($rows) === 1 ? 'y' : 'ies' ?>
            &middot; <?= $unassigned ?> <?= e($unassigned === 1 ? $one : $many) ?> without a subcategory
        </div>
    </div>
    <div>
        <a href="<?= attr(ADMIN_URL . '/' . $subcat_cfg['items_url']) ?>" class="admin-btn admin-btn--ghost"><?= e($subcat_cfg['items_label']) ?></a>
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
                <th><?= e(ucfirst($many)) ?></th>
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
                <?php foreach ($rows as $r): $count = (int) $r['item_count']; ?>
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
                                  ? "This subcategory still has $count $many assigned and cannot be deleted until they are reassigned. Continue anyway?"
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
