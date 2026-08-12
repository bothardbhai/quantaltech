<?php

/**
 * Media library — upload, view, copy path, and delete files.
 *
 * Supports images, PDFs, videos, and common office documents. Each upload is
 * tagged with a category (blog/page/service/media), which decides both the
 * storage folder and the Media Master filter. Files are tracked in the
 * `media` table as an index over the real files on disk — the table mirrors
 * the filesystem (upload writes both; delete removes both) rather than being
 * the source of truth on its own. The blog editor's own inline image uploads
 * (CKEditor, /uploads/blog/...) are tracked the same way and show up here too.
 */
require __DIR__ . '/bootstrap.php';

$pdo = db();
$user = auth_user();

// Upload categories: each maps to its own storage folder (see media_category_dirs()).
const MEDIA_CATEGORY_LABELS = [
    'blog'          => 'Blog',
    'page'          => 'Page',
    'service'       => 'Service',
    'media'         => 'Media',
    'success-story' => 'Success Story',
];

/**
 * Storage folder for a given category. Blog/Media get YYYY/MM subfolders;
 * Page/Service upload directly into their own folder.
 *
 * @return array{0: string, 1: string} [relative path (from site root), absolute path]
 */
function media_category_dirs(string $category): array
{
    $ym = date('Y/m');
    return match ($category) {
        'blog'          => ['/uploads/blog/' . $ym, UPLOADS_DIR . '/blog/' . $ym],
        'page'          => ['/uploads/page', UPLOADS_DIR . '/page'],
        'service'       => ['/uploads/service', UPLOADS_DIR . '/service'],
        'success-story' => ['/uploads/success-story', UPLOADS_DIR . '/success-story'],
        default         => ['/uploads/media/' . $ym, UPLOADS_DIR . '/media/' . $ym],
    };
}

/**
 * Turn an original filename into the on-disk filename: sanitized original
 * name + current timestamp + original extension (e.g. "about-us-banner-1721819254.jpg").
 * No random IDs — the name stays human-readable. Falls back to appending a
 * counter in the rare case two files land on the exact same name+second.
 */
function media_build_filename(string $original_name, string $abs_dir): string
{
    $ext  = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    $base = pathinfo($original_name, PATHINFO_FILENAME);
    $base = str_replace(' ', '-', trim($base));
    $base = preg_replace('/[^A-Za-z0-9_-]/', '', $base) ?? '';
    $base = preg_replace('/-+/', '-', $base) ?? '';
    $base = trim($base, '-');
    $base = $base === '' ? 'file' : substr($base, 0, 100);
    $ext_suffix = $ext !== '' ? '.' . $ext : '';

    $ts = time();
    $filename = $base . '-' . $ts . $ext_suffix;
    $i = 2;
    while (is_file($abs_dir . '/' . $filename)) {
        $filename = $base . '-' . $ts . '-' . $i . $ext_suffix;
        $i++;
    }
    return $filename;
}

