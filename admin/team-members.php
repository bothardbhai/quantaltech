<?php
/**
 * Admin — Team Members (full CRUD)
 *
 * A global, admin-managed roster of team/engineer profiles — independent
 * of any single hire_pages row — so one profile can be entered once and
 * attached to multiple places (first consumer: Success Stories' "Meet the
 * Expert" section). Deliberately flat, no tabs — same shape as
 * admin/success-story-categories.php.
 */

require __DIR__ . '/bootstrap.php';

$pdo = db();
if (!$pdo) { die('Database connection failed.'); }

$action = $_GET['action'] ?? 'list';

// ── helpers ──────────────────────────────────────────────────────────────────

/** "One tag per line, or comma-separated" -> array of strings. */
function tm_expertise_to_array(string $raw): array
{
    $parts = preg_split('/[\n,]+/', $raw) ?: [];
    return array_values(array_filter(array_map('trim', $parts), static fn(string $s): bool => $s !== ''));
}

// ── DELETE ───────────────────────────────────────────────────────────────────

if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_or_die();
    $del_id = (int) ($_POST['id'] ?? 0);
    if ($del_id > 0) {
        $pdo->prepare('DELETE FROM team_members WHERE id = :id')->execute([':id' => $del_id]);
        flash('success', 'Team member deleted. Any story featuring them will simply hide that section.');
    }
    header('Location: ' . ADMIN_URL . '/team-members.php'); exit;
}

// ── EDIT / NEW ───────────────────────────────────────────────────────────────

