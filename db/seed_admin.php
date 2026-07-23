<?php
/**
 * Admin seeder — creates or resets the first admin user.
 *
 * Usage (CLI):
 *   php db/seed_admin.php <username> <email> <password>
 *
 * Example:
 *   php db/seed_admin.php admin admin@quantaltech.ai 'StrongP@ssw0rd!'
 *
 * Uses bcrypt (PASSWORD_DEFAULT). Re-running with the same username updates
 * the password (handy for password resets when locked out).
 */

declare(strict_types=1);

// Run only from CLI
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This script must be run from the command line.\n");
}

if ($argc < 4) {
    fwrite(STDERR, "Usage: php db/seed_admin.php <username> <email> <password>\n");
    exit(1);
}

[$_, $username, $email, $password] = $argv;

if (strlen($password) < 10) {
    fwrite(STDERR, "Password must be at least 10 characters.\n");
    exit(1);
}

require __DIR__ . '/../config/config.php';
require __DIR__ . '/../core/db.php';

$pdo = db();
if (!$pdo) {
    fwrite(STDERR, "Cannot connect to database. Check config/config.php\n");
    exit(1);
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare(
    'INSERT INTO users (username, email, password_hash, role, is_active)
     VALUES (:u, :e, :h, "admin", 1)
     ON DUPLICATE KEY UPDATE password_hash = :h2, email = :e2, is_active = 1'
);
$stmt->execute([
    ':u'  => $username,
    ':e'  => $email,
    ':h'  => $hash,
    ':h2' => $hash,
    ':e2' => $email,
]);

echo "Admin user '{$username}' is ready.\n";
