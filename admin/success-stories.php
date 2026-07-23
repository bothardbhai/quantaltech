<?php
/**
 * Admin — Success Stories (full CRUD)
 */

require __DIR__ . '/bootstrap.php';

$pdo  = db();
if (!$pdo) { die('Database connection failed.'); }

$action = $_GET['action'] ?? 'list';
$user   = auth_user();

// ── helpers ──────────────────────────────────────────────────────────────────

function story_slugify(string $t): string
{
    $t = strtolower(trim($t));
    if (function_exists('iconv')) {
        $c = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $t);
        if ($c !== false) $t = $c;
    }
    $t = preg_replace('/[^a-z0-9]+/', '-', $t) ?? '';
    return trim($t, '-') ?: 'story';
}

function story_unique_slug(PDO $pdo, string $base, ?int $excl = null): string
{
    $slug = $base; $i = 2;
    while (true) {
        $stmt = $pdo->prepare('SELECT id FROM success_stories WHERE slug = :s' . ($excl ? ' AND id != :id' : ''));
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
        $pdo->prepare('DELETE FROM success_stories WHERE id = :id')->execute([':id' => $del_id]);
        flash('success', 'Success story deleted.');
    }
    header('Location: ' . ADMIN_URL . '/success-stories.php'); exit;
}

// ── EDIT / NEW ───────────────────────────────────────────────────────────────

