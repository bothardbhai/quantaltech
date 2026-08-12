<?php
/**
 * Sitemap Management — status view + manual "Regenerate" trigger for
 * /sitemap.xml. Uses the exact same generator as the public sitemap
 * (core/sitemap.php); there is no separate admin-side generation logic.
 *
 * /sitemap.xml itself is always built live on each request, so nothing here
 * can make it stale — "Regenerate" re-runs the generator to confirm the URL
 * count and records an informational last-run timestamp for this screen.
 */
require __DIR__ . '/bootstrap.php';

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_or_die();

    try {
        $result = sitemap_generate();
        sitemap_record_generation($result['final_total']);
        flash('success', 'Sitemap regenerated successfully. ' . $result['final_total'] . ' URL(s) found.');
    } catch (Throwable $e) {
        error_log('Sitemap regenerate action failed: ' . $e->getMessage());
        flash('error', 'Could not regenerate the sitemap. Check the server error log for details.');
    }

    header('Location: ' . ADMIN_URL . '/sitemap.php');
    exit;
}

// Live status for the view — always freshly computed, same as /sitemap.xml.
$sitemap_result = ['urls' => [], 'sources' => [], 'raw_total' => 0, 'final_total' => 0];
$generate_error = null;
try {
    $sitemap_result = sitemap_generate();
} catch (Throwable $e) {
    error_log('Sitemap status load failed: ' . $e->getMessage());
    $generate_error = 'Could not load sitemap status. Check the server error log for details.';
}
$url_count = $sitemap_result['final_total'];
$sources   = $sitemap_result['sources'];

$last_generated_at = null;
if ($pdo) {
    try {
        $stmt = $pdo->prepare('SELECT `value` FROM settings WHERE `key` = :k');
        $stmt->execute([':k' => 'sitemap_last_generated_at']);
        $last_generated_at = $stmt->fetchColumn() ?: null;
    } catch (PDOException $e) {
        // settings table missing — leave as null
    }
}

$sitemap_url = (defined('SITE_URL') && SITE_URL ? rtrim(SITE_URL, '/') : '') . '/sitemap.xml';

$admin_page_title = 'Sitemap';
$admin_active     = 'sitemap';
require __DIR__ . '/_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>Sitemap Management</h1>
        <div class="subtitle">Auto-generated from your published pages, services, hire pages, success stories, and blog posts.</div>
    </div>
</div>

<div class="stat-grid">
    <div class="stat-card <?= $generate_error ? 'stat-card--warn' : 'stat-card--ok' ?>">
        <div class="stat-card__val"><?= $generate_error ? '—' : 'Available' ?></div>
        <div class="stat-card__lbl">Status</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__val"><?= $generate_error ? '—' : (int) $url_count ?></div>
        <div class="stat-card__lbl">Total URLs</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__val" style="font-size:1.1rem;"><?= $last_generated_at ? e(substr($last_generated_at, 0, 16)) : 'Never' ?></div>
        <div class="stat-card__lbl">Last Regenerated</div>
    </div>
</div>

<?php if ($generate_error): ?>
    <div class="flash flash--error"><?= e($generate_error) ?></div>
<?php endif; ?>

<div class="admin-card">
    <div class="admin-card__head">Sitemap URL</div>
    <div class="admin-card__body">
        <p><code class="text-mono"><?= e($sitemap_url) ?></code></p>
        <p class="help">Always reflects your current published content — no need to edit or upload this file manually.</p>

        <div style="display:flex;gap:10px;margin-top:14px;">
            <form method="post" id="sitemap-regenerate-form">
                <?= csrf_field() ?>
                <button type="submit" class="admin-btn" id="sitemap-regenerate-btn">Regenerate Sitemap</button>
            </form>
            <a href="<?= attr($sitemap_url) ?>" target="_blank" rel="noopener" class="admin-btn admin-btn--ghost">View Sitemap</a>
        </div>
    </div>
</div>

<?php if (!$generate_error): ?>
<div class="admin-card">
    <div class="admin-card__head">Source breakdown</div>
    <div class="admin-card__body">
        <p class="help">How many URLs each content source contributed to the sitemap above — useful for confirming nothing was missed. This breakdown is admin-only; it is never exposed in the public <code>/sitemap.xml</code>.</p>
        <table class="admin-table">
            <thead>
                <tr><th>Source</th><th>URLs included</th></tr>
            </thead>
            <tbody>
                <tr><td>Static pages (on disk, resolvable)</td><td><?= (int) $sources['static_pages_on_disk'] ?> discovered &rarr; <?= (int) $sources['static_pages_included'] ?> included</td></tr>
                <tr><td class="text-muted" style="padding-left:28px;">&#8627; with SEO metadata (Pages &amp; SEO)</td><td class="text-muted"><?= (int) $sources['pages_table_published'] ?> published / <?= (int) $sources['pages_table_total'] ?> total rows</td></tr>
                <tr><td>Blog posts</td><td><?= (int) $sources['blog_posts'] ?></td></tr>
                <tr><td>Services</td><td><?= (int) $sources['services'] ?></td></tr>
                <tr><td>Hire pages</td><td><?= (int) $sources['hire_pages'] ?></td></tr>
                <tr><td>Success stories</td><td><?= (int) $sources['success_stories'] ?></td></tr>
                <tr><td>Webinars / Podcast <span class="text-muted">(no public detail route yet)</span></td><td><?= (int) $sources['webinars'] ?></td></tr>
                <tr>
                    <td><strong>Total before duplicate removal</strong></td>
                    <td><strong><?= (int) $sitemap_result['raw_total'] ?></strong></td>
                </tr>
                <tr>
                    <td><strong>Total final URLs</strong></td>
                    <td><strong><?= (int) $sitemap_result['final_total'] ?></strong></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<script>
document.getElementById('sitemap-regenerate-form').addEventListener('submit', function () {
    var btn = document.getElementById('sitemap-regenerate-btn');
    btn.disabled = true;
    btn.textContent = 'Regenerating...';
});
</script>

<?php require __DIR__ . '/_footer.php'; ?>
