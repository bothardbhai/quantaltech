<?php

/**
 * Contact form POST handler — returns JSON, always exits.
 * Loaded by the file-path router like any page, but calls exit() immediately
 * so the header/footer wrapping from index.php is bypassed.
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// CSRF verification (session started inside auth_session_start via csrf_token())
auth_session_start();
$submitted = $_POST['_csrf'] ?? '';
$expected = $_SESSION['csrf_token'] ?? '';
if (!$expected || !is_string($submitted) || !hash_equals($expected, $submitted)) {
    http_response_code(419);
    echo json_encode(['success' => false, 'message' => 'Session expired — please reload the page and try again.']);
    exit;
}

// Honeypot — silently accept bots so they think the form worked
if (!empty($_POST['form_botcheck'])) {
    echo json_encode(['success' => true, 'message' => 'Thank you!']);
    exit;
}

// Collect and sanitise
$first_name = trim(strip_tags($_POST['form_first_name'] ?? ''));
$last_name = trim(strip_tags($_POST['form_last_name'] ?? ''));
$name = trim(strip_tags($_POST['form_name'] ?? ''));
if ($name === '' && ($first_name !== '' || $last_name !== '')) {
    $name = trim($first_name . ' ' . $last_name);
}
$company = trim(strip_tags($_POST['form_company'] ?? ''));
$email = trim($_POST['form_email'] ?? '');
$subject = trim(strip_tags($_POST['form_subject'] ?? ''));
$phone = trim(strip_tags($_POST['form_phone'] ?? ''));
$message = trim(strip_tags($_POST['form_message'] ?? ''));

// Validate
$errors = [];
if ($name === '')
    $errors[] = 'Your name is required.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL))
    $errors[] = 'A valid email address is required.';
if ($subject === '')
    $errors[] = 'Please enter a subject.';
if (mb_strlen($message) < 10)
    $errors[] = 'Message must be at least 10 characters.';

if ($errors) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

// Escape for HTML email bodies
$en = static fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
$ip = $en($_SERVER['REMOTE_ADDR'] ?? '');

$to_addr = defined('CONTACT_TO_EMAIL') ? CONTACT_TO_EMAIL : 'contact@quantaltech.ai';
$to_name = defined('CONTACT_TO_NAME') ? CONTACT_TO_NAME : 'Quantal AI Team';

$company_row = $company !== ''
    ? '<tr style="background:#f9f9f9;"><td style="padding:10px 8px;font-weight:600;color:#555;">Company</td>'
        . '<td style="padding:10px 8px;">' . $en($company) . '</td></tr>'
    : '';

// $to_addr = 'developer@savit.in';
// $to_name = 'Quantal AI Team';

// Notification email to the team
$notify_html = <<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <body style="font-family:sans-serif;color:#333;max-width:600px;margin:0 auto;padding:20px;">
      <h2 style="color:#1d2327;border-bottom:2px solid #eee;padding-bottom:10px;">New Contact Form Submission</h2>
      <table style="width:100%;border-collapse:collapse;font-size:15px;">
        <tr><td style="padding:10px 8px;font-weight:600;width:110px;color:#555;">Name</td>
            <td style="padding:10px 8px;">{$en($name)}</td></tr>
        <tr style="background:#f9f9f9;">
            <td style="padding:10px 8px;font-weight:600;color:#555;">Email</td>
            <td style="padding:10px 8px;"><a href="mailto:{$en($email)}">{$en($email)}</a></td></tr>
        <tr><td style="padding:10px 8px;font-weight:600;color:#555;">Phone</td>
            <td style="padding:10px 8px;">{$en($phone)}</td></tr>
        {$company_row}
        <tr style="background:#f9f9f9;">
            <td style="padding:10px 8px;font-weight:600;color:#555;">Subject</td>
            <td style="padding:10px 8px;">{$en($subject)}</td></tr>
        <tr><td style="padding:10px 8px;font-weight:600;color:#555;vertical-align:top;">Message</td>
            <td style="padding:10px 8px;line-height:1.7;">{$en($message)}</td></tr>
      </table>
      <p style="color:#aaa;font-size:12px;margin-top:24px;border-top:1px solid #eee;padding-top:12px;">
        Sent via quantaltech.ai contact form &middot; IP: {$ip}
      </p>
    </body>
    </html>
    HTML;

$ok = send_email($to_addr, $to_name, "Contact: {$subject}", $notify_html);

// Auto-reply to the sender
if ($ok) {
    $reply_html = <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <body style="font-family:sans-serif;color:#333;max-width:600px;margin:0 auto;padding:20px;">
          <h2 style="color:#1d2327;">Hi {$en($name)}, thanks for reaching out!</h2>
          <p style="line-height:1.7;">We received your message and a member of our team will get back to you within 1 business day.</p>
          <p style="line-height:1.7;">
            In the meantime, explore our
            <a href="https://quantaltech.ai/blog" style="color:#2271b1;">AI engineering blog</a>
            or learn more about our
            <a href="https://quantaltech.ai/services" style="color:#2271b1;">services</a>.
          </p>
          <br>
          <p>Best regards,<br><strong>The Quantal AI Team</strong></p>
          <hr style="border:none;border-top:1px solid #eee;margin:24px 0;">
          <p style="color:#aaa;font-size:12px;">Quantal AI &middot; contact@quantaltech.ai &middot; quantaltech.ai</p>
        </body>
        </html>
        HTML;
    send_email($email, $name, 'We received your message — Quantal AI', $reply_html);
}

echo json_encode([
    'success' => $ok,
    'message' => $ok
        ? "Thank you, {$en($name)}! We'll be in touch within 1 business day."
        : 'Email delivery failed. Please try again or email us at contact@quantaltech.ai.',
    'redirect' => $ok ? url('/thank-you') : null,
]);
exit;
