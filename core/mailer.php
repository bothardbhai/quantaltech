<?php
declare(strict_types=1);

/**
 * Send an email using SMTP (configured via SMTP_* constants in config.php).
 * Falls back to PHP mail() when SMTP_HOST is empty — handy for local dev
 * with a sendmail stub (MailHog, Mailtrap localhost proxy, etc.).
 *
 * @param string $to_email  Recipient address
 * @param string $to_name   Recipient display name
 * @param string $subject   Subject line
 * @param string $html_body HTML email body
 * @param string $text_body Plain-text alternative (auto-derived from HTML when empty)
 */
function send_email(
    string $to_email,
    string $to_name,
    string $subject,
    string $html_body,
    string $text_body = ''
): bool {
    if ($text_body === '') {
        $text_body = trim(strip_tags(
            str_replace(['<br>', '<br/>', '<br />', '</p>', '</li>', '</h2>', '</h3>'], "\n", $html_body)
        ));
    }

    if (!defined('SMTP_HOST') || SMTP_HOST === '') {
        return _mailer_fallback($to_email, $to_name, $subject, $html_body);
    }

    return _mailer_smtp($to_email, $to_name, $subject, $html_body, $text_body);
}

/**
 * PHP mail() fallback (local dev / shared hosting with sendmail configured).
 */
function _mailer_fallback(string $to_email, string $to_name, string $subject, string $html): bool
{
    $from = defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : 'noreply@example.com';
    $name = defined('SMTP_FROM_NAME')  ? SMTP_FROM_NAME  : 'Quantal AI';

    $headers = implode("\r\n", [
        'From: =?UTF-8?B?' . base64_encode($name) . "?= <{$from}>",
        "Reply-To: {$from}",
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'X-Mailer: PHP/' . PHP_VERSION,
    ]);

    return @mail(
        $to_email,
        '=?UTF-8?B?' . base64_encode($subject) . '?=',
        $html,
        $headers
    );
}

/**
 * Pure-PHP SMTP sender — no external libraries required.
 * Supports STARTTLS (port 587), SSL/TLS (port 465), and plain (port 25).
 */
function _mailer_smtp(
    string $to_email,
    string $to_name,
    string $subject,
    string $html_body,
    string $text_body
): bool {
    $host      = SMTP_HOST;
    $port      = (int) (defined('SMTP_PORT') ? SMTP_PORT : 587);
    $enc       = strtolower(defined('SMTP_ENCRYPTION') ? SMTP_ENCRYPTION : 'tls');
    $user      = defined('SMTP_USERNAME') ? SMTP_USERNAME : '';
    $pass      = defined('SMTP_PASSWORD') ? SMTP_PASSWORD : '';
    $from      = SMTP_FROM_EMAIL;
    $from_name = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'Quantal AI';

    // Open socket (ssl:// prefix for implicit TLS on port 465)
    $prefix = ($enc === 'ssl') ? 'ssl://' : '';
    $conn   = @stream_socket_client("{$prefix}{$host}:{$port}", $errno, $errstr, 15);
    if (!$conn) {
        error_log("SMTP: cannot connect to {$host}:{$port} — {$errstr} ({$errno})");
        return false;
    }
    stream_set_timeout($conn, 15);

    // Read a multi-line SMTP response; last line has a space at position 3
    $read = static function () use ($conn): string {
        $buf = '';
        while (($line = fgets($conn, 4096)) !== false) {
            $buf .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        return $buf;
    };

    $cmd = static function (string $command) use ($conn, $read): string {
        fwrite($conn, $command . "\r\n");
        return $read();
    };

    // Server greeting (220 ...)
    $greeting = $read();
    if (strpos($greeting, '220') === false) {
        error_log("SMTP: bad greeting: {$greeting}");
        fclose($conn);
        return false;
    }

    $domain = gethostname() ?: 'localhost';

    $cmd("EHLO {$domain}");

    // STARTTLS upgrade for explicit TLS (port 587)
    if ($enc === 'tls') {
        $resp = $cmd('STARTTLS');
        if (strpos($resp, '220') === false) {
            error_log("SMTP: STARTTLS rejected — {$resp}");
            fclose($conn);
            return false;
        }
        if (!stream_socket_enable_crypto($conn, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            error_log('SMTP: TLS handshake failed');
            fclose($conn);
            return false;
        }
        // Re-introduce after TLS upgrade
        $cmd("EHLO {$domain}");
    }

    // AUTH LOGIN
    if ($user !== '') {
        $cmd('AUTH LOGIN');
        $cmd(base64_encode($user));
        $resp = $cmd(base64_encode($pass));
        if (strpos($resp, '235') === false) {
            error_log("SMTP: AUTH LOGIN failed — {$resp}");
            fclose($conn);
            return false;
        }
    }

    // Envelope
    $cmd("MAIL FROM:<{$from}>");
    $cmd("RCPT TO:<{$to_email}>");
    $cmd('DATA');

    // Build multipart/alternative MIME message
    $boundary = 'qai_' . bin2hex(random_bytes(8));
    $message  = implode("\r\n", [
        'Date: ' . date('r'),
        'From: =?UTF-8?B?' . base64_encode($from_name) . "?= <{$from}>",
        'To: =?UTF-8?B?' . base64_encode($to_name) . "?= <{$to_email}>",
        'Subject: =?UTF-8?B?' . base64_encode($subject) . '?=',
        'MIME-Version: 1.0',
        "Content-Type: multipart/alternative; boundary=\"{$boundary}\"",
        '',
        "--{$boundary}",
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: quoted-printable',
        '',
        quoted_printable_encode($text_body),
        '',
        "--{$boundary}",
        'Content-Type: text/html; charset=UTF-8',
        'Content-Transfer-Encoding: quoted-printable',
        '',
        quoted_printable_encode($html_body),
        '',
        "--{$boundary}--",
    ]);

    fwrite($conn, $message . "\r\n.\r\n");
    $read();
    $cmd('QUIT');
    fclose($conn);

    return true;
}
