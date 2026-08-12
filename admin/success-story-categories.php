<?php
/**
 * Admin — Success Story Categories (full CRUD)
 *
 * Small lookup-table master for the Success Stories filter/taxonomy
 * (see db/migrations/2026-08-12-001-create-success-story-categories-table.sql).
 * Kept deliberately simple — flat form, no tabs — same shape as the
 * original admin/success-stories.php before it grew repeaters.
 */

require __DIR__ . '/bootstrap.php';

$pdo = db();
if (!$pdo) { die('Database connection failed.'); }

$action = $_GET['action'] ?? 'list';

// ── helpers ──────────────────────────────────────────────────────────────────

function ssc_slugify(string $t): string
{
    $t = strtolower(trim($t));
    if (function_exists('iconv')) {
        $c = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $t);
        if ($c !== false) $t = $c;
    }
    $t = preg_replace('/[^a-z0-9]+/', '-', $t) ?? '';
    return trim($t, '-') ?: 'category';
}

function ssc_unique_slug(PDO $pdo, string $base, ?int $excl = null): string
{
    $slug = $base; $i = 2;
    while (true) {
        $stmt = $pdo->prepare('SELECT id FROM success_story_categories WHERE slug = :s' . ($excl ? ' AND id != :id' : ''));
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
        $pdo->prepare('DELETE FROM success_story_categories WHERE id = :id')->execute([':id' => $del_id]);
        flash('success', 'Category deleted. Stories in this category are unassigned, not deleted.');
    }
    header('Location: ' . ADMIN_URL . '/success-story-categories.php'); exit;
}

// ── EDIT / NEW ───────────────────────────────────────────────────────────────

if ($action === 'new' || $action === 'edit') {
    $id  = (int) ($_GET['id'] ?? 0);
    $cat = null;

    if ($action === 'edit' && $id > 0) {
        $s = $pdo->prepare('SELECT * FROM success_story_categories WHERE id = :id');
        $s->execute([':id' => $id]);
        $cat = $s->fetch();
        if (!$cat) {
            flash('error', 'Category not found.');
            header('Location: ' . ADMIN_URL . '/success-story-categories.php'); exit;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_verify_or_die();

        $name = trim($_POST['name'] ?? '');
        $errors = [];
        if ($name === '') $errors[] = 'Name is required.';

        if ($errors) {
            foreach ($errors as $e) flash('error', $e);
            $cat = array_merge((array) $cat, $_POST);
        } else {
            $slug_raw  = trim($_POST['slug'] ?? '');
            $slug_base = $slug_raw !== '' ? ssc_slugify($slug_raw) : ssc_slugify($name);
            $slug      = ssc_unique_slug($pdo, $slug_base, $cat['id'] ?? null);
            $status    = ($_POST['status'] ?? '') === 'inactive' ? 'inactive' : 'active';
            $sort      = max(1, (int) ($_POST['sort_order'] ?? 1));

            $cols = [
                'name'       => $name,
                'slug'       => $slug,
                'status'     => $status,
                'sort_order' => $sort,
            ];
            $bind = [];
            foreach ($cols as $k => $v) $bind[":$k"] = $v;

            if (isset($cat['id'])) {
                $sets = implode(', ', array_map(fn($k) => "`$k` = :$k", array_keys($cols)));
                $stmt = $pdo->prepare("UPDATE success_story_categories SET $sets WHERE id = :where_id");
                $bind[':where_id'] = $cat['id'];
                $stmt->execute($bind);
                flash('success', 'Category updated.');
            } else {
                $keys = implode(', ', array_map(fn($k) => "`$k`", array_keys($cols)));
                $phs  = implode(', ', array_map(fn($k) => ":$k", array_keys($cols)));
                $stmt = $pdo->prepare("INSERT INTO success_story_categories ($keys) VALUES ($phs)");
                $stmt->execute($bind);
                flash('success', 'Category created.');
            }
            header('Location: ' . ADMIN_URL . '/success-story-categories.php'); exit;
        }
    }

    $f = array_merge([
        'id' => null, 'name' => '', 'slug' => '', 'status' => 'active', 'sort_order' => 1,
    ], (array) $cat);

    $admin_page_title = $cat ? 'Edit Category' : 'New Category';
    $admin_active     = 'success-story-categories';
    require __DIR__ . '/_header.php';
    ?>

    <div class="admin-page-header">
        <div>
            <h1><?= $cat ? 'Edit Category' : 'New Category' ?></h1>
        </div>
        <a href="<?= ADMIN_URL ?>/success-story-categories.php" class="admin-btn admin-btn--ghost">Back to list</a>
    </div>

    <form method="post" class="admin-form">
        <?= csrf_field() ?>
        <div class="admin-card" style="max-width:560px;">
            <div class="admin-card__body">
                <div class="form-row">
                    <label for="name">Name *</label>
                    <input type="text" id="name" name="name" required value="<?= attr($f['name']) ?>">
                </div>
                <div class="form-row">
                    <label for="slug">Slug</label>
                    <input type="text" id="slug" name="slug" value="<?= attr($f['slug']) ?>" placeholder="auto-generated">
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
                        <label for="sort_order">Sort Order</label>
                        <input type="number" id="sort_order" name="sort_order" min="1" value="<?= attr((string) $f['sort_order']) ?>">
                    </div>
                </div>
                <button type="submit" class="admin-btn" style="margin-top:10px;">
                    <?= $cat ? 'Update Category' : 'Create Category' ?>
                </button>
            </div>
        </div>
    </form>

    <?php
    require __DIR__ . '/_footer.php';
    exit;
}

// ── LIST ─────────────────────────────────────────────────────────────────────

$stmt = $pdo->query(
    'SELECT c.*, (SELECT COUNT(*) FROM success_stories s WHERE s.category_id = c.id) AS story_count
     FROM success_story_categories c
     ORDER BY c.sort_order ASC, c.name ASC'
);
$rows = $stmt->fetchAll();

$admin_page_title = 'Success Story Categories';
$admin_active     = 'success-story-categories';
require __DIR__ . '/_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>Success Story Categories</h1>
        <div class="subtitle"><?= count($rows) ?> categor<?= count($rows) === 1 ? 'y' : 'ies' ?></div>
    </div>
    <a href="?action=new" class="admin-btn">New Category</a>
</div>

<div class="admin-card">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Slug</th>
                <th>Status</th>
                <th>Sort Order</th>
                <th>Stories</th>
                <th class="col-actions">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="6" class="empty-state">
                    <h3>No categories yet</h3>
                    <a href="?action=new" class="admin-btn">New Category</a>
                </td></tr>
            <?php else: ?>
                <?php foreach ($rows as $r): ?>
                <tr>
                    <td><a href="?action=edit&amp;id=<?= (int) $r['id'] ?>"><?= e($r['name']) ?></a></td>
                    <td><code class="text-mono"><?= e($r['slug']) ?></code></td>
                    <td><span class="pill pill--<?= attr($r['status']) ?>"><?= e($r['status']) ?></span></td>
                    <td><?= (int) $r['sort_order'] ?></td>
                    <td><?= (int) $r['story_count'] ?></td>
                    <td class="col-actions">
                        <a href="?action=edit&amp;id=<?= (int) $r['id'] ?>" class="admin-btn admin-btn--small">Edit</a>
                        <form method="post" action="?action=delete" style="display:inline;"
                              onsubmit="return confirm('Delete this category? Stories using it will become uncategorized.');">
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
