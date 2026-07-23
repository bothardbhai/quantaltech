<?php
/**
 * Admin — Database setup & migrations
 */

require __DIR__ . '/bootstrap.php';
require DB_DIR . '/migrate.php';

// ── Run migrations ────────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'run_migrations') {
    csrf_verify_or_die();
    try {
        $result = run_migrations(DB_MIGRATIONS_DIR);
        if ($result['success']) {
            flash('success', $result['message']);
        } else {
            flash('error', $result['message']);
        }
    } catch (Exception $e) {
        flash('error', 'Error running migrations: ' . $e->getMessage());
    }
    header('Location: ' . ADMIN_URL . '/setup.php'); exit;
}

// ── Get migration status ──────────────────────────────────────────────────────

try {
    $status = get_migration_status(DB_MIGRATIONS_DIR);
} catch (Exception $e) {
    $status = [
        'status'              => 'error',
        'message'             => $e->getMessage(),
        'total_migrations'    => 0,
        'executed_migrations' => 0,
        'pending_migrations'  => 0,
        'pending_migration_names' => [],
    ];
}

$admin_page_title = 'Database Setup';
$admin_active     = 'setup';
require __DIR__ . '/_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>Database Setup</h1>
        <div class="subtitle">Migrations &amp; schema status</div>
    </div>
</div>

<!-- Migration Status -->
<div class="admin-card" style="margin-bottom:20px;">
    <div class="admin-card__head">Migration Status</div>
    <div class="admin-card__body">
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-card__val"><?= (int) $status['total_migrations'] ?></div>
                <div class="stat-card__lbl">Total</div>
            </div>
            <div class="stat-card stat-card--ok">
                <div class="stat-card__val"><?= (int) $status['executed_migrations'] ?></div>
                <div class="stat-card__lbl">Executed</div>
            </div>
            <div class="stat-card <?= $status['pending_migrations'] > 0 ? 'stat-card--warn' : 'stat-card--ok' ?>">
                <div class="stat-card__val"><?= (int) $status['pending_migrations'] ?></div>
                <div class="stat-card__lbl">Pending</div>
            </div>
        </div>

        <?php if ($status['status'] === 'ok' && $status['pending_migrations'] === 0): ?>
            <p style="color:var(--admin-success);font-weight:600;">✓ All migrations executed. Database is up to date.</p>
        <?php elseif ($status['pending_migrations'] > 0): ?>
            <p style="color:var(--admin-warn);font-weight:600;">⚠ <?= (int) $status['pending_migrations'] ?> pending migration(s):</p>
            <ul class="migration-list">
                <?php foreach ($status['pending_migration_names'] as $name): ?>
                    <li><code class="text-mono"><?= e($name) ?></code></li>
                <?php endforeach; ?>
            </ul>
            <form method="post" style="margin-top:16px;">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="run_migrations">
                <button type="submit" class="admin-btn"
                        onclick="return confirm('Run all pending migrations?')">Run Migrations</button>
            </form>
        <?php elseif ($status['status'] === 'error'): ?>
            <p style="color:var(--admin-danger);">Error: <?= e($status['message'] ?? 'Unknown error') ?></p>
        <?php endif; ?>
    </div>
</div>

<!-- Tables -->
<div class="admin-card" style="margin-bottom:20px;">
    <div class="admin-card__head">Database Tables</div>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Table</th>
                <th>Purpose</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code class="text-mono">webinars</code></td>
                <td>Webinar events and scheduling</td>
                <td>
                    <?php if ($status['executed_migrations'] >= 1): ?>
                        <span class="pill pill--published">✓ Created</span>
                    <?php else: ?>
                        <span class="pill pill--draft">Pending</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td><code class="text-mono">success_stories</code></td>
                <td>Client case studies and testimonials</td>
                <td>
                    <?php if ($status['executed_migrations'] >= 2): ?>
                        <span class="pill pill--published">✓ Created</span>
                    <?php else: ?>
                        <span class="pill pill--draft">Pending</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td><code class="text-mono">services</code></td>
                <td>AI service offerings</td>
                <td>
                    <?php if ($status['executed_migrations'] >= 3): ?>
                        <span class="pill pill--published">✓ Created</span>
                    <?php else: ?>
                        <span class="pill pill--draft">Pending</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td><code class="text-mono">newsletter_subscribers</code></td>
                <td>Newsletter subscriber list</td>
                <td>
                    <?php if ($status['executed_migrations'] >= 4): ?>
                        <span class="pill pill--published">✓ Created</span>
                    <?php else: ?>
                        <span class="pill pill--draft">Pending</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td><code class="text-mono">migrations_log</code></td>
                <td>Migration execution history</td>
                <td>
                    <?php if ($status['status'] === 'ok'): ?>
                        <span class="pill pill--published">✓ Exists</span>
                    <?php else: ?>
                        <span class="pill pill--draft">Will be created</span>
                    <?php endif; ?>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Content Management Links -->
<div class="admin-card" style="margin-bottom:20px;">
    <div class="admin-card__head">Content Management</div>
    <div class="admin-card__body">
        <div class="mgmt-links">
            <div class="mgmt-link-item">
                <a href="<?= ADMIN_URL ?>/services.php" class="admin-btn admin-btn--ghost">Services</a>
                <small>Configure AI service offerings</small>
            </div>
            <div class="mgmt-link-item">
                <a href="<?= ADMIN_URL ?>/webinars.php" class="admin-btn admin-btn--ghost">Webinars</a>
                <small>Schedule and publish webinar events</small>
            </div>
            <div class="mgmt-link-item">
                <a href="<?= ADMIN_URL ?>/success-stories.php" class="admin-btn admin-btn--ghost">Success Stories</a>
                <small>Publish client case studies</small>
            </div>
            <div class="mgmt-link-item">
                <a href="<?= ADMIN_URL ?>/newsletter.php" class="admin-btn admin-btn--ghost">Newsletter</a>
                <small>Manage subscriber list</small>
            </div>
        </div>
    </div>
</div>

<!-- API Endpoints Reference -->
<div class="admin-card">
    <div class="admin-card__head">Public API Endpoints</div>
    <table class="admin-table">
        <thead>
            <tr><th>Endpoint</th><th>Description</th></tr>
        </thead>
        <tbody>
            <tr><td><code class="text-mono">/api/content?endpoint=services&amp;action=list</code></td><td>All published services</td></tr>
            <tr><td><code class="text-mono">/api/content?endpoint=services&amp;action=detail&amp;slug=voice-ai</code></td><td>Service by slug</td></tr>
            <tr><td><code class="text-mono">/api/content?endpoint=webinars&amp;action=list</code></td><td>All published webinars</td></tr>
            <tr><td><code class="text-mono">/api/content?endpoint=webinars&amp;action=list&amp;filter=upcoming</code></td><td>Future webinars only</td></tr>
            <tr><td><code class="text-mono">/api/content?endpoint=success-stories&amp;action=list</code></td><td>All published success stories</td></tr>
            <tr><td><code class="text-mono">/api/content?endpoint=success-stories&amp;action=list&amp;featured=1</code></td><td>Featured stories only</td></tr>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
