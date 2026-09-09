<?php
/**
 * Admin — Contact / General Enquiry Submissions
 *
 * Shared list for the Contact Form and Podcast Inquiry Form (both post to
 * pages/contact-submit.php into the common `contact_submissions` table,
 * distinguished by `form_type`). Modeled directly on admin/newsletter.php —
 * same table-exists guard, same PRG action pattern, same hard-delete
 * convention (no soft-delete exists anywhere in this project).
 */

require __DIR__ . '/bootstrap.php';

$pdo = db();
if (!$pdo) { die('Database connection failed.'); }

$action = $_GET['action'] ?? 'list';
$STATUSES = ['new', 'read', 'contacted', 'closed'];

// Check table exists
try {
    $pdo->query('SELECT 1 FROM contact_submissions LIMIT 1');
    $table_exists = true;
} catch (PDOException $e) {
    $table_exists = false;
}

// ── UPDATE STATUS ─────────────────────────────────────────────────────────────

if ($action === 'update_status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_or_die();
    $id = (int) ($_POST['id'] ?? 0);
    $status = in_array($_POST['status'] ?? '', $STATUSES, true) ? $_POST['status'] : 'new';
    if ($id > 0) {
        $pdo->prepare(
            'UPDATE contact_submissions SET status = :st, updated_at = NOW() WHERE id = :id'
        )->execute([':st' => $status, ':id' => $id]);
        flash('success', 'Status updated.');
    }
    header('Location: ' . ADMIN_URL . '/contact-submissions.php'); exit;
}

// ── DELETE ────────────────────────────────────────────────────────────────────

if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_or_die();
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        $pdo->prepare('DELETE FROM contact_submissions WHERE id = :id')->execute([':id' => $id]);
        flash('success', 'Submission deleted.');
    }
    header('Location: ' . ADMIN_URL . '/contact-submissions.php'); exit;
}

// ── VIEW (detail) ─────────────────────────────────────────────────────────────

$view_row = null;
if ($action === 'view' && $table_exists) {
    $id = (int) ($_GET['id'] ?? 0);
    $stmt = $pdo->prepare('SELECT * FROM contact_submissions WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $view_row = $stmt->fetch();
    if (!$view_row) {
        flash('error', 'Submission not found.');
        header('Location: ' . ADMIN_URL . '/contact-submissions.php'); exit;
    }
}

// ── LIST ──────────────────────────────────────────────────────────────────────

$type_filter = $_GET['type'] ?? '';
$status_filter = $_GET['status'] ?? '';
$where = []; $params = [];
if (in_array($type_filter, ['contact', 'podcast'], true)) {
    $where[] = 'form_type = :ft';
    $params[':ft'] = $type_filter;
}
if (in_array($status_filter, $STATUSES, true)) {
    $where[] = 'status = :st';
    $params[':st'] = $status_filter;
}
$where_clause = $where ? (' WHERE ' . implode(' AND ', $where)) : '';

$rows = [];
$stats = ['total' => 0, 'new' => 0, 'read' => 0, 'contacted' => 0, 'closed' => 0];

if ($table_exists) {
    $stmt = $pdo->prepare(
        'SELECT id, form_type, name, email, phone, company, subject, page_url, status, created_at
         FROM contact_submissions' . $where_clause . '
         ORDER BY created_at DESC LIMIT 500'
    );
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    foreach ($pdo->query('SELECT status, COUNT(*) AS cnt FROM contact_submissions GROUP BY status')->fetchAll() as $r) {
        $stats[$r['status']] = (int) $r['cnt'];
        $stats['total'] += (int) $r['cnt'];
    }
}

$admin_page_title = $action === 'view' ? 'Contact Submission' : 'Contact Submissions';
$admin_active     = 'contact-submissions';
require __DIR__ . '/_header.php';
?>

<?php if ($action === 'view' && $view_row): ?>

    <div class="admin-page-header">
        <div>
            <h1>Submission #<?= (int) $view_row['id'] ?></h1>
            <div class="subtitle"><?= e(ucfirst($view_row['form_type'])) ?> &middot; <?= e(substr($view_row['created_at'], 0, 16)) ?></div>
        </div>
        <a href="<?= ADMIN_URL ?>/contact-submissions.php" class="admin-btn admin-btn--ghost">Back to list</a>
    </div>

    <div class="admin-card"><div class="admin-card__body">
        <table class="admin-table">
            <tbody>
                <tr><th style="width:160px;">Form Type</th><td><?= e(ucfirst($view_row['form_type'])) ?></td></tr>
                <tr><th>Name</th><td><?= e($view_row['name']) ?></td></tr>
                <tr><th>First Name</th><td><?= e($view_row['first_name']) ?></td></tr>
                <tr><th>Last Name</th><td><?= e($view_row['last_name']) ?></td></tr>
                <tr><th>Email</th><td><a href="mailto:<?= attr($view_row['email']) ?>"><?= e($view_row['email']) ?></a></td></tr>
                <tr><th>Phone</th><td><?= e($view_row['phone']) ?></td></tr>
                <tr><th>Company</th><td><?= e($view_row['company']) ?></td></tr>
                <tr><th>Subject</th><td><?= e($view_row['subject']) ?></td></tr>
                <tr><th>Message</th><td style="white-space:pre-wrap;"><?= e($view_row['message']) ?></td></tr>
                <tr><th>Page URL</th><td><a href="<?= attr($view_row['page_url']) ?>" target="_blank" rel="noopener"><?= e($view_row['page_url']) ?></a></td></tr>
                <tr><th>IP Address</th><td><?= e($view_row['ip_address']) ?></td></tr>
                <tr><th>Created</th><td><?= e($view_row['created_at']) ?></td></tr>
            </tbody>
        </table>
    </div></div>

    <div class="admin-card"><div class="admin-card__body">
        <form method="post" action="?action=update_status" style="display:inline-flex;gap:10px;align-items:center;">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int) $view_row['id'] ?>">
            <label for="status" style="margin:0;">Status</label>
            <select id="status" name="status" onchange="this.form.submit()">
                <?php foreach ($STATUSES as $st): ?>
                    <option value="<?= $st ?>" <?= $view_row['status'] === $st ? 'selected' : '' ?>><?= e(ucfirst($st)) ?></option>
                <?php endforeach; ?>
            </select>
        </form>
        <form method="post" action="?action=delete" style="display:inline;margin-left:14px;"
              onsubmit="return confirm('Permanently delete this submission?');">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int) $view_row['id'] ?>">
            <button type="submit" class="admin-btn admin-btn--danger admin-btn--small">Delete</button>
        </form>
    </div></div>

