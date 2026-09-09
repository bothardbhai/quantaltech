<?php

/**
 * Hire form POST handler — returns JSON, always exits.
 * Loaded by the file-path router like any page, but calls exit() immediately
 * so the header/footer wrapping from index.php is bypassed. Mirrors
 * pages/contact-submit.php's structure exactly (same CSRF/honeypot/DB-first
 * pattern), against the dedicated `hire_submissions` table instead of the
 * shared `contact_submissions` table.
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
$name = trim(strip_tags($_POST['name'] ?? ''));
$email = trim($_POST['email'] ?? '');
$phone = trim(strip_tags($_POST['phone'] ?? ''));
$company = trim(strip_tags($_POST['company'] ?? ''));
$country = trim(strip_tags($_POST['country'] ?? ''));
$hiring_model = trim(strip_tags($_POST['hiring_model'] ?? ''));
$project_details = trim(strip_tags($_POST['project_details'] ?? ''));

// Validate
$errors = [];
if ($name === '')
    $errors[] = 'Your name is required.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL))
    $errors[] = 'A valid email address is required.';
if (mb_strlen($project_details) < 10)
    $errors[] = 'Please tell us a bit more about your project (at least 10 characters).';

if ($errors) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

// Capture submission metadata
$ip_raw = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
$page_url = trim((string) ($_POST['page_url'] ?? ''));
if ($page_url === '') {
    $page_url = trim((string) ($_SERVER['HTTP_REFERER'] ?? ''));
}
$page_url = mb_substr(strip_tags($page_url), 0, 500);

// Store in the database first — this is the source of truth for the submission.
// Email delivery below is best-effort and must never affect success/failure.
$pdo = db();
$db_ok = false;
if ($pdo) {
    try {
        $pdo->prepare(
            'INSERT INTO hire_submissions
                (name, email, phone, company, country, hiring_model, project_details, page_url, ip_address)
             VALUES (:name, :email, :phone, :company, :country, :hiring_model, :project_details, :page_url, :ip)'
        )->execute([
            ':name' => $name,
            ':email' => $email,
            ':phone' => $phone,
            ':company' => $company,
            ':country' => $country,
            ':hiring_model' => $hiring_model,
            ':project_details' => $project_details,
            ':page_url' => $page_url,
            ':ip' => $ip_raw,
        ]);
        $db_ok = true;
    } catch (PDOException $e) {
        error_log('Hire submission DB insert failed: ' . $e->getMessage());
    }
} else {
    error_log('Hire submission DB insert skipped: no DB connection');
}

if (!$db_ok) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Something went wrong. Please try again or email us at contact@quantaltech.ai.']);
    exit;
}

// Escape for HTML email bodies
$en = static fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
$ip = $en($ip_raw);

$to_addr = defined('CONTACT_TO_EMAIL') ? CONTACT_TO_EMAIL : 'contact@quantaltech.ai';
$to_name = defined('CONTACT_TO_NAME') ? CONTACT_TO_NAME : 'Quantal AI Team';

$optional_row = static function (string $label, string $value): string {
    if ($value === '') {
        return '';
    }
    $en = static fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
    return '<tr style="background:#f9f9f9;"><td style="padding:10px 8px;font-weight:600;color:#555;">' . $en($label) . '</td>'
        . '<td style="padding:10px 8px;">' . $en($value) . '</td></tr>';
};

// Notification email to the team
$notify_html = <<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <body style="font-family:sans-serif;color:#333;max-width:600px;margin:0 auto;padding:20px;">
      <h2 style="color:#1d2327;border-bottom:2px solid #eee;padding-bottom:10px;">New Hire Enquiry</h2>
      <table style="width:100%;border-collapse:collapse;font-size:15px;">
        <tr><td style="padding:10px 8px;font-weight:600;width:140px;color:#555;">Form Type</td>
            <td style="padding:10px 8px;">Hire</td></tr>
        <tr style="background:#f9f9f9;"><td style="padding:10px 8px;font-weight:600;color:#555;">Name</td>
            <td style="padding:10px 8px;">{$en($name)}</td></tr>
        <tr><td style="padding:10px 8px;font-weight:600;color:#555;">Email</td>
            <td style="padding:10px 8px;"><a href="mailto:{$en($email)}">{$en($email)}</a></td></tr>
        {$optional_row('Phone', $phone)}
        {$optional_row('Company', $company)}
        {$optional_row('Country', $country)}
        {$optional_row('Hiring Model', $hiring_model)}
        <tr><td style="padding:10px 8px;font-weight:600;color:#555;vertical-align:top;">Project Details</td>
            <td style="padding:10px 8px;line-height:1.7;">{$en($project_details)}</td></tr>
        <tr style="background:#f9f9f9;"><td style="padding:10px 8px;font-weight:600;color:#555;">Page URL</td>
            <td style="padding:10px 8px;">{$en($page_url)}</td></tr>
        <tr><td style="padding:10px 8px;font-weight:600;color:#555;">Submitted</td>
            <td style="padding:10px 8px;">{$en(date('Y-m-d H:i:s'))}</td></tr>
      </table>
      <p style="color:#aaa;font-size:12px;margin-top:24px;border-top:1px solid #eee;padding-top:12px;">
        Sent via quantaltech.ai hire form &middot; IP: {$ip}
      </p>
    </body>
    </html>
    HTML;

$notify_ok = send_email($to_addr, $to_name, "Hire Enquiry: {$name}", $notify_html);
if (!$notify_ok) {
    error_log('Hire submission notification email failed for ' . $email);
}

// Auto-reply to the sender (best-effort — its outcome never affects the response below)
if ($notify_ok) {
    $reply_html = <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <body style="font-family:sans-serif;color:#333;max-width:600px;margin:0 auto;padding:20px;">
          <h2 style="color:#1d2327;">Hi {$en($name)}, thanks for reaching out!</h2>
          <p style="line-height:1.7;">We received your hire enquiry and a member of our team will get back to you within 1 business day.</p>
          <p style="line-height:1.7;">
            In the meantime, explore our
            <a href="https://quantaltech.ai/hire-ai-engineers" style="color:#2271b1;">AI engineer profiles</a>
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
    $reply_ok = send_email($email, $name, 'We received your enquiry — Quantal AI', $reply_html);
    if (!$reply_ok) {
        error_log('Hire submission auto-reply email failed for ' . $email);
    }
}

echo json_encode([
    'success' => true,
    'message' => "Thank you, {$en($name)}! Our team will reach out within 1 business day.",
    'redirect' => url('/thank-you'),
]);
exit;
