<?php
/**
 * Admin bootstrap — required at the top of every admin page.
 *
 * Loads config, helpers, DB, auth, CSRF. By default requires login; pages that
 * don't (login.php) skip the require.
 */

declare(strict_types=1);

// Admin is at /admin/. ROOT_DIR is one level up.
define('ROOT_DIR',     dirname(__DIR__, 1));
define('CORE_DIR',     ROOT_DIR . '/core');
define('CONFIG_DIR',   ROOT_DIR . '/config');
define('PAGES_DIR',    ROOT_DIR . '/pages');
define('PARTIALS_DIR', ROOT_DIR . '/partials');
define('UPLOADS_DIR',  ROOT_DIR . '/uploads');
define('DB_DIR',       ROOT_DIR . '/db');
define('DB_MIGRATIONS_DIR', DB_DIR . '/migrations');

require CONFIG_DIR . '/config.php';

// Admin URL for all redirects and links
define('ADMIN_URL', BASE_URL . '/admin');

require CORE_DIR    . '/helpers.php';
require CORE_DIR    . '/db.php';
require CORE_DIR    . '/content-helpers.php';
require CORE_DIR    . '/router.php';
require CORE_DIR    . '/auth.php';
require CORE_DIR    . '/csrf.php';

auth_session_start();

// Default: require login. Override by setting $skip_auth = true BEFORE including this.
if (empty($skip_auth)) {
    auth_require();
}

// Flash message helpers — for redirect-after-post pattern.
function flash(string $type, string $message): void
{
    auth_session_start();
    $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
}

function flash_consume(): array
{
    auth_session_start();
    $f = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $f;
}
