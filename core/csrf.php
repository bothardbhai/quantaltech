<?php
/**
 * CSRF — synchronizer token pattern.
 *
 * Every admin form embeds a hidden token field. Every state-changing POST
 * verifies the token matches the session-stored value. Token rotates on login
 * (via session_regenerate_id and a fresh _SESSION).
 *
 * Usage:
 *   In the form:    <?= csrf_field() ?>
 *   Handling POST:  csrf_verify_or_die();
 */

declare(strict_types=1);

/**
 * Get (lazily creating) the current CSRF token.
 */
function csrf_token(): string
{
    auth_session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Output a hidden form field carrying the token.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Verify a submitted token. On mismatch, abort with HTTP 419 (a popular code
 * for "page expired / CSRF").
 */
function csrf_verify_or_die(): void
{
    auth_session_start();
    $submitted = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $expected  = $_SESSION['csrf_token'] ?? '';
    if (!$expected || !is_string($submitted) || !hash_equals($expected, $submitted)) {
        http_response_code(419);
        exit('CSRF token mismatch. Please reload the page and try again.');
    }
}
