<?php
/**
 * Admin — Webinars (full CRUD)
 */

require __DIR__ . '/bootstrap.php';

$pdo  = db();
if (!$pdo) { die('Database connection failed.'); }

$action = $_GET['action'] ?? 'list';
$user   = auth_user();

// ── helpers ──────────────────────────────────────────────────────────────────

function web_slugify(string $t): string
{
    $t = strtolower(trim($t));
    if (function_exists('iconv')) {
        $c = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $t);
        if ($c !== false) $t = $c;
    }
    $t = preg_replace('/[^a-z0-9]+/', '-', $t) ?? '';
    return trim($t, '-') ?: 'webinar';
}

function web_unique_slug(PDO $pdo, string $base, ?int $excl = null): string
{
    $slug = $base; $i = 2;
    while (true) {
        $stmt = $pdo->prepare('SELECT id FROM webinars WHERE slug = :s' . ($excl ? ' AND id != :id' : ''));
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
        $pdo->prepare('DELETE FROM webinars WHERE id = :id')->execute([':id' => $del_id]);
        flash('success', 'Webinar deleted.');
    }
    header('Location: ' . ADMIN_URL . '/webinars.php'); exit;
}

// ── EDIT / NEW ───────────────────────────────────────────────────────────────

if ($action === 'new' || $action === 'edit') {
    $id      = (int) ($_GET['id'] ?? 0);
    $webinar = null;

    if ($action === 'edit' && $id > 0) {
        $s = $pdo->prepare('SELECT * FROM webinars WHERE id = :id');
        $s->execute([':id' => $id]);
        $webinar = $s->fetch();
        if (!$webinar) {
            flash('error', 'Webinar not found.');
            header('Location: ' . ADMIN_URL . '/webinars.php'); exit;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_verify_or_die();

        $title  = trim($_POST['title'] ?? '');
        $sched  = trim($_POST['scheduled_at'] ?? '');
        $errors = [];
        if ($title === '') $errors[] = 'Title is required.';
        if ($sched === '') $errors[] = 'Scheduled date/time is required.';

        if ($errors) {
            foreach ($errors as $e) flash('error', $e);
            $webinar = array_merge((array) $webinar, $_POST);
        } else {
            $slug_raw  = trim($_POST['slug'] ?? '');
            $slug_base = $slug_raw !== '' ? web_slugify($slug_raw) : web_slugify($title);
            $slug      = web_unique_slug($pdo, $slug_base, $webinar['id'] ?? null);
            $status    = in_array($_POST['status'] ?? '', ['draft', 'published', 'archived', 'completed', 'cancelled'], true)
                            ? $_POST['status'] : 'draft';
            $pub_at    = $status === 'published'
                            ? (($webinar['published_at'] ?? null) ?: date('Y-m-d H:i:s'))
                            : null;
            $sched_db  = str_replace('T', ' ', $sched);
            $max_att   = trim($_POST['max_attendees'] ?? '');

            $cols = [
                'slug'             => $slug,
                'title'            => $title,
                'excerpt'          => trim($_POST['excerpt'] ?? ''),
                'description'      => trim($_POST['description'] ?? ''),
                'speaker_name'     => trim($_POST['speaker_name'] ?? ''),
                'speaker_title'    => trim($_POST['speaker_title'] ?? ''),
                'speaker_bio'      => trim($_POST['speaker_bio'] ?? ''),
                'speaker_image'    => trim($_POST['speaker_image'] ?? ''),
                'featured_image'   => trim($_POST['featured_image'] ?? ''),
                'featured_alt'     => trim($_POST['featured_alt'] ?? ''),
                'scheduled_at'     => $sched_db,
                'duration_minutes' => max(15, (int) ($_POST['duration_minutes'] ?? 60)),
                'timezone'         => trim($_POST['timezone'] ?? 'UTC') ?: 'UTC',
                'registration_url' => trim($_POST['registration_url'] ?? ''),
                'max_attendees'    => ($max_att !== '' && (int) $max_att > 0) ? (int) $max_att : null,
                'content_html'     => trim($_POST['content_html'] ?? ''),
                'meta_title'       => trim($_POST['meta_title'] ?? ''),
                'meta_description' => trim($_POST['meta_description'] ?? ''),
                'meta_keywords'    => trim($_POST['meta_keywords'] ?? ''),
                'og_image'         => trim($_POST['og_image'] ?? ''),
                'schema_json'      => trim($_POST['schema_json'] ?? ''),
                'status'           => $status,
                'published_at'     => $pub_at,
                'author_id'        => $user['id'],
            ];

            $bind = [];
            foreach ($cols as $k => $v) $bind[":$k"] = $v;

            if (isset($webinar['id'])) {
                $sets = implode(', ', array_map(fn($k) => "`$k` = :$k", array_keys($cols)));
                $stmt = $pdo->prepare("UPDATE webinars SET $sets WHERE id = :where_id");
                $bind[':where_id'] = $webinar['id'];
                $stmt->execute($bind);
                flash('success', 'Webinar updated.');
                header('Location: ' . ADMIN_URL . '/webinars.php?action=edit&id=' . $webinar['id']); exit;
            } else {
                $keys = implode(', ', array_map(fn($k) => "`$k`", array_keys($cols)));
                $phs  = implode(', ', array_map(fn($k) => ":$k", array_keys($cols)));
                $stmt = $pdo->prepare("INSERT INTO webinars ($keys) VALUES ($phs)");
                $stmt->execute($bind);
                $newId = (int) $pdo->lastInsertId();
                flash('success', 'Webinar created.');
                header('Location: ' . ADMIN_URL . '/webinars.php?action=edit&id=' . $newId); exit;
            }
        }
    }

    $f = array_merge([
        'id' => null, 'slug' => '', 'title' => '', 'excerpt' => '', 'description' => '',
        'speaker_name' => '', 'speaker_title' => '', 'speaker_bio' => '', 'speaker_image' => '',
        'featured_image' => '', 'featured_alt' => '',
        'scheduled_at' => '', 'duration_minutes' => 60, 'timezone' => 'UTC',
        'registration_url' => '', 'max_attendees' => '', 'content_html' => '',
        'meta_title' => '', 'meta_description' => '', 'meta_keywords' => '',
        'og_image' => '', 'schema_json' => '', 'status' => 'draft', 'published_at' => null,
    ], (array) $webinar);

    $sched_local = $f['scheduled_at']
        ? str_replace(' ', 'T', substr((string) $f['scheduled_at'], 0, 16))
        : '';

    $admin_page_title = $webinar ? 'Edit Webinar' : 'New Webinar';
    $admin_active     = 'webinars';
    require __DIR__ . '/_header.php';
    ?>

    <div class="admin-page-header">
        <div>
            <h1><?= $webinar ? 'Edit Webinar' : 'New Webinar' ?></h1>
            <?php if ($webinar): ?>
                <div class="subtitle">Slug: <code class="text-mono"><?= e($f['slug']) ?></code></div>
            <?php endif; ?>
        </div>
        <a href="<?= ADMIN_URL ?>/webinars.php" class="admin-btn admin-btn--ghost">Back to list</a>
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
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="4"><?= e($f['description']) ?></textarea>
                        </div>
                        <div class="form-row">
                            <label for="content_html">Full Content</label>
                            <textarea id="content_html" name="content_html" rows="6"><?= e($f['content_html']) ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="admin-card">
                    <div class="admin-card__head">Speaker</div>
                    <div class="admin-card__body">
                        <div class="form-row">
                            <label for="speaker_name">Speaker Name *</label>
                            <input type="text" id="speaker_name" name="speaker_name" required value="<?= attr($f['speaker_name']) ?>">
                        </div>
                        <div class="form-row">
                            <label for="speaker_title">Speaker Title / Role</label>
                            <input type="text" id="speaker_title" name="speaker_title" value="<?= attr($f['speaker_title']) ?>">
                        </div>
                        <div class="form-row">
                            <label for="speaker_bio">Speaker Bio</label>
                            <textarea id="speaker_bio" name="speaker_bio" rows="3"><?= e($f['speaker_bio']) ?></textarea>
                        </div>
                        <div class="form-row">
                            <label for="speaker_image">Speaker Photo URL</label>
                            <input type="text" id="speaker_image" name="speaker_image" value="<?= attr($f['speaker_image']) ?>">
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
                                <option value="completed" <?= $f['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="cancelled" <?= $f['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                <option value="archived"  <?= $f['status'] === 'archived'  ? 'selected' : '' ?>>Archived</option>
                            </select>
                        </div>
                        <?php if ($f['published_at']): ?>
                            <div class="text-muted" style="font-size:12px;">Published <?= e(substr((string) $f['published_at'], 0, 16)) ?></div>
                        <?php endif; ?>
                        <button type="submit" class="admin-btn" style="width:100%;margin-top:10px;">
                            <?= $webinar ? 'Update Webinar' : 'Create Webinar' ?>
                        </button>
                        <a href="<?= ADMIN_URL ?>/webinars.php" class="admin-btn admin-btn--ghost"
                           style="width:100%;text-align:center;margin-top:8px;display:block;">Cancel</a>
                    </div>
                </div>

                <div class="admin-card">
                    <div class="admin-card__head">Event Details</div>
                    <div class="admin-card__body">
                        <div class="form-row">
                            <label for="scheduled_at">Date &amp; Time *</label>
                            <input type="datetime-local" id="scheduled_at" name="scheduled_at" required value="<?= attr($sched_local) ?>">
                        </div>
                        <div class="form-row">
                            <label for="duration_minutes">Duration (minutes)</label>
                            <input type="number" id="duration_minutes" name="duration_minutes" min="15" value="<?= (int) $f['duration_minutes'] ?>">
                        </div>
                        <div class="form-row">
                            <label for="timezone">Timezone</label>
                            <input type="text" id="timezone" name="timezone" value="<?= attr($f['timezone']) ?>" placeholder="UTC">
                        </div>
                        <div class="form-row">
                            <label for="registration_url">Registration URL</label>
                            <input type="text" id="registration_url" name="registration_url" value="<?= attr($f['registration_url']) ?>">
                        </div>
                        <div class="form-row">
                            <label for="max_attendees">Max Attendees</label>
                            <input type="number" id="max_attendees" name="max_attendees" min="1"
                                   value="<?= attr((string) $f['max_attendees']) ?>" placeholder="unlimited">
                        </div>
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
if (in_array($status_filter, ['draft', 'published', 'archived', 'completed', 'cancelled'], true)) {
    $where = ' WHERE status = :st';
    $params[':st'] = $status_filter;
}

$stmt = $pdo->prepare(
    'SELECT id, slug, title, speaker_name, scheduled_at, status, updated_at
     FROM webinars' . $where . ' ORDER BY scheduled_at DESC LIMIT 200'
);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$admin_page_title = 'Webinars';
$admin_active     = 'webinars';
require __DIR__ . '/_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>Webinars</h1>
        <div class="subtitle"><?= count($rows) ?> webinar<?= count($rows) === 1 ? '' : 's' ?></div>
    </div>
    <a href="?action=new" class="admin-btn">New Webinar</a>
</div>

<div style="margin-bottom:14px;">
    <a href="?" class="<?= $status_filter === '' ? 'admin-btn admin-btn--small' : 'admin-btn admin-btn--ghost admin-btn--small' ?>">All</a>
    <a href="?status=published" class="<?= $status_filter === 'published' ? 'admin-btn admin-btn--small' : 'admin-btn admin-btn--ghost admin-btn--small' ?>">Published</a>
    <a href="?status=draft" class="<?= $status_filter === 'draft' ? 'admin-btn admin-btn--small' : 'admin-btn admin-btn--ghost admin-btn--small' ?>">Drafts</a>
    <a href="?status=completed" class="<?= $status_filter === 'completed' ? 'admin-btn admin-btn--small' : 'admin-btn admin-btn--ghost admin-btn--small' ?>">Completed</a>
</div>

<div class="admin-card">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Speaker</th>
                <th>Scheduled</th>
                <th>Status</th>
                <th class="col-actions">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="5" class="empty-state">
                    <h3>No webinars yet</h3>
                    <a href="?action=new" class="admin-btn">New Webinar</a>
                </td></tr>
            <?php else: ?>
                <?php foreach ($rows as $r): ?>
                <tr>
                    <td><a href="?action=edit&amp;id=<?= (int) $r['id'] ?>"><?= e($r['title']) ?></a></td>
                    <td><?= e($r['speaker_name']) ?></td>
                    <td class="no-wrap text-muted"><?= $r['scheduled_at'] ? e(substr($r['scheduled_at'], 0, 16)) : '—' ?></td>
                    <td><span class="pill pill--<?= attr($r['status']) ?>"><?= e($r['status']) ?></span></td>
                    <td class="col-actions">
                        <a href="?action=edit&amp;id=<?= (int) $r['id'] ?>" class="admin-btn admin-btn--small">Edit</a>
                        <form method="post" action="?action=delete" style="display:inline;"
                              onsubmit="return confirm('Delete this webinar permanently?');">
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
