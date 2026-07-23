<?php
/**
 * Newsletter subscription POST handler - returns JSON, always exits.
 */
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// CSRF
auth_session_start();
$submitted = $_POST['_csrf'] ?? '';
$expected  = $_SESSION['csrf_token'] ?? '';
if (!$expected || !is_string($submitted) || !hash_equals($expected, $submitted)) {
    http_response_code(419);
    echo json_encode(['success' => false, 'message' => 'Session expired - please reload the page.']);
    exit;
}

$email = trim($_POST['email'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

// Persist to DB if available
$pdo = db();
if ($pdo) {
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO newsletter_subscribers (email, ip_address)
             VALUES (:email, :ip)
             ON DUPLICATE KEY UPDATE
               status        = 'active',
               subscribed_at = CURRENT_TIMESTAMP,
               ip_address    = VALUES(ip_address)"
        );
        $stmt->execute([
            ':email' => $email,
            ':ip'    => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    } catch (PDOException $e) {
        error_log('Newsletter DB error: ' . $e->getMessage());
        // Continue - still send confirmation even if DB insert failed
    }
}

// Confirmation email to subscriber
$en = static fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');

$confirm_html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<body style="font-family:sans-serif;color:#333;max-width:600px;margin:0 auto;padding:20px;">
  <h2 style="color:#1d2327;">You're subscribed to Quantal AI Insights!</h2>
  <p style="line-height:1.7;">
    Thanks for signing up. We'll send you practical AI engineering content - no spam, unsubscribe any time.
  </p>
  <p style="line-height:1.7;">While you wait, check out what we've published already:</p>
  <p>
    <a href="https://quantaltech.ai/blog" style="color:#2271b1;font-weight:600;">
      quantaltech.ai/blog &rarr;
    </a>
  </p>
  <br>
  <p>The Quantal AI Team</p>
  <hr style="border:none;border-top:1px solid #eee;margin:24px 0;">
  <p style="color:#aaa;font-size:12px;">
    Quantal AI &middot; contact@quantaltech.ai &middot; quantaltech.ai<br>
    Subscribed as: {$en($email)}
  </p>
</body>
</html>
HTML;

send_email($email, $email, 'Welcome to Quantal AI Insights', $confirm_html);

echo json_encode([
    'success' => true,
    'message' => "You're subscribed! Check your inbox for a confirmation.",
]);
exit;