// Extension => [type, allowed MIME types, max size in bytes]
// Office formats (docx/xlsx/pptx) are ZIP containers, so finfo often reports
// them as application/zip or application/octet-stream — both are accepted
// alongside their "proper" MIME type.
const MEDIA_ALLOWED_TYPES = [
    'jpg' => ['image', ['image/jpeg'], 5 * 1024 * 1024],
    'jpeg' => ['image', ['image/jpeg'], 5 * 1024 * 1024],
    'png' => ['image', ['image/png'], 5 * 1024 * 1024],
    'gif' => ['image', ['image/gif'], 5 * 1024 * 1024],
    'webp' => ['image', ['image/webp'], 5 * 1024 * 1024],
    'svg' => ['image', ['image/svg+xml'], 5 * 1024 * 1024],
    'pdf' => ['document', ['application/pdf'], 15 * 1024 * 1024],
    'doc' => ['document', ['application/msword'], 15 * 1024 * 1024],
    'docx' => ['document', ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/octet-stream'], 15 * 1024 * 1024],
    'xls' => ['document', ['application/vnd.ms-excel'], 15 * 1024 * 1024],
    'xlsx' => ['document', ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip', 'application/octet-stream'], 15 * 1024 * 1024],
    'ppt' => ['document', ['application/vnd.ms-powerpoint'], 15 * 1024 * 1024],
    'pptx' => ['document', ['application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/zip', 'application/octet-stream'], 15 * 1024 * 1024],
    'txt' => ['document', ['text/plain'], 15 * 1024 * 1024],
    'csv' => ['document', ['text/csv', 'text/plain', 'application/vnd.ms-excel'], 15 * 1024 * 1024],
    'mp4' => ['video', ['video/mp4'], 50 * 1024 * 1024],
    'webm' => ['video', ['video/webm'], 50 * 1024 * 1024],
    'mov' => ['video', ['video/quicktime'], 50 * 1024 * 1024],
];

/**
 * Validate and store a single uploaded file (one slot of $_FILES['media'])
 * under the given category's folder. Returns
 * ['ok' => bool, 'name' => original filename, 'message' => string].
 */
function media_process_upload(array $file, PDO $pdo, ?int $uploaded_by, string $category): array
{
    $original_name = (string) $file['name'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $msg = match ($file['error']) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'File is too large.',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
            default => 'Upload failed (code ' . $file['error'] . ').',
        };
        return ['ok' => false, 'name' => $original_name, 'message' => $msg];
    }

    $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    if (!isset(MEDIA_ALLOWED_TYPES[$ext])) {
        return ['ok' => false, 'name' => $original_name, 'message' => 'Unsupported file type: .' . $ext];
    }
    [$type, $allowed_mimes, $max_bytes] = MEDIA_ALLOWED_TYPES[$ext];

    if ((int) $file['size'] > $max_bytes) {
        return ['ok' => false, 'name' => $original_name, 'message' => 'Exceeds the ' . round($max_bytes / 1024 / 1024) . ' MB limit for this file type.'];
    }

    // Duplicate filename check, scoped to the category (same name is fine in
    // different categories since they live in different folders).
    $stmt = $pdo->prepare('SELECT id FROM media WHERE original_name = :n AND category = :c LIMIT 1');
    $stmt->execute([':n' => $original_name, ':c' => $category]);
    if ($stmt->fetch()) {
        return ['ok' => false, 'name' => $original_name, 'message' => 'A file with this name already exists in this category. Rename the file and try again.'];
    }

    // Real MIME sniff — don't trust the browser-supplied type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']) ?: '';
    if (!in_array($mime, $allowed_mimes, true)) {
        return ['ok' => false, 'name' => $original_name, 'message' => 'File content does not match a supported ' . $ext . ' file (detected ' . $mime . ').'];
    }

    [$rel_dir, $abs_dir] = media_category_dirs($category);
    if (!is_dir($abs_dir) && !mkdir($abs_dir, 0755, true) && !is_dir($abs_dir)) {
        return ['ok' => false, 'name' => $original_name, 'message' => 'Could not create the upload directory.'];
    }

    // Original filename preserved, invalid characters stripped, spaces to
    // hyphens, timestamped to stay unique — no random IDs.
    $filename = media_build_filename($original_name, $abs_dir);

    $rel_path = $rel_dir . '/' . $filename;
    $abs_path = $abs_dir . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $abs_path)) {
        return ['ok' => false, 'name' => $original_name, 'message' => 'Could not save the file to disk.'];
    }

    [$w, $h] = ($type === 'image' && $mime !== 'image/svg+xml')
        ? (getimagesize($abs_path) ?: [null, null])
        : [null, null];

    try {
        $stmt = $pdo->prepare(
            'INSERT INTO media (path, original_name, category, mime_type, size_bytes, width, height, uploaded_by)
             VALUES (:p, :on, :cat, :mt, :sz, :w, :h, :u)'
        );
        $stmt->execute([
            ':p' => $rel_path,
            ':on' => $original_name,
            ':cat' => $category,
            ':mt' => $mime,
            ':sz' => (int) $file['size'],
            ':w' => $w,
            ':h' => $h,
            ':u' => $uploaded_by,
        ]);
    } catch (PDOException $e) {
        // DB row is the only way this file will ever show up again — if it
        // can't be tracked, don't leave an orphaned file behind.
        @unlink($abs_path);
        error_log('Media insert failed: ' . $e->getMessage());
        return ['ok' => false, 'name' => $original_name, 'message' => 'Could not save the file record.'];
    }

    return ['ok' => true, 'name' => $original_name, 'message' => 'Uploaded.'];
}

// Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'upload') {
    csrf_verify_or_die();

    $category = (string) ($_POST['category'] ?? '');
    if (!isset(MEDIA_CATEGORY_LABELS[$category])) {
        flash('error', 'Choose an upload category.');
        header('Location: ' . ADMIN_URL . '/media.php');
        exit;
    }

    $successes = [];
    $failures = [];
    $files = $_FILES['media'] ?? null;

    if ($files && is_array($files['name'])) {
        $count = count($files['name']);
        for ($i = 0; $i < $count; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $single = [
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i],
            ];
            $result = $pdo
                ? media_process_upload($single, $pdo, $user['id'] ?? null, $category)
                : ['ok' => false, 'name' => $single['name'], 'message' => 'Database unavailable.'];
            if ($result['ok']) {
                $successes[] = $result['name'];
            } else {
                $failures[] = $result['name'] . ': ' . $result['message'];
            }
        }
    }

    if ($successes) {
        flash('success', count($successes) . ' file' . (count($successes) === 1 ? '' : 's') . ' uploaded: ' . implode(', ', $successes));
    }
    if ($failures) {
        flash('error', implode(' | ', $failures));
    }
    if (!$successes && !$failures) {
        flash('error', 'Choose at least one file to upload.');
    }
    header('Location: ' . ADMIN_URL . '/media.php');
    exit;
}

// Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    csrf_verify_or_die();
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0 && $pdo) {
        $stmt = $pdo->prepare('SELECT path FROM media WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if ($row) {
            // Delete from disk — tolerate the file already being gone (e.g.
            // removed manually) so the DB row (and thus the stale list entry)
            // still gets cleaned up either way.
            $abs = ROOT_DIR . $row['path'];
            if (is_file($abs) && str_starts_with(realpath($abs) ?: '', realpath(UPLOADS_DIR) ?: '')) {
                @unlink($abs);
            }
            $pdo->prepare('DELETE FROM media WHERE id = :id')->execute([':id' => $id]);
            flash('success', 'File deleted.');
        } else {
            flash('error', 'File not found — it may have already been deleted.');
        }
    }
    header('Location: ' . ADMIN_URL . '/media.php' . (isset($_POST['category']) ? '?category=' . urlencode((string) $_POST['category']) : ''));
    exit;
}

$category_filter = (string) ($_GET['category'] ?? '');
if (!isset(MEDIA_CATEGORY_LABELS[$category_filter])) {
    $category_filter = '';
}

$rows = [];
if ($pdo) {
    try {
        if ($category_filter !== '') {
            $stmt = $pdo->prepare('SELECT * FROM media WHERE category = :c ORDER BY created_at DESC LIMIT 200');
            $stmt->execute([':c' => $category_filter]);
            $rows = $stmt->fetchAll();
        } else {
            $rows = $pdo->query('SELECT * FROM media ORDER BY created_at DESC LIMIT 200')->fetchAll();
        }
    } catch (PDOException $e) {
    }
}

$admin_page_title = 'Media';
$admin_active = 'media';
require __DIR__ . '/_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>Media library</h1>
        <div class="subtitle"><?= count($rows) ?> file<?= count($rows) === 1 ? '' : 's' ?><?= $category_filter !== '' ? ' in ' . e(MEDIA_CATEGORY_LABELS[$category_filter]) : '' ?></div>
    </div>
    <div>
        <button type="button" class="admin-btn" id="open-upload-modal">Upload Media</button>
    </div>
</div>

<div style="margin-bottom:14px;">
    <a href="?" class="<?= $category_filter === '' ? 'admin-btn admin-btn--small' : 'admin-btn admin-btn--ghost admin-btn--small' ?>">All</a>
    <?php foreach (MEDIA_CATEGORY_LABELS as $cat_key => $cat_label): ?>
        <a href="?category=<?= urlencode($cat_key) ?>" class="<?= $category_filter === $cat_key ? 'admin-btn admin-btn--small' : 'admin-btn admin-btn--ghost admin-btn--small' ?>"><?= e($cat_label) ?></a>
    <?php endforeach; ?>
</div>