<?php else: ?>

    <div class="admin-page-header">
        <div>
            <h1>Contact Submissions</h1>
            <div class="subtitle"><?= $stats['total'] ?> total &middot; <?= $stats['new'] ?> new</div>
        </div>
    </div>

    <?php if (!$table_exists): ?>
        <div class="flash flash--error">
            Table <code>contact_submissions</code> does not exist.
            <a href="<?= ADMIN_URL ?>/setup.php">Run migrations</a> to create it.
        </div>
    <?php else: ?>

        <div class="stat-grid" style="margin-bottom:24px;">
            <div class="stat-card"><div class="stat-card__val"><?= $stats['total'] ?></div><div class="stat-card__lbl">Total</div></div>
            <div class="stat-card stat-card--warn"><div class="stat-card__val"><?= $stats['new'] ?></div><div class="stat-card__lbl">New</div></div>
            <div class="stat-card"><div class="stat-card__val"><?= $stats['read'] ?></div><div class="stat-card__lbl">Read</div></div>
            <div class="stat-card stat-card--ok"><div class="stat-card__val"><?= $stats['contacted'] ?></div><div class="stat-card__lbl">Contacted</div></div>
            <div class="stat-card"><div class="stat-card__val"><?= $stats['closed'] ?></div><div class="stat-card__lbl">Closed</div></div>
        </div>

        <div style="margin-bottom:8px;">
            <a href="?" class="<?= $type_filter === '' ? 'admin-btn admin-btn--small' : 'admin-btn admin-btn--ghost admin-btn--small' ?>">All Types</a>
            <a href="?type=contact" class="<?= $type_filter === 'contact' ? 'admin-btn admin-btn--small' : 'admin-btn admin-btn--ghost admin-btn--small' ?>">Contact</a>
            <a href="?type=podcast" class="<?= $type_filter === 'podcast' ? 'admin-btn admin-btn--small' : 'admin-btn admin-btn--ghost admin-btn--small' ?>">Podcast</a>
        </div>
        <div style="margin-bottom:14px;">
            <a href="?status=" class="<?= $status_filter === '' ? 'admin-btn admin-btn--small' : 'admin-btn admin-btn--ghost admin-btn--small' ?>">All Statuses</a>
            <?php foreach ($STATUSES as $st): ?>
                <a href="?status=<?= $st ?>" class="<?= $status_filter === $st ? 'admin-btn admin-btn--small' : 'admin-btn admin-btn--ghost admin-btn--small' ?>"><?= e(ucfirst($st)) ?></a>
            <?php endforeach; ?>
        </div>

        <div class="admin-card">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Company</th>
                        <th>Subject</th>
                        <th>Page URL</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                        <tr><td colspan="10" class="empty-state">
                            <h3>No submissions yet</h3>
                            <p>Contact and podcast enquiry submissions will appear here.</p>
                        </td></tr>
                    <?php else: ?>
                        <?php foreach ($rows as $r): ?>
                        <tr>
                            <td><span class="pill pill--<?= $r['form_type'] === 'podcast' ? 'archived' : 'published' ?>"><?= e(ucfirst($r['form_type'])) ?></span></td>
                            <td><?= e($r['name']) ?></td>
                            <td><?= e($r['email']) ?></td>
                            <td class="text-muted"><?= e($r['phone']) ?></td>
                            <td class="text-muted"><?= e($r['company']) ?></td>
                            <td><?= e($r['subject']) ?></td>
                            <td class="text-muted no-wrap" style="max-width:220px;overflow:hidden;text-overflow:ellipsis;">
                                <a href="<?= attr($r['page_url']) ?>" target="_blank" rel="noopener"><?= e($r['page_url']) ?></a>
                            </td>
                            <td>
                                <form method="post" action="?action=update_status" style="display:inline;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                                    <select name="status" onchange="this.form.submit()">
                                        <?php foreach ($STATUSES as $st): ?>
                                            <option value="<?= $st ?>" <?= $r['status'] === $st ? 'selected' : '' ?>><?= e(ucfirst($st)) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </td>
                            <td class="no-wrap text-muted"><?= e(substr($r['created_at'], 0, 16)) ?></td>
                            <td class="col-actions">
                                <a href="?action=view&id=<?= (int) $r['id'] ?>" class="admin-btn admin-btn--ghost admin-btn--small">View</a>
                                <form method="post" action="?action=delete" style="display:inline;"
                                      onsubmit="return confirm('Permanently delete this submission?');">
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

    <?php endif; ?>

<?php endif; ?>

<?php require __DIR__ . '/_footer.php'; ?>
