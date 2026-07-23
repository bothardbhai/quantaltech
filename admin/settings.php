<?php
/**
 * Settings — site-wide configuration stored in the `settings` key/value table.
 * These act as defaults when individual pages/posts don't override.
 */
require __DIR__ . '/bootstrap.php';

$pdo = db();
if (!$pdo) {
    die('Database connection failed.');
}

// Settings we manage in the UI
$known_keys = [
    'site_name'    => ['label' => 'Site name',    'type' => 'text',  'help' => ''],
    'description'  => ['label' => 'Default meta description', 'type' => 'textarea', 'help' => 'Used on pages without a specific description.'],
    'og_image'     => ['label' => 'Default OG image', 'type' => 'text', 'help' => 'Path or URL. Used for pages with no specific OG image set.'],
    'organization' => ['label' => 'Organization JSON-LD', 'type' => 'json', 'help' => 'Site-wide Organization schema. Injected on every page.'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_or_die();
    $errors = [];

    // Validate JSON for the organization field
    $org = trim((string)($_POST['organization'] ?? ''));
    if ($org !== '') {
        json_decode($org);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $errors[] = 'Organization JSON-LD is invalid: ' . json_last_error_msg();
        }
    }

    if ($errors) {
        foreach ($errors as $e) flash('error', $e);
    } else {
        $stmt = $pdo->prepare('INSERT INTO settings (`key`, `value`) VALUES (:k, :v) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)');
        foreach ($known_keys as $key => $_) {
            $value = trim((string)($_POST[$key] ?? ''));
            $stmt->execute([':k' => $key, ':v' => $value]);
        }
        flash('success', 'Settings saved.');
    }
    header('Location: ' . ADMIN_URL . '/settings.php');
    exit;
}

// Load current values
$current = [];
foreach ($pdo->query('SELECT `key`, `value` FROM settings')->fetchAll() as $row) {
    $current[$row['key']] = $row['value'];
}

$admin_page_title = 'Settings';
$admin_active     = 'settings';
require __DIR__ . '/_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>Settings</h1>
        <div class="subtitle">Site-wide defaults. Used when pages or posts don't have their own value.</div>
    </div>
</div>

<form method="post" class="admin-form" style="max-width:780px;">
    <?= csrf_field() ?>

    <div class="admin-card">
        <div class="admin-card__body">
            <?php foreach ($known_keys as $key => $cfg):
                $val = $current[$key] ?? '';
            ?>
                <div class="form-row">
                    <label for="<?= attr($key) ?>"><?= e($cfg['label']) ?></label>
                    <?php if ($cfg['type'] === 'textarea'): ?>
                        <textarea id="<?= attr($key) ?>" name="<?= attr($key) ?>" rows="3"><?= e($val) ?></textarea>
                    <?php elseif ($cfg['type'] === 'json'): ?>
                        <textarea id="<?= attr($key) ?>" name="<?= attr($key) ?>" rows="8" class="json-textarea" placeholder='{"@context":"https://schema.org","@type":"Organization","name":"..."}'><?= e($val) ?></textarea>
                    <?php else: ?>
                        <input type="text" id="<?= attr($key) ?>" name="<?= attr($key) ?>" maxlength="500" value="<?= attr($val) ?>">
                    <?php endif; ?>
                    <?php if ($cfg['help']): ?>
                        <div class="help"><?= e($cfg['help']) ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <button type="submit" class="admin-btn">Save settings</button>
</form>

<?php require __DIR__ . '/_footer.php'; ?>
