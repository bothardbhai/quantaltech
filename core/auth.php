<?php
/**
 * Auth — session-based admin authentication.
 *
 * Uses PHP sessions with a dedicated cookie name (so admin auth doesn't collide
 * with anything on the public side later). Sessions regenerate IDs on login to
 * defend against session fixation.
 */

declare(strict_types=1);

/**
 * Start (or resume) the admin session. Idempotent — safe to call from any admin file.
 */
function auth_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $name = defined('SESSION_NAME') ? SESSION_NAME : 'quantal_admin';
    session_name($name);

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => defined('SESSION_SECURE_COOKIE') ? SESSION_SECURE_COOKIE : false,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

/**
 * Attempt to log in with username + password.
 * Returns true on success, false on failure (caller decides what to display).
 *
 * Uses constant-time comparison via password_verify(). Re-hashes the password if
 * PHP's recommended algorithm has moved on.
 */
function auth_login(string $username, string $password): bool
{
    auth_session_start();

    $pdo = db();
    if (!$pdo) {
        return false;
    }

    $stmt = $pdo->prepare(
        'SELECT id, username, password_hash, role, is_active, display_name
         FROM users
         WHERE (username = :u1 OR email = :u2) AND is_active = 1
         LIMIT 1'
    );
    $stmt->execute([':u1' => $username, ':u2' => $username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        // Throttle against brute force: small uniform delay regardless of failure reason
        usleep(250_000); // 250ms
        return false;
    }

    // Re-hash if needed
    if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $upd = $pdo->prepare('UPDATE users SET password_hash = :h WHERE id = :id');
        $upd->execute([':h' => $newHash, ':id' => $user['id']]);
    }

    // Defend against session fixation
    session_regenerate_id(true);

    $_SESSION['user_id']      = (int) $user['id'];
    $_SESSION['username']     = $user['username'];
    $_SESSION['role']         = $user['role'];
    $_SESSION['display_name'] = $user['display_name'] ?: $user['username'];
    $_SESSION['logged_in_at'] = time();

    // Update last login (best-effort; not fatal if it fails)
    try {
        $pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id')
            ->execute([':id' => $user['id']]);
    } catch (PDOException $e) {
        error_log('Last-login update failed: ' . $e->getMessage());
    }

    return true;
}

/**
 * End the session.
 */
function auth_logout(): void
{
    auth_session_start();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}

/**
 * True if the current request has a valid logged-in session.
 */
function auth_is_logged_in(): bool
{
    auth_session_start();
    return !empty($_SESSION['user_id']);
}

/**
 * Guard — call at the top of any admin page that requires auth. Redirects to
 * /admin/login.php if the user is not logged in.
 */
function auth_require(): void
{
    if (!auth_is_logged_in()) {
        $base  = defined('ADMIN_URL') ? ADMIN_URL
               : (defined('BASE_URL') ? rtrim(BASE_URL, '/') . '/admin' : '/admin');
        header('Location: ' . $base . '/login.php');
        exit;
    }
}

/**
 * The currently logged-in user as a small assoc array (id, username, role, display_name).
 * Returns null if not logged in.
 */
function auth_user(): ?array
{
    if (!auth_is_logged_in()) {
        return null;
    }
    return [
        'id'           => $_SESSION['user_id'],
        'username'     => $_SESSION['username'],
        'role'         => $_SESSION['role'],
        'display_name' => $_SESSION['display_name'],
    ];
}
