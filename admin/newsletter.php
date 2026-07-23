<?php
/**
 * Admin — Newsletter Subscribers
 */

require __DIR__ . '/bootstrap.php';

$pdo = db();
if (!$pdo) { die('Database connection failed.'); }

$action = $_GET['action'] ?? 'list';

// Check table exists
try {
    $pdo->query('SELECT 1 FROM newsletter_subscribers LIMIT 1');
    $table_exists = true;
} catch (PDOException $e) {
    $table_exists = false;
}

// ── UNSUBSCRIBE ───────────────────────────────────────────────────────────────

if ($action === 'unsubscribe' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_or_die();
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        $pdo->prepare(
            "UPDATE newsletter_subscribers SET status = 'unsubscribed', unsubscribed_at = NOW() WHERE id = :id"
        )->execute([':id' => $id]);
        flash('success', 'Subscriber unsubscribed.');
    }
    header('Location: ' . ADMIN_URL . '/newsletter.php'); exit;
}

// ── REACTIVATE ────────────────────────────────────────────────────────────────

if ($action === 'resubscribe' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_or_die();
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        $pdo->prepare(
            "UPDATE newsletter_subscribers SET status = 'active', unsubscribed_at = NULL WHERE id = :id"
        )->execute([':id' => $id]);
        flash('success', 'Subscriber reactivated.');
    }
    header('Location: ' . ADMIN_URL . '/newsletter.php'); exit;
}

// ── DELETE ────────────────────────────────────────────────────────────────────

if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_or_die();
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        $pdo->prepare('DELETE FROM newsletter_subscribers WHERE id = :id')->execute([':id' => $id]);
        flash('success', 'Subscriber removed.');
    }
    header('Location: ' . ADMIN_URL . '/newsletter.php'); exit;
}

// ── EXPORT CSV ────────────────────────────────────────────────────────────────

if ($action === 'export' && $table_exists) {
    $rows = $pdo->query(
        'SELECT email, status, source, subscribed_at, ip_address
         FROM newsletter_subscribers ORDER BY subscribed_at DESC'
    )->fetchAll();
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="subscribers_' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Email', 'Status', 'Source', 'Subscribed At', 'IP Address']);
    foreach ($rows as $r) {
        fputcsv($out, [$r['email'], $r['status'], $r['source'], $r['subscribed_at'], $r['ip_address']]);
    }
    fclose($out);
    exit;
}

// ── LIST ──────────────────────────────────────────────────────────────────────

$status_filter = $_GET['status'] ?? '';
$where  = ''; $params = [];
if (in_array($status_filter, ['active', 'unsubscribed'], true)) {
    $where = ' WHERE status = :st';
    $params[':st'] = $status_filter;
}

$rows  = [];
$stats = ['total' => 0, 'active' => 0, 'unsubscribed' => 0];

if ($table_exists) {
    $stmt = $pdo->prepare(
        'SELECT id, email, status, source, subscribed_at
         FROM newsletter_subscribers' . $where . '
         ORDER BY subscribed_at DESC LIMIT 500'
    );
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    foreach ($pdo->query('SELECT status, COUNT(*) AS cnt FROM newsletter_subscribers GROUP BY status')->fetchAll() as $r) {
        $stats[$r['status']] = (int) $r['cnt'];
        $stats['total'] += (int) $r['cnt'];
    }
}

$admin_page_title = 'Newsletter';
$admin_active     = 'newsletter';
require __DIR__ . '/_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>Newsletter Subscribers</h1>
        <div class="subtitle"><?= $stats['total'] ?> total · <?= $stats['active'] ?> active</div>
    </div>
    <?php if ($table_exists): ?>
        <a href="?action=export" class="admin-btn admin-btn--ghost">Export CSV</a>
    <?php endif; ?>
</div>

<?php if (!$table_exists): ?>
    <div class="flash flash--error">
        Table <code>newsletter_subscribers</code> does not exist.
        <a href="<?= ADMIN_URL ?>/setup.php">Run migrations</a> to create it.
    </div>
<?php else: ?>

<div class="stat-grid" style="margin-bottom:24px;">
    <div class="stat-card">
        <div class="stat-card__val"><?= $stats['total'] ?></div>
        <div class="stat-card__lbl">Total</div>
    </div>
    <div class="stat-card stat-card--ok">
        <div class="stat-card__val"><?= $stats['active'] ?></div>
        <div class="stat-card__lbl">Active</div>
    </div>
    <div class="stat-card stat-card--warn">
        <div class="stat-card__val"><?= $stats['unsubscribed'] ?></div>
        <div class="stat-card__lbl">Unsubscribed</div>
    </div>
</div>

<div style="margin-bottom:14px;">
    <a href="?" class="<?= $status_filter === '' ? 'admin-btn admin-btn--small' : 'admin-btn admin-btn--ghost admin-btn--small' ?>">All</a>
    <a href="?status=active" class="<?= $status_filter === 'active' ? 'admin-btn admin-btn--small' : 'admin-btn admin-btn--ghost admin-btn--small' ?>">Active</a>
    <a href="?status=unsubscribed" class="<?= $status_filter === 'unsubscribed' ? 'admin-btn admin-btn--small' : 'admin-btn admin-btn--ghost admin-btn--small' ?>">Unsubscribed</a>
</div>

<div class="admin-card">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Email</th>
                <th>Status</th>
                <th>Source</th>
                <th>Subscribed</th>
                <th class="col-actions">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="5" class="empty-state">
                    <h3>No subscribers yet</h3>
                    <p>Subscribers added via the website footer form will appear here.</p>
                </td></tr>
            <?php else: ?>
                <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= e($r['email']) ?></td>
                    <td>
                        <span class="pill pill--<?= $r['status'] === 'active' ? 'published' : 'archived' ?>">
                            <?= e($r['status']) ?>
                        </span>
                    </td>
                    <td class="text-muted"><?= e($r['source']) ?></td>
                    <td class="no-wrap text-muted"><?= e(substr($r['subscribed_at'], 0, 10)) ?></td>
                    <td class="col-actions">
                        <?php if ($r['status'] === 'active'): ?>
                            <form method="post" action="?action=unsubscribe" style="display:inline;"
                                  onsubmit="return confirm('Unsubscribe this address?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                                <button type="submit" class="admin-btn admin-btn--ghost admin-btn--small">Unsubscribe</button>
                            </form>
                        <?php else: ?>
                            <form method="post" action="?action=resubscribe" style="display:inline;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                                <button type="submit" class="admin-btn admin-btn--ghost admin-btn--small">Reactivate</button>
                            </form>
                        <?php endif; ?>
                        <form method="post" action="?action=delete" style="display:inline;"
                              onsubmit="return confirm('Permanently delete this subscriber?');">
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

<?php require __DIR__ . '/_footer.php'; ?>