<div class="admin-modal-overlay" id="upload-modal-overlay">
    <div class="admin-modal">
        <form method="post" enctype="multipart/form-data" class="admin-form">
            <div class="admin-modal__head">
                <h2>Upload media</h2>
                <button type="button" class="admin-modal__close" id="close-upload-modal" aria-label="Close">&times;</button>
            </div>
            <div class="admin-modal__body">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="upload">
                <div class="form-row">
                    <label for="media-category">Upload category</label>
                    <select id="media-category" name="category" required>
                        <option value="">Select a category…</option>
                        <?php foreach (MEDIA_CATEGORY_LABELS as $cat_key => $cat_label): ?>
                            <option value="<?= attr($cat_key) ?>"><?= e($cat_label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-row" style="margin-bottom:0;">
                    <label for="media-files">Choose one or more files</label>
                    <input type="file" id="media-files" name="media[]" multiple required
                           accept=".jpg,.jpeg,.png,.gif,.webp,.svg,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.mp4,.webm,.mov">
                    <div class="help">
                        Images (JPG, PNG, GIF, WEBP, SVG) up to 5&nbsp;MB<br>
                        Documents (PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, CSV) up to 15&nbsp;MB<br>
                        Video (MP4, WEBM, MOV) up to 50&nbsp;MB
                    </div>
                </div>
            </div>
            <div class="admin-modal__foot">
                <button type="button" class="admin-btn admin-btn--ghost" id="cancel-upload-modal">Cancel</button>
                <button type="submit" class="admin-btn" id="upload-submit" disabled>Upload</button>
            </div>
        </form>
    </div>
</div>

<?php if (empty($rows)): ?>
    <div class="admin-card">
        <div class="empty-state">
            <h3>No files yet</h3>
            <p>Click "Upload Media" to add images, PDFs, videos, or documents.</p>
        </div>
    </div>
<?php else: ?>
    <div class="admin-card">
        <div class="admin-card__body">
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:14px;">
                <?php
                foreach ($rows as $r):
                    $is_image = str_starts_with((string) $r['mime_type'], 'image/');
                    $ext = strtoupper((string) pathinfo((string) $r['path'], PATHINFO_EXTENSION));
                    $display_name = pathinfo((string) $r['original_name'], PATHINFO_FILENAME);
                    ?>
                    <div style="border:1px solid var(--admin-border);border-radius:6px;padding:8px;background:#fff;">
                        <div style="aspect-ratio:1;background:#f6f7fb;border-radius:4px;display:flex;align-items:center;justify-content:center;overflow:hidden;margin-bottom:8px;">
                            <?php if ($is_image): ?>
                                <img src="<?= attr(media_url($r['path'])) ?>" alt="<?= attr($r['alt_text']) ?>" style="max-width:100%;max-height:100%;object-fit:cover;">
                            <?php else: ?>
                                <span class="media-badge"><?= e($ext !== '' ? $ext : 'FILE') ?></span>
                            <?php endif; ?>
                        </div>
                        <div style="font-size:12px;font-weight:600;word-break:break-word;" title="<?= attr($r['original_name']) ?>"><?= e($display_name !== '' ? $display_name : $r['original_name']) ?></div>
                        <div class="text-muted" style="font-size:11px;text-transform:uppercase;letter-spacing:0.3px;margin-top:2px;"><?= e(MEDIA_CATEGORY_LABELS[$r['category']] ?? $r['category']) ?></div>
                        <div class="media-card__actions">
                            <button type="button" class="admin-btn admin-btn--ghost admin-btn--small copy-path" data-path="<?= attr($r['path']) ?>">Copy</button>
                            <form method="post" onsubmit="return confirm('Delete this file permanently? This cannot be undone.');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                                <input type="hidden" name="category" value="<?= attr($category_filter) ?>">
                                <button type="submit" class="admin-btn admin-btn--danger admin-btn--small">Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
    document.querySelectorAll('.copy-path').forEach(btn => {
        btn.addEventListener('click', function() {
            navigator.clipboard.writeText(this.dataset.path).then(() => {
                const orig = this.textContent;
                this.textContent = 'Copied!';
                setTimeout(() => this.textContent = orig, 1200);
            });
        });
    });
    </script>
<?php endif; ?>

<script>
(function() {
    const overlay     = document.getElementById('upload-modal-overlay');
    const openBtn     = document.getElementById('open-upload-modal');
    const closeBtn    = document.getElementById('close-upload-modal');
    const cancelBtn   = document.getElementById('cancel-upload-modal');
    const categorySel = document.getElementById('media-category');
    const filesInput  = document.getElementById('media-files');
    const submitBtn   = document.getElementById('upload-submit');

    function open()  { overlay.classList.add('is-open'); }
    function close() { overlay.classList.remove('is-open'); }
    openBtn.addEventListener('click', open);
    closeBtn.addEventListener('click', close);
    cancelBtn.addEventListener('click', close);
    overlay.addEventListener('click', function(e) { if (e.target === overlay) close(); });

    // Upload stays disabled until both a category and at least one file are chosen.
    function updateSubmitState() {
        const hasCategory = categorySel.value !== '';
        const hasFiles = filesInput.files && filesInput.files.length > 0;
        submitBtn.disabled = !(hasCategory && hasFiles);
    }
    categorySel.addEventListener('change', updateSubmitState);
    filesInput.addEventListener('change', updateSubmitState);
    updateSubmitState();
})();
</script>

<?php require __DIR__ . '/_footer.php'; ?>
