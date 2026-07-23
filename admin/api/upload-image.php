<?php
/**
 * Image upload endpoint for CKEditor 5's SimpleUploadAdapter.
 *
 * Receives a file (multipart/form-data, key 'upload') and returns JSON:
 *   Success: {"url": "/uploads/blog/<filename>"}
 *   Failure: {"error": {"message": "..."}}
 *
 * Security:
 *   - Login required (auth_require)
 *   - CSRF token via X-CSRF-Token header (set by CKEditor config)
 *   - Whitelisted MIME types and extensions
 *   - Random filename prefix prevents guessing/overwrites
 *   - Max file size enforced
 */

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

header('Content-Type: application/json');

function fail(string $msg, int $http = 400): void
{
    http_response_code($http);
    echo json_encode(['error' => ['message' => $msg]]);
    exit;
}

// CSRF: header-based for AJAX
$submitted_token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
$expected_token  = $_SESSION['csrf_token'] ?? '';
if (!$expected_token || !is_string($submitted_token) || !hash_equals($expected_token, $submitted_token)) {
    fail('CSRF token mismatch', 419);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fail('POST required', 405);
}

if (!isset($_FILES['upload']) || $_FILES['upload']['error'] !== UPLOAD_ERR_OK) {
    $code = $_FILES['upload']['error'] ?? 'no file';
    fail('Upload failed (code ' . $code . ').');
}

$file = $_FILES['upload'];
$max_bytes = 5 * 1024 * 1024; // 5 MB
if ($file['size'] > $max_bytes) {
    fail('File exceeds 5 MB limit.');
}

// Validate MIME via finfo (don't trust the browser-supplied MIME)
$finfo    = new finfo(FILEINFO_MIME_TYPE);
$mime     = $finfo->file($file['tmp_name']) ?: '';
$ext_map  = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/gif'  => 'gif',
    'image/webp' => 'webp',
    'image/svg+xml' => 'svg',
];
if (!isset($ext_map[$mime])) {
    fail('Unsupported file type: ' . $mime);
}

// Build a safe destination filename: yyyymm/<random>-<orig-base>.<ext>
$ym       = date('Y-m');
$base     = pathinfo($file['name'], PATHINFO_FILENAME);
$base     = preg_replace('/[^a-zA-Z0-9_-]/', '-', $base) ?? '';
$base     = trim((string) $base, '-');
$base     = $base === '' ? 'image' : substr($base, 0, 60);
$prefix   = bin2hex(random_bytes(4));
$filename = $prefix . '-' . $base . '.' . $ext_map[$mime];

$rel_dir  = '/uploads/blog/' . $ym;
$abs_dir  = UPLOADS_DIR . '/blog/' . $ym;
if (!is_dir($abs_dir)) {
    if (!mkdir($abs_dir, 0755, true) && !is_dir($abs_dir)) {
        fail('Could not create upload directory.');
    }
}

$rel_path = $rel_dir . '/' . $filename;
$abs_path = $abs_dir . '/' . $filename;

if (!move_uploaded_file($file['tmp_name'], $abs_path)) {
    fail('Could not save file.');
}

// Get image dims (skip for SVG)
[$w, $h] = ($mime !== 'image/svg+xml')
    ? (getimagesize($abs_path) ?: [null, null])
    : [null, null];

// Track in media table — best-effort, not fatal if it fails
$pdo = db();
if ($pdo) {
    try {
        $user = auth_user();
        $stmt = $pdo->prepare(
            'INSERT INTO media (path, original_name, mime_type, size_bytes, width, height, uploaded_by)
             VALUES (:p, :on, :mt, :sz, :w, :h, :u)'
        );
        $stmt->execute([
            ':p' => $rel_path,
            ':on'=> $file['name'],
            ':mt'=> $mime,
            ':sz'=> $file['size'],
            ':w' => $w,
            ':h' => $h,
            ':u' => $user['id'] ?? null,
        ]);
    } catch (PDOException $e) {
        error_log('Media insert failed: ' . $e->getMessage());
    }
}

echo json_encode(['url' => $rel_path]);
