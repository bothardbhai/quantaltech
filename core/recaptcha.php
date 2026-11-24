<?php
/**
 * Public-form spam protection — Google reCAPTCHA v3 + an off-screen honeypot.
 *
 * Keys live in config/config.php (RECAPTCHA_SITE_KEY / RECAPTCHA_SECRET_KEY).
 * Only the site key is ever written to the page; the secret is used solely by
 * recaptcha_verify() below.
 *
 * Usage:
 *   In the form:    <?= honeypot_field() ?>
 *   Before the JS:  <?= recaptcha_script() ?>
 *   In the JS:      qtRecaptcha(form, 'contact_form').then(function (fd) { return fetch(..., { body: fd }); })
 *   Handling POST:  if (!recaptcha_verify('contact_form')) { ...reject... }
 */

declare(strict_types=1);

// Minimum score (0.0 = bot … 1.0 = human) a submission needs to pass.
// Change it here, or override by defining RECAPTCHA_MIN_SCORE in config.php.
if (!defined('RECAPTCHA_MIN_SCORE')) {
    define('RECAPTCHA_MIN_SCORE', 0.5);
}

// Name of the honeypot input. Real users never see or fill it.
const HONEYPOT_FIELD_NAME = 'website';

/**
 * Off-screen text input that bots tend to fill in. Positioned out of view via
 * the .qt-hp class (assets/css/style.css) rather than display:none.
 */
function honeypot_field(): string
{
    return '<div class="qt-hp" aria-hidden="true"><input type="text" name="' . HONEYPOT_FIELD_NAME
        . '" value="" tabindex="-1" autocomplete="off"></div>';
}

/**
 * True when the honeypot field came back non-empty (i.e. a bot filled it).
 */
function honeypot_tripped(): bool
{
    return !empty($_POST[HONEYPOT_FIELD_NAME]);
}

/**
 * The reCAPTCHA v3 loader plus a small qtRecaptcha(form, action) helper that
 * resolves to the form's FormData with a fresh token appended. Emitted once
 * per page, even when several forms call it.
 */
function recaptcha_script(): string
{
    static $done = false;
    if ($done) {
        return '';
    }
    $done = true;

    $site_key = defined('RECAPTCHA_SITE_KEY') ? (string) RECAPTCHA_SITE_KEY : '';
    $key_attr = htmlspecialchars($site_key, ENT_QUOTES, 'UTF-8');
    $key_js = json_encode($site_key, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

    return <<<HTML
        <script src="https://www.google.com/recaptcha/api.js?render={$key_attr}"></script>
        <script>
            window.qtRecaptcha = function (form, action) {
                var fd = new FormData(form);
                return new Promise(function (resolve) {
                    if (!window.grecaptcha || !grecaptcha.ready) { resolve(fd); return; }
                    grecaptcha.ready(function () {
                        grecaptcha.execute({$key_js}, { action: action })
                            .then(function (token) { fd.append('g-recaptcha-response', token); resolve(fd); })
                            .catch(function () { resolve(fd); });
                    });
                });
            };
        </script>
        HTML;
}

/**
 * Verify the submitted reCAPTCHA v3 token with Google. Fails closed: a missing
 * token, network error, unsuccessful response, wrong action, or a score below
 * RECAPTCHA_MIN_SCORE all return false.
 */
function recaptcha_verify(string $expected_action): bool
{
    $token = $_POST['g-recaptcha-response'] ?? '';
    $secret = defined('RECAPTCHA_SECRET_KEY') ? (string) RECAPTCHA_SECRET_KEY : '';

    if (!is_string($token) || $token === '') {
        error_log("reCAPTCHA rejected ({$expected_action}): missing token");
        return false;
    }
    if ($secret === '') {
        error_log('reCAPTCHA rejected: RECAPTCHA_SECRET_KEY is not configured');
        return false;
    }

    $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'secret' => $secret,
            'response' => $token,
            'remoteip' => (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $body = curl_exec($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if (!is_string($body) || $body === '') {
        error_log("reCAPTCHA rejected ({$expected_action}): verification request failed: {$curl_error}");
        return false;
    }

    $result = json_decode($body, true);
    if (!is_array($result)) {
        error_log("reCAPTCHA rejected ({$expected_action}): unreadable verification response");
        return false;
    }

    $success = ($result['success'] ?? false) === true;
    $action = (string) ($result['action'] ?? '');
    $score = (float) ($result['score'] ?? 0);

    if (!$success || $action !== $expected_action || $score < (float) RECAPTCHA_MIN_SCORE) {
        $codes = implode(',', (array) ($result['error-codes'] ?? []));
        error_log("reCAPTCHA rejected ({$expected_action}): success=" . ($success ? '1' : '0')
            . " action={$action} score={$score} errors={$codes}");
        return false;
    }

    return true;
}
