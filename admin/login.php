<?php
/**
 * Admin login.
 */
$skip_auth = true;          // Skip the auth_require() guard for this page
require __DIR__ . '/bootstrap.php';

// If already logged in, bounce to dashboard
if (auth_is_logged_in()) {
    header('Location: ' . ADMIN_URL . '/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_or_die();
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } elseif (auth_login($username, $password)) {
        header('Location: ' . ADMIN_URL . '/index.php');
        exit;
    } else {
        $error = 'Invalid credentials.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title>Sign In — Admin</title>
    <link rel="stylesheet" href="<?= ADMIN_URL ?>/assets/css/admin.css">
</head>
<body>
<div class="login-shell">
    <form method="post" class="login-card admin-form" autocomplete="off">
        <h1><?= e(SITE_NAME) ?></h1>
        <p class="subtitle">Sign in to the admin panel</p>

        <?php if ($error !== ''): ?>
            <div class="flash flash--error"><?= e($error) ?></div>
        <?php endif; ?>

        <?= csrf_field() ?>

        <div class="form-row">
            <label for="username">Username or Email</label>
            <input type="text" id="username" name="username" required autofocus value="<?= attr($_POST['username'] ?? '') ?>">
        </div>

        <div class="form-row">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>

        <button type="submit" class="admin-btn" style="width:100%;padding:10px;">Sign In</button>
    </form>
</div>
</body>
</html>
