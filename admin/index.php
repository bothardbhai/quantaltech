<?php
require __DIR__ . '/bootstrap.php';

$pdo = db();

// Quick counts for dashboard tiles
$counts = [
    'pages_with_seo' => 0,
    'pages_on_disk'  => count(router_discover_pages()),
    'posts_total'    => 0,
    'posts_published'=> 0,
    'posts_draft'    => 0,
    'media_count'    => 0,
];
if ($pdo) {
    try {
        $counts['pages_with_seo']  = (int) $pdo->query('SELECT COUNT(*) FROM pages')->fetchColumn();
        $counts['posts_total']     = (int) $pdo->query('SELECT COUNT(*) FROM posts')->fetchColumn();
        $counts['posts_published'] = (int) $pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'published'")->fetchColumn();
        $counts['posts_draft']     = (int) $pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'draft'")->fetchColumn();
        $counts['media_count']     = (int) $pdo->query('SELECT COUNT(*) FROM media')->fetchColumn();
    } catch (PDOException $e) {
        // schema not imported — show zeros
    }
}

$admin_page_title = 'Dashboard';
$admin_active     = 'dashboard';
require __DIR__ . '/_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>Dashboard</h1>
        <div class="subtitle">Welcome back, <?= e(auth_user()['display_name']) ?>.</div>
    </div>
</div>

<div class="row" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;">
    <div class="admin-card">
        <div class="admin-card__body">
            <div style="font-size:12px;color:var(--admin-muted);text-transform:uppercase;letter-spacing:0.4px;">Pages on disk</div>
            <div style="font-size:32px;font-weight:600;line-height:1.1;margin-top:6px;"><?= $counts['pages_on_disk'] ?></div>
            <div style="margin-top:8px;font-size:12.5px;color:var(--admin-muted);">
                <?= $counts['pages_with_seo'] ?> have SEO metadata
            </div>
            <a href="<?= ADMIN_URL ?>/pages.php" class="admin-btn admin-btn--ghost admin-btn--small" style="margin-top:10px;">Manage →</a>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card__body">
            <div style="font-size:12px;color:var(--admin-muted);text-transform:uppercase;letter-spacing:0.4px;">Blog posts</div>
            <div style="font-size:32px;font-weight:600;line-height:1.1;margin-top:6px;"><?= $counts['posts_total'] ?></div>
            <div style="margin-top:8px;font-size:12.5px;color:var(--admin-muted);">
                <?= $counts['posts_published'] ?> published, <?= $counts['posts_draft'] ?> draft
            </div>
            <a href="<?= ADMIN_URL ?>/blog.php" class="admin-btn admin-btn--ghost admin-btn--small" style="margin-top:10px;">Manage →</a>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card__body">
            <div style="font-size:12px;color:var(--admin-muted);text-transform:uppercase;letter-spacing:0.4px;">Media files</div>
            <div style="font-size:32px;font-weight:600;line-height:1.1;margin-top:6px;"><?= $counts['media_count'] ?></div>
            <div style="margin-top:8px;font-size:12.5px;color:var(--admin-muted);">Uploaded via blog or pages</div>
            <a href="<?= ADMIN_URL ?>/media.php" class="admin-btn admin-btn--ghost admin-btn--small" style="margin-top:10px;">Browse →</a>
        </div>
    </div>
</div>

<div class="admin-card" style="margin-top:24px;">
    <div class="admin-card__head">Getting started</div>
    <div class="admin-card__body">
        <ol style="padding-left:18px;line-height:1.8;">
            <li><strong>Set SEO metadata</strong> on your existing pages — go to <a href="<?= ADMIN_URL ?>/pages.php">Pages &amp; SEO</a> and click any page to edit its title, meta description, OG image, and JSON-LD schema.</li>
            <li><strong>Configure site-wide defaults</strong> in <a href="<?= ADMIN_URL ?>/settings.php">Settings</a> — default OG image, Organization schema, etc.</li>
            <li><strong>Add your first blog post</strong> in <a href="<?= ADMIN_URL ?>/blog.php">Blog → New Post</a>.</li>
            <li>To add a new page, create the file at <code>pages/&lt;path&gt;.php</code> on disk — it appears here automatically.</li>
        </ol>
    </div>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