if ($action === 'new' || $action === 'edit') {
    $id    = (int) ($_GET['id'] ?? 0);
    $story = null;

    if ($action === 'edit' && $id > 0) {
        $s = $pdo->prepare('SELECT * FROM success_stories WHERE id = :id');
        $s->execute([':id' => $id]);
        $story = $s->fetch();
        if (!$story) {
            flash('error', 'Success story not found.');
            header('Location: ' . ADMIN_URL . '/success-stories.php'); exit;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_verify_or_die();

        $title        = trim($_POST['title'] ?? '');
        $company_name = trim($_POST['company_name'] ?? '');
        $industry     = trim($_POST['industry'] ?? '');
        $errors       = [];
        if ($title        === '') $errors[] = 'Title is required.';
        if ($company_name === '') $errors[] = 'Company name is required.';
        if ($industry     === '') $errors[] = 'Industry is required.';

        if ($errors) {
            foreach ($errors as $e) flash('error', $e);
            $story = array_merge((array) $story, $_POST);
        } else {
            $slug_raw  = trim($_POST['slug'] ?? '');
            $slug_base = $slug_raw !== '' ? story_slugify($slug_raw) : story_slugify($title);
            $slug      = story_unique_slug($pdo, $slug_base, $story['id'] ?? null);
            $status    = in_array($_POST['status'] ?? '', ['draft', 'published', 'archived'], true)
                            ? $_POST['status'] : 'draft';
            $pub_at    = $status === 'published'
                            ? (($story['published_at'] ?? null) ?: date('Y-m-d H:i:s'))
                            : null;

            $cols = [
                'slug'             => $slug,
                'title'            => $title,
                'excerpt'          => trim($_POST['excerpt'] ?? ''),
                'body_html'        => trim($_POST['body_html'] ?? ''),
                'company_name'     => $company_name,
                'company_website'  => trim($_POST['company_website'] ?? ''),
                'company_logo'     => trim($_POST['company_logo'] ?? ''),
                'industry'         => $industry,
                'company_size'     => trim($_POST['company_size'] ?? ''),
                'challenge_html'   => trim($_POST['challenge_html'] ?? ''),
                'solution_html'    => trim($_POST['solution_html'] ?? ''),
                'results_html'     => trim($_POST['results_html'] ?? ''),
                'featured_image'   => trim($_POST['featured_image'] ?? ''),
                'featured_alt'     => trim($_POST['featured_alt'] ?? ''),
                'client_name'      => trim($_POST['client_name'] ?? ''),
                'client_title'     => trim($_POST['client_title'] ?? ''),
                'client_image'     => trim($_POST['client_image'] ?? ''),
                'meta_title'       => trim($_POST['meta_title'] ?? ''),
                'meta_description' => trim($_POST['meta_description'] ?? ''),
                'meta_keywords'    => trim($_POST['meta_keywords'] ?? ''),
                'og_image'         => trim($_POST['og_image'] ?? ''),
                'schema_json'      => trim($_POST['schema_json'] ?? ''),
                'status'           => $status,
                'published_at'     => $pub_at,
                'featured'         => isset($_POST['featured']) ? 1 : 0,
                'author_id'        => $user['id'],
            ];

            $bind = [];
            foreach ($cols as $k => $v) $bind[":$k"] = $v;

            if (isset($story['id'])) {
                $sets = implode(', ', array_map(fn($k) => "`$k` = :$k", array_keys($cols)));
                $stmt = $pdo->prepare("UPDATE success_stories SET $sets WHERE id = :where_id");
                $bind[':where_id'] = $story['id'];
                $stmt->execute($bind);
                flash('success', 'Success story updated.');
                header('Location: ' . ADMIN_URL . '/success-stories.php?action=edit&id=' . $story['id']); exit;
            } else {
                $keys = implode(', ', array_map(fn($k) => "`$k`", array_keys($cols)));
                $phs  = implode(', ', array_map(fn($k) => ":$k", array_keys($cols)));
                $stmt = $pdo->prepare("INSERT INTO success_stories ($keys) VALUES ($phs)");
                $stmt->execute($bind);
                $newId = (int) $pdo->lastInsertId();
                flash('success', 'Success story created.');
                header('Location: ' . ADMIN_URL . '/success-stories.php?action=edit&id=' . $newId); exit;
            }
        }
    }

    $f = array_merge([
        'id' => null, 'slug' => '', 'title' => '', 'excerpt' => '', 'body_html' => '',
        'company_name' => '', 'company_website' => '', 'company_logo' => '',
        'industry' => '', 'company_size' => '',
        'challenge_html' => '', 'solution_html' => '', 'results_html' => '',
        'featured_image' => '', 'featured_alt' => '',
        'client_name' => '', 'client_title' => '', 'client_image' => '',
        'meta_title' => '', 'meta_description' => '', 'meta_keywords' => '',
        'og_image' => '', 'schema_json' => '', 'status' => 'draft',
        'featured' => 0, 'published_at' => null,
    ], (array) $story);

    $admin_page_title = $story ? 'Edit Success Story' : 'New Success Story';
    $admin_active     = 'success-stories';
    require __DIR__ . '/_header.php';
    ?>

    <div class="admin-page-header">
        <div>
            <h1><?= $story ? 'Edit Success Story' : 'New Success Story' ?></h1>
            <?php if ($story): ?>
                <div class="subtitle">Slug: <code class="text-mono"><?= e($f['slug']) ?></code></div>
            <?php endif; ?>
        </div>
        <a href="<?= ADMIN_URL ?>/success-stories.php" class="admin-btn admin-btn--ghost">Back to list</a>
    </div>

    <form method="post" class="admin-form">
        <?= csrf_field() ?>
        <div style="display:grid;grid-template-columns:minmax(0,1fr) 300px;gap:24px;align-items:flex-start;">

            <!-- left column -->
            <div>
                <div class="admin-card">
                    <div class="admin-card__body">
                        <div class="form-row">
                            <label for="title">Title *</label>
                            <input type="text" id="title" name="title" required value="<?= attr($f['title']) ?>">
                        </div>
                        <div class="form-row">
                            <label for="slug">Slug</label>
                            <input type="text" id="slug" name="slug" value="<?= attr($f['slug']) ?>" placeholder="auto-generated">
                        </div>
                        <div class="form-row">
                            <label for="excerpt">Excerpt *</label>
                            <textarea id="excerpt" name="excerpt" rows="2" required><?= e($f['excerpt']) ?></textarea>
                        </div>
                        <div class="form-row">
                            <label for="body_html">Case Study Details</label>
                            <textarea id="body_html" name="body_html" rows="6"><?= e($f['body_html']) ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="admin-card">
                    <div class="admin-card__head">Company</div>
                    <div class="admin-card__body">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                            <div class="form-row">
                                <label for="company_name">Company Name *</label>
                                <input type="text" id="company_name" name="company_name" required value="<?= attr($f['company_name']) ?>">
                            </div>
                            <div class="form-row">
                                <label for="industry">Industry *</label>
                                <input type="text" id="industry" name="industry" required value="<?= attr($f['industry']) ?>">
                            </div>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                            <div class="form-row">
                                <label for="company_website">Website</label>
                                <input type="text" id="company_website" name="company_website" value="<?= attr($f['company_website']) ?>">
                            </div>
                            <div class="form-row">
                                <label for="company_size">Company Size</label>
                                <input type="text" id="company_size" name="company_size" placeholder="e.g. 50–200 employees" value="<?= attr($f['company_size']) ?>">
                            </div>
                        </div>
                        <div class="form-row">
                            <label for="company_logo">Company Logo URL</label>
                            <input type="text" id="company_logo" name="company_logo" value="<?= attr($f['company_logo']) ?>">
                        </div>
                    </div>
                </div>

                <div class="admin-card">
                    <div class="admin-card__head">Challenge, Solution &amp; Results</div>
                    <div class="admin-card__body">
                        <div class="form-row">
                            <label for="challenge_html">The Challenge</label>
                            <textarea id="challenge_html" name="challenge_html" rows="4"><?= e($f['challenge_html']) ?></textarea>
                        </div>
                        <div class="form-row">
                            <label for="solution_html">Our Solution</label>
                            <textarea id="solution_html" name="solution_html" rows="4"><?= e($f['solution_html']) ?></textarea>
                        </div>
                        <div class="form-row">
                            <label for="results_html">Results &amp; Impact</label>
                            <textarea id="results_html" name="results_html" rows="4"><?= e($f['results_html']) ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="admin-card">
                    <div class="admin-card__head">Client Testimonial</div>
                    <div class="admin-card__body">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                            <div class="form-row">
                                <label for="client_name">Client Name</label>
                                <input type="text" id="client_name" name="client_name" value="<?= attr($f['client_name']) ?>">
                            </div>
                            <div class="form-row">
                                <label for="client_title">Client Title</label>
                                <input type="text" id="client_title" name="client_title" value="<?= attr($f['client_title']) ?>">
                            </div>
                        </div>
                        <div class="form-row">
                            <label for="client_image">Client Photo URL</label>
                            <input type="text" id="client_image" name="client_image" value="<?= attr($f['client_image']) ?>">
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
                            <textarea id="schema_json" name="schema_json" rows="3" class="json-textarea"><?= e($f['schema_json']) ?></textarea>
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
                                <input type="checkbox" name="featured" <?= $f['featured'] ? 'checked' : '' ?>>
                                Featured on Homepage
                            </label>
                        </div>
                        <?php if ($f['published_at']): ?>
                            <div class="text-muted" style="font-size:12px;">Published <?= e(substr((string) $f['published_at'], 0, 16)) ?></div>
                        <?php endif; ?>
                        <button type="submit" class="admin-btn" style="width:100%;margin-top:10px;">
                            <?= $story ? 'Update Story' : 'Create Story' ?>
                        </button>
                        <a href="<?= ADMIN_URL ?>/success-stories.php" class="admin-btn admin-btn--ghost"
                           style="width:100%;text-align:center;margin-top:8px;display:block;">Cancel</a>
                    </div>
                </div>

                <div class="admin-card">
                    <div class="admin-card__head">Media</div>
                    <div class="admin-card__body">
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

$status_filter = $_GET['status'] ?? '';
$where  = ''; $params = [];
if (in_array($status_filter, ['draft', 'published', 'archived'], true)) {
    $where = ' WHERE status = :st';
    $params[':st'] = $status_filter;
}

$stmt = $pdo->prepare(
    'SELECT id, slug, title, company_name, industry, status, featured, updated_at
     FROM success_stories' . $where . '
     ORDER BY COALESCE(published_at, updated_at) DESC LIMIT 200'
);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$admin_page_title = 'Success Stories';
$admin_active     = 'success-stories';
require __DIR__ . '/_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>Success Stories</h1>
        <div class="subtitle"><?= count($rows) ?> stor<?= count($rows) === 1 ? 'y' : 'ies' ?></div>
    </div>
    <a href="?action=new" class="admin-btn">New Story</a>
</div>

<div style="margin-bottom:14px;">
    <a href="?" class="<?= $status_filter === '' ? 'admin-btn admin-btn--small' : 'admin-btn admin-btn--ghost admin-btn--small' ?>">All</a>
    <a href="?status=published" class="<?= $status_filter === 'published' ? 'admin-btn admin-btn--small' : 'admin-btn admin-btn--ghost admin-btn--small' ?>">Published</a>
    <a href="?status=draft" class="<?= $status_filter === 'draft' ? 'admin-btn admin-btn--small' : 'admin-btn admin-btn--ghost admin-btn--small' ?>">Drafts</a>
</div>

<div class="admin-card">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Company</th>
                <th>Industry</th>
                <th>Status</th>
                <th>Featured</th>
                <th class="col-actions">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="6" class="empty-state">
                    <h3>No success stories yet</h3>
                    <a href="?action=new" class="admin-btn">New Story</a>
                </td></tr>
            <?php else: ?>
                <?php foreach ($rows as $r): ?>
                <tr>
                    <td><a href="?action=edit&amp;id=<?= (int) $r['id'] ?>"><?= e($r['title']) ?></a></td>
                    <td><?= e($r['company_name']) ?></td>
                    <td><?= e($r['industry']) ?></td>
                    <td><span class="pill pill--<?= attr($r['status']) ?>"><?= e($r['status']) ?></span></td>
                    <td><?= $r['featured'] ? '★' : '' ?></td>
                    <td class="col-actions">
                        <a href="?action=edit&amp;id=<?= (int) $r['id'] ?>" class="admin-btn admin-btn--small">Edit</a>
                        <form method="post" action="?action=delete" style="display:inline;"
                              onsubmit="return confirm('Delete this story permanently?');">
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