if ($action === 'new' || $action === 'edit') {
    $id     = (int) ($_GET['id'] ?? 0);
    $member = null;

    if ($action === 'edit' && $id > 0) {
        $s = $pdo->prepare('SELECT * FROM team_members WHERE id = :id');
        $s->execute([':id' => $id]);
        $member = $s->fetch();
        if (!$member) {
            flash('error', 'Team member not found.');
            header('Location: ' . ADMIN_URL . '/team-members.php'); exit;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_verify_or_die();

        $name = trim((string) ($_POST['name'] ?? ''));
        $errors = [];
        if ($name === '') { $errors[] = 'Name is required.'; }

        if ($errors) {
            foreach ($errors as $e) { flash('error', $e); }
            $member = array_merge((array) $member, $_POST);
        } else {
            $status = ($_POST['status'] ?? '') === 'inactive' ? 'inactive' : 'active';
            $sort   = max(1, (int) ($_POST['sort_order'] ?? 1));

            $cols = [
                'name'            => $name,
                'designation'     => trim((string) ($_POST['designation'] ?? '')),
                'experience_text' => strip_wrapping_p(sanitize_html_fragment((string) ($_POST['experience_text'] ?? ''))),
                'expertise_json'  => json_encode(tm_expertise_to_array((string) ($_POST['expertise'] ?? ''))),
                'image'           => trim((string) ($_POST['image'] ?? '')),
                'profile_url'     => trim((string) ($_POST['profile_url'] ?? '')),
                'status'          => $status,
                'sort_order'      => $sort,
            ];
            $bind = [];
            foreach ($cols as $k => $v) { $bind[":$k"] = $v; }

            if (isset($member['id'])) {
                $sets = implode(', ', array_map(fn($k) => "`$k` = :$k", array_keys($cols)));
                $stmt = $pdo->prepare("UPDATE team_members SET $sets WHERE id = :where_id");
                $bind[':where_id'] = $member['id'];
                $stmt->execute($bind);
                flash('success', 'Team member updated.');
            } else {
                $keys = implode(', ', array_map(fn($k) => "`$k`", array_keys($cols)));
                $phs  = implode(', ', array_map(fn($k) => ":$k", array_keys($cols)));
                $stmt = $pdo->prepare("INSERT INTO team_members ($keys) VALUES ($phs)");
                $stmt->execute($bind);
                flash('success', 'Team member created.');
            }
            header('Location: ' . ADMIN_URL . '/team-members.php'); exit;
        }
    }

    $f = array_merge([
        'id' => null, 'name' => '', 'designation' => '', 'experience_text' => '',
        'expertise_json' => null, 'image' => '', 'profile_url' => '',
        'status' => 'active', 'sort_order' => 1,
    ], (array) $member);
    $expertise_list = svc_json_decode($f['expertise_json'] ?? null);

    $admin_page_title = $member ? 'Edit Team Member' : 'New Team Member';
    $admin_active     = 'team-members';
    require __DIR__ . '/_header.php';
    ?>

    <div class="admin-page-header">
        <div>
            <h1><?= $member ? 'Edit Team Member' : 'New Team Member' ?></h1>
        </div>
        <a href="<?= ADMIN_URL ?>/team-members.php" class="admin-btn admin-btn--ghost">Back to list</a>
    </div>

    <form method="post" class="admin-form">
        <?= csrf_field() ?>
        <div class="admin-card" style="max-width:640px;">
            <div class="admin-card__body">
                <div class="form-row">
                    <label for="name">Name *</label>
                    <input type="text" id="name" name="name" required value="<?= attr($f['name']) ?>" placeholder="e.g. Meet Nagadia">
                </div>
                <div class="form-row">
                    <label for="designation">Designation</label>
                    <input type="text" id="designation" name="designation" value="<?= attr($f['designation']) ?>" placeholder="Senior AI Engineer and Developer - Quantal AI">
                </div>
                <div class="form-row">
                    <label for="experience_text">Experience <span class="text-muted">(HTML allowed — links, bullet/numbered lists)</span></label>
                    <textarea id="experience_text" name="experience_text" rows="4" placeholder="4+ years of experience across Artificial Intelligence, Machine Learning, NLP..."><?= e($f['experience_text']) ?></textarea>
                </div>
                <div class="form-row">
                    <label for="expertise">Expertise Tags <span class="text-muted">(comma separated — shown as pills)</span></label>
                    <input type="text" id="expertise" name="expertise" value="<?= attr(implode(', ', $expertise_list)) ?>" placeholder="AI, Machine Learning, NLP, LLMs, RAG, OpenAI, Python, SQL">
                </div>
                <div class="form-row">
                    <label for="image">Photo <span class="text-muted">(copy a path from the Media Library, optional)</span></label>
                    <input type="text" id="image" name="image" value="<?= attr($f['image']) ?>" placeholder="/uploads/team/nagadia.jpg">
                </div>
                <div class="form-row">
                    <label for="profile_url">Profile Link <span class="text-muted">(optional — e.g. LinkedIn; "View Profile" button is hidden if blank)</span></label>
                    <input type="url" id="profile_url" name="profile_url" value="<?= attr($f['profile_url']) ?>" placeholder="https://www.linkedin.com/in/...">
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div class="form-row">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="active"   <?= $f['status'] === 'active'   ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= $f['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                        <div class="help">Only active members appear in the picker on Success Stories.</div>
                    </div>
                    <div class="form-row">
                        <label for="sort_order">Sort Order</label>
                        <input type="number" id="sort_order" name="sort_order" min="1" value="<?= attr((string) $f['sort_order']) ?>">
                    </div>
                </div>
                <button type="submit" class="admin-btn" style="margin-top:10px;">
                    <?= $member ? 'Update Team Member' : 'Create Team Member' ?>
                </button>
            </div>
        </div>
    </form>

    <!-- CKEditor 5 on the Experience field -->
    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
    <script>
    (function () {
        var el = document.getElementById('experience_text');
        if (!el) { return; }
        ClassicEditor.create(el, {
            toolbar: ['bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'undo', 'redo']
        }).catch(function (err) { console.error(err); });
    })();
    </script>

    <?php
    require __DIR__ . '/_footer.php';
    exit;
}

// ── LIST ─────────────────────────────────────────────────────────────────────

$stmt = $pdo->query(
    'SELECT m.*, (SELECT COUNT(*) FROM success_stories s WHERE s.expert_member_id = m.id) AS story_count
     FROM team_members m
     ORDER BY m.sort_order ASC, m.name ASC'
);
$rows = $stmt->fetchAll();

$admin_page_title = 'Team Members';
$admin_active     = 'team-members';
require __DIR__ . '/_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>Team Members</h1>
        <div class="subtitle"><?= count($rows) ?> member<?= count($rows) === 1 ? '' : 's' ?> — a shared roster you can attach to Success Stories (and other pages later).</div>
    </div>
    <a href="?action=new" class="admin-btn">New Team Member</a>
</div>

<div class="admin-card">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Designation</th>
                <th>Status</th>
                <th>Sort Order</th>
                <th>Stories</th>
                <th class="col-actions">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="6" class="empty-state">
                    <h3>No team members yet</h3>
                    <a href="?action=new" class="admin-btn">New Team Member</a>
                </td></tr>
            <?php else: ?>
                <?php foreach ($rows as $r): ?>
                <tr>
                    <td><a href="?action=edit&amp;id=<?= (int) $r['id'] ?>"><?= e($r['name']) ?></a></td>
                    <td><?= e($r['designation']) ?></td>
                    <td><span class="pill pill--<?= attr($r['status']) ?>"><?= e($r['status']) ?></span></td>
                    <td><?= (int) $r['sort_order'] ?></td>
                    <td><?= (int) $r['story_count'] ?></td>
                    <td class="col-actions">
                        <a href="?action=edit&amp;id=<?= (int) $r['id'] ?>" class="admin-btn admin-btn--small">Edit</a>
                        <form method="post" action="?action=delete" style="display:inline;"
                              onsubmit="return confirm('Delete this team member? Stories featuring them will just hide that section.');">
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
