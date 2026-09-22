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

    // Shared, site-wide section headings — these render identically on
    // every Services and Hire Master page (not per-page content), so they
    // live here instead of being duplicated across every service/hire row.
    'shared_clients_label' => ['label' => 'Trusted Clients — Label', 'type' => 'text',
        'help' => 'Optional — defaults to "Our Trusted Clients".', 'group' => 'Trusted Clients'],

    'shared_founders_sub' => ['label' => 'Founders — Eyebrow / Subtitle', 'type' => 'text',
        'help' => 'Optional — defaults to "Leadership Team".', 'group' => 'Meet Our Founders'],
    'shared_founders_title_html' => ['label' => 'Founders — Title', 'type' => 'html',
        'help' => 'Optional — defaults to "Meet Our Founders".', 'group' => 'Meet Our Founders'],
    'shared_founders_intro_html' => ['label' => 'Founders — Intro', 'type' => 'html',
        'help' => 'Optional — only shows on the page if filled in.', 'group' => 'Meet Our Founders'],

    'shared_testimonials_sub' => ['label' => 'Testimonials — Eyebrow / Subtitle', 'type' => 'text',
        'help' => 'Optional — defaults to "Client Stories".', 'group' => 'Testimonials'],
    'shared_testimonials_title_html' => ['label' => 'Testimonials — Title', 'type' => 'html',
        'help' => 'Optional — defaults to "What clients say about us."', 'group' => 'Testimonials'],
    'shared_testimonials_intro_html' => ['label' => 'Testimonials — Intro', 'type' => 'html',
        'help' => 'Optional — only shows on the page if filled in.', 'group' => 'Testimonials'],

    'shared_contact_sub' => ['label' => 'Contact Band — Eyebrow / Subtitle', 'type' => 'text',
        'help' => 'Optional — defaults to "Get in Touch".', 'group' => 'Contact Band'],
    'shared_contact_title_html' => ['label' => 'Contact Band — Title', 'type' => 'html',
        'help' => 'Optional — defaults to "Talk to an AI Expert".', 'group' => 'Contact Band'],
    'shared_contact_intro_html' => ['label' => 'Contact Band — Intro', 'type' => 'html',
        'help' => 'Optional — only shows on the page if filled in.', 'group' => 'Contact Band'],
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
        foreach ($known_keys as $key => $cfg) {
            $raw = (string) ($_POST[$key] ?? '');
            $value = $cfg['type'] === 'html'
                ? strip_wrapping_p(sanitize_html_fragment(trim($raw)))
                : trim($raw);
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
                if (!empty($cfg['group'])) { continue; } // rendered in their own grouped cards below
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

    <p class="text-muted" style="margin: 24px 0 8px;">Shared sections — these render identically on every Services and Hire Master page, so they're edited once here instead of per-page.</p>
    <?php
    $groups = [];
    foreach ($known_keys as $key => $cfg) {
        if (!empty($cfg['group'])) { $groups[$cfg['group']][$key] = $cfg; }
    }
    foreach ($groups as $group_label => $group_fields): ?>
        <div class="admin-card">
            <div class="admin-card__body">
                <div class="repeater-row__title" style="margin-bottom:10px;"><?= e($group_label) ?></div>
                <?php foreach ($group_fields as $key => $cfg):
                    $val = $current[$key] ?? '';
                ?>
                    <div class="form-row">
                        <label for="<?= attr($key) ?>"><?= e($cfg['label']) ?></label>
                        <?php if ($cfg['type'] === 'html'): ?>
                            <textarea id="<?= attr($key) ?>" name="<?= attr($key) ?>" rows="3"><?= e($val) ?></textarea>
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
    <?php endforeach; ?>

    <button type="submit" class="admin-btn">Save settings</button>
</form>

<!-- CKEditor 5 on the shared-section title/intro fields -->
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
(function () {
    var CK_HEADING_OPTIONS = {
        options: [
            { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
            { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
            { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' },
            { model: 'heading4', view: 'h4', title: 'Heading 4', class: 'ck-heading_heading4' }
        ]
    };
    document.querySelectorAll('textarea[id^="shared_"]').forEach(function (el) {
        ClassicEditor.create(el, {
            toolbar: ['heading', '|', 'bold', 'italic', 'link', '|', 'undo', 'redo'],
            heading: CK_HEADING_OPTIONS
        }).catch(function (err) { console.error(err); });
    });
})();
</script>

<?php require __DIR__ . '/_footer.php'; ?>
