<?php
/**
 * Admin header — common chrome for every page (except login).
 *
 * Variables:
 *   $admin_page_title — string, browser title
 *   $admin_active     — string, marks the active sidebar item ('dashboard', 'pages', 'blog', 'media', 'settings')
 */
$admin_page_title = $admin_page_title ?? 'Admin';
$admin_active     = $admin_active     ?? '';
$user             = auth_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title><?= e($admin_page_title) ?> — Admin</title>
    <link rel="stylesheet" href="<?= ADMIN_URL ?>/assets/css/admin.css">
</head>
<body>
<div class="admin-shell">
    <aside class="admin-sidebar">
        <div class="admin-sidebar__brand">
            <?= e(SITE_NAME) ?>
            <small>Admin Panel</small>
        </div>
        <nav class="admin-sidebar__nav">
            <a href="<?= ADMIN_URL ?>/index.php"          class="<?= $admin_active === 'dashboard'     ? 'is-active' : '' ?>" data-icon="🏠">Dashboard</a>
            <a href="<?= ADMIN_URL ?>/pages.php"          class="<?= $admin_active === 'pages'         ? 'is-active' : '' ?>" data-icon="📄">Pages &amp; SEO</a>
            <a href="<?= ADMIN_URL ?>/blog.php"           class="<?= $admin_active === 'blog'          ? 'is-active' : '' ?>" data-icon="📝">Blog</a>
            <a href="<?= ADMIN_URL ?>/services.php"       class="<?= $admin_active === 'services'      ? 'is-active' : '' ?>" data-icon="⚡">Services</a>
            <a href="<?= ADMIN_URL ?>/webinars.php"       class="<?= $admin_active === 'webinars'      ? 'is-active' : '' ?>" data-icon="🎓">Webinars</a>
            <a href="<?= ADMIN_URL ?>/success-stories.php" class="<?= $admin_active === 'success-stories' ? 'is-active' : '' ?>" data-icon="🏆">Success Stories</a>
            <a href="<?= ADMIN_URL ?>/newsletter.php"     class="<?= $admin_active === 'newsletter'     ? 'is-active' : '' ?>" data-icon="📧">Newsletter</a>
            <a href="<?= ADMIN_URL ?>/media.php"          class="<?= $admin_active === 'media'         ? 'is-active' : '' ?>" data-icon="🖼️">Media</a>
            <a href="<?= ADMIN_URL ?>/setup.php"          class="<?= $admin_active === 'setup'         ? 'is-active' : '' ?>" data-icon="🔧">Database Setup</a>
            <a href="<?= ADMIN_URL ?>/settings.php"       class="<?= $admin_active === 'settings'      ? 'is-active' : '' ?>" data-icon="⚙️">Settings</a>
        </nav>
        <div class="admin-sidebar__footer">
            Signed in as<br>
            <strong><?= e($user['display_name'] ?? '') ?></strong>
            <br><br>
            <a href="/" target="_blank" rel="noopener">View site →</a>
            &middot;
            <a href="<?= ADMIN_URL ?>/logout.php">Log out</a>
        </div>
    </aside>
    <main class="admin-main">
        <?php foreach (flash_consume() as $flash_msg): ?>
            <div class="flash flash--<?= attr($flash_msg['type']) ?>"><?= e($flash_msg['message']) ?></div>
        <?php endforeach; ?>
