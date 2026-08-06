<?php
/**
 * Pages module — list all .php files under /pages and let admin edit their
 * SEO metadata. The file path on disk IS the URL; the admin only manages
 * metadata, never creates or deletes pages.
 */

require __DIR__ . '/bootstrap.php';

$pdo = db();
if (!$pdo) {
    die('Database connection failed. Check config/config.php.');
}

$action = $_GET['action'] ?? 'list';

// ===========================================================================
// EDIT view & save
// ===========================================================================
if ($action === 'edit') {
    $path = $_GET['path'] ?? '';
    // Only accept paths that are real, discoverable templates on disk.
    $valid_paths = router_discover_pages();
    if (!in_array($path, $valid_paths, true)) {
        flash('error', 'Page not found on disk: ' . $path);
        header('Location: ' . ADMIN_URL . '/pages.php'); exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_verify_or_die();

        $title       = trim((string) ($_POST['title'] ?? ''));
        $description = trim((string) ($_POST['meta_description'] ?? ''));
        $keywords    = trim((string) ($_POST['meta_keywords'] ?? ''));
        $og_image    = trim((string) ($_POST['og_image'] ?? ''));
        $canonical   = trim((string) ($_POST['canonical'] ?? ''));
        $is_pub      = isset($_POST['is_published']) ? 1 : 0;
        $notes       = trim((string) ($_POST['notes'] ?? ''));

        // Multiple JSON-LD schema blocks — stored as one JSON array
        // [{type, label, code}, ...] in the schema_json column (same column
        // that used to hold a single raw JSON-LD string; jsonld_multi() on
        // the frontend understands both shapes).
        $schema_blocks = svc_build_repeater($_POST, ['type' => 'schema_type', 'label' => 'schema_label', 'code' => 'schema_code']);
        $schema_errors = [];
        foreach ($schema_blocks as $idx => $block) {
            if ($block['code'] === '') { continue; }
            json_decode($block['code']);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $schema_errors[] = 'JSON-LD schema #' . ($idx + 1) . ($block['label'] !== '' ? " ({$block['label']})" : '')
                    . ' is invalid: ' . json_last_error_msg();
            }
        }
        if ($schema_errors) {
            foreach ($schema_errors as $err) { flash('error', $err); }
            header('Location: ' . ADMIN_URL . '/pages.php?action=edit&path=' . urlencode($path));
            exit;
        }
        $schema = json_encode($schema_blocks, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        // Upsert
        $stmt = $pdo->prepare(
            'INSERT INTO pages (path, title, meta_description, meta_keywords, og_image, canonical, schema_json, is_published, notes)
             VALUES (:path, :title, :desc, :kw, :og, :can, :schema, :pub, :notes)
             ON DUPLICATE KEY UPDATE
                title = VALUES(title),
                meta_description = VALUES(meta_description),
                meta_keywords = VALUES(meta_keywords),
                og_image = VALUES(og_image),
                canonical = VALUES(canonical),
                schema_json = VALUES(schema_json),
                is_published = VALUES(is_published),
                notes = VALUES(notes)'
        );
        $stmt->execute([
            ':path'   => $path,
            ':title'  => $title,
            ':desc'   => $description,
            ':kw'     => $keywords,
            ':og'     => $og_image,
            ':can'    => $canonical,
            ':schema' => $schema,
            ':pub'    => $is_pub,
            ':notes'  => $notes,
        ]);

        flash('success', 'Saved SEO metadata for ' . $path);
        header('Location: ' . ADMIN_URL . '/pages.php?action=edit&path=' . urlencode($path));
        exit;
    }

    // Load current data (or empty defaults)
    $stmt = $pdo->prepare('SELECT * FROM pages WHERE path = :p LIMIT 1');
    $stmt->execute([':p' => $path]);
    $page = $stmt->fetch() ?: [
        'path' => $path, 'title' => '', 'meta_description' => '', 'meta_keywords' => '',
        'og_image' => '', 'canonical' => '', 'schema_json' => '', 'is_published' => 1, 'notes' => '',
    ];

    $admin_page_title = 'Edit Page SEO';
    $admin_active     = 'pages';
    require __DIR__ . '/_header.php';
    ?>

    <script src="<?= ADMIN_URL ?>/assets/js/repeater.js"></script>

    <div class="admin-page-header">
        <div>
            <h1>Edit Page SEO</h1>
            <div class="subtitle">URL: <code class="text-mono"><?= e($path) ?></code></div>
        </div>
        <div>
            <a href="<?= attr($path) ?>" target="_blank" rel="noopener" class="admin-btn admin-btn--ghost">View page →</a>
            <a href="<?= ADMIN_URL ?>/pages.php" class="admin-btn admin-btn--ghost">Back to list</a>
        </div>
    </div>

    <form method="post" class="admin-form">
        <?= csrf_field() ?>

        <div class="admin-card">
            <div class="admin-card__head">Search engine snippet</div>
            <div class="admin-card__body">
                <div class="form-row">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" maxlength="255" value="<?= attr($page['title']) ?>">
                    <div class="help">Shown in browser tabs and search results. Aim for 50–60 characters.</div>
                </div>

                <div class="form-row">
                    <label for="meta_description">Meta description</label>
                    <textarea id="meta_description" name="meta_description" maxlength="320" rows="3"><?= e($page['meta_description']) ?></textarea>
                    <div class="help">Aim for 140–160 characters. Search engines may truncate longer.</div>
                </div>

                <div class="form-row">
                    <label for="meta_keywords">Meta keywords <span class="text-muted">(legacy)</span></label>
                    <input type="text" id="meta_keywords" name="meta_keywords" maxlength="255" value="<?= attr($page['meta_keywords']) ?>">
                    <div class="help">Comma-separated. Most search engines ignore this; included for completeness.</div>
                </div>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card__head">Social sharing &amp; canonical</div>
            <div class="admin-card__body">
                <div class="form-row">
                    <label for="og_image">Open Graph / social image</label>
                    <input type="text" id="og_image" name="og_image" maxlength="500" value="<?= attr($page['og_image']) ?>" placeholder="/uploads/pages/og-image.jpg">
                    <div class="help">Path or full URL. Recommended size 1200×630.</div>
                </div>

                <div class="form-row">
                    <label for="canonical">Canonical URL <span class="text-muted">(optional)</span></label>
                    <input type="url" id="canonical" name="canonical" maxlength="500" value="<?= attr($page['canonical']) ?>" placeholder="<?= attr((defined('SITE_URL') ? SITE_URL : '') . $path) ?>">
                    <div class="help">Leave blank to auto-derive from <code>SITE_URL</code> + page path.</div>
                </div>
            </div>
        </div>

        <?php
        $schema_types = [
            'WebPage', 'BreadcrumbList', 'FAQPage', 'Organization', 'LocalBusiness',
            'Product', 'Service', 'Article', 'BlogPosting', 'Person', 'Event',
            'VideoObject', 'SoftwareApplication', 'HowTo', 'Review', 'Rating', 'Custom',
        ];
        $schema_type_options = implode('', array_map(
            static fn($t) => '<option value="' . attr($t) . '">' . e($t) . '</option>',
            $schema_types
        ));
        ?>
        <div class="admin-card">
            <div class="admin-card__head">Structured data (JSON-LD)</div>
            <div class="admin-card__body">
                <p class="help" style="margin-top:0;">
                    Add as many schema blocks as this page needs. Each renders as its own
                    <code>&lt;script type="application/ld+json"&gt;</code> tag, in this order.
                </p>
                <?php svc_repeater_field(
                    'schemas', 'JSON-LD Schemas', '+ Add Another Schema',
                    '<div class="form-row"><label>Schema Type</label>'
                        . '<select name="schema_type[]" class="schema-type-select">' . $schema_type_options . '</select></div>'
                        . '<div class="form-row"><label>Schema Name / Label <span class="text-muted">(optional)</span></label>'
                        . '<input type="text" name="schema_label[]" placeholder="e.g. Homepage FAQ"></div>'
                        . '<div class="form-row" style="margin-bottom:0;"><label>JSON-LD Code'
                        . '<button type="button" class="admin-btn admin-btn--ghost admin-btn--small schema-generate-btn" style="float:right;">Generate</button>'
                        . '</label><textarea name="schema_code[]" rows="8" class="json-textarea" placeholder=\'{"@context": "https://schema.org", "@type": "WebPage", "name": "..."}\'></textarea></div>',
                    svc_normalize_schemas($page['schema_json'] ?? null),
                    [
                        'select[name="schema_type[]"]' => 'type',
                        'input[name="schema_label[]"]' => 'label',
                        'textarea[name="schema_code[]"]' => 'code',
                    ]
                ); ?>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card__head">Settings</div>
            <div class="admin-card__body">
                <div class="form-row">
                    <label>
                        <input type="checkbox" name="is_published" value="1" <?= $page['is_published'] ? 'checked' : '' ?>>
                        Apply this SEO metadata on the live site
                    </label>
                    <div class="help">Uncheck to temporarily revert to template defaults without losing your data.</div>
                </div>

                <div class="form-row">
                    <label for="notes">Internal notes</label>
                    <textarea id="notes" name="notes" rows="2"><?= e($page['notes']) ?></textarea>
                    <div class="help">Visible only here in the admin.</div>
                </div>
            </div>
        </div>

        <div style="display:flex;gap:10px;">
            <button type="submit" class="admin-btn">Save SEO metadata</button>
            <a href="<?= ADMIN_URL ?>/pages.php" class="admin-btn admin-btn--ghost">Cancel</a>
        </div>
    </form>

    <script>
    (function() {
        const path  = <?= json_encode($path) ?>;
        const title = document.getElementById('title');
        const desc  = document.getElementById('meta_description');
        const SITE  = <?= json_encode(defined('SITE_URL') ? SITE_URL : '') ?>;
        const SITE_NAME = <?= json_encode(defined('SITE_NAME') ? SITE_NAME : '') ?>;
        const fullUrl = (SITE || '') + path;

        // One generator per dropdown option — each returns a plain object,
        // stringified into that row's own JSON-LD textarea. Add more here as
        // new schema types are needed; the dropdown/textarea markup doesn't
        // need to change.
        const SCHEMA_GENERATORS = {
            WebPage: () => ({
                '@context': 'https://schema.org', '@type': 'WebPage',
                'name': title.value || '', 'description': desc.value || '', 'url': fullUrl
            }),
            BreadcrumbList: () => {
                const segments = path.split('/').filter(Boolean);
                const list = [{ '@type': 'ListItem', 'position': 1, 'name': 'Home', 'item': SITE || '/' }];
                let acc = '';
                segments.forEach((seg, i) => {
                    acc += '/' + seg;
                    list.push({
                        '@type': 'ListItem', 'position': i + 2,
                        'name': seg.charAt(0).toUpperCase() + seg.slice(1).replace(/-/g, ' '),
                        'item': (SITE || '') + acc
                    });
                });
                return { '@context': 'https://schema.org', '@type': 'BreadcrumbList', 'itemListElement': list };
            },
            FAQPage: () => ({
                '@context': 'https://schema.org', '@type': 'FAQPage',
                'mainEntity': [{ '@type': 'Question', 'name': '', 'acceptedAnswer': { '@type': 'Answer', 'text': '' } }]
            }),
            Organization: () => ({
                '@context': 'https://schema.org', '@type': 'Organization', 'name': SITE_NAME || '', 'url': SITE || ''
            }),
            LocalBusiness: () => ({
                '@context': 'https://schema.org', '@type': 'LocalBusiness', 'name': SITE_NAME || '', 'url': SITE || '',
                'address': { '@type': 'PostalAddress', 'streetAddress': '', 'addressLocality': '', 'addressCountry': '' }
            }),
            Product: () => ({
                '@context': 'https://schema.org', '@type': 'Product', 'name': title.value || '', 'description': desc.value || '',
                'image': '', 'offers': { '@type': 'Offer', 'price': '', 'priceCurrency': 'USD', 'availability': 'https://schema.org/InStock' }
            }),
            Service: () => ({
                '@context': 'https://schema.org', '@type': 'Service', 'name': title.value || '', 'description': desc.value || '',
                'provider': { '@type': 'Organization', 'name': SITE_NAME || '' }, 'url': fullUrl
            }),
            Article: () => ({
                '@context': 'https://schema.org', '@type': 'Article', 'headline': title.value || '',
                'description': desc.value || '', 'author': { '@type': 'Organization', 'name': SITE_NAME || '' }
            }),
            BlogPosting: () => ({
                '@context': 'https://schema.org', '@type': 'BlogPosting', 'headline': title.value || '',
                'description': desc.value || '', 'author': { '@type': 'Organization', 'name': SITE_NAME || '' }
            }),
            Person: () => ({ '@context': 'https://schema.org', '@type': 'Person', 'name': '', 'jobTitle': '', 'url': '' }),
            Event: () => ({
                '@context': 'https://schema.org', '@type': 'Event', 'name': '', 'startDate': '', 'endDate': '',
                'location': { '@type': 'Place', 'name': '', 'address': '' }
            }),
            VideoObject: () => ({
                '@context': 'https://schema.org', '@type': 'VideoObject', 'name': title.value || '',
                'description': desc.value || '', 'thumbnailUrl': '', 'uploadDate': ''
            }),
            SoftwareApplication: () => ({
                '@context': 'https://schema.org', '@type': 'SoftwareApplication', 'name': title.value || '',
                'applicationCategory': '', 'operatingSystem': '',
                'offers': { '@type': 'Offer', 'price': '', 'priceCurrency': 'USD' }
            }),
            HowTo: () => ({
                '@context': 'https://schema.org', '@type': 'HowTo', 'name': title.value || '',
                'step': [{ '@type': 'HowToStep', 'name': '', 'text': '' }]
            }),
            Review: () => ({
                '@context': 'https://schema.org', '@type': 'Review',
                'reviewRating': { '@type': 'Rating', 'ratingValue': '', 'bestRating': '5' },
                'author': { '@type': 'Person', 'name': '' }, 'itemReviewed': { '@type': 'Thing', 'name': title.value || '' }
            }),
            Rating: () => ({
                '@context': 'https://schema.org', '@type': 'AggregateRating', 'ratingValue': '', 'reviewCount': '', 'bestRating': '5'
            }),
            Custom: () => ({ '@context': 'https://schema.org', '@type': '' })
        };

        // Event-delegated: the repeater clones new rows from a <template>,
        // so listeners must live on the container, not the (not-yet-existing) button.
        const rowsContainer = document.getElementById('schemas-rows');
        if (rowsContainer) {
            rowsContainer.addEventListener('click', function (e) {
                const btn = e.target.closest('.schema-generate-btn');
                if (!btn) return;
                e.preventDefault();
                const row = btn.closest('.repeater-row');
                if (!row) return;
                const typeSelect = row.querySelector('select[name="schema_type[]"]');
                const codeField  = row.querySelector('textarea[name="schema_code[]"]');
                const generator  = typeSelect && SCHEMA_GENERATORS[typeSelect.value];
                if (generator && codeField) {
                    codeField.value = JSON.stringify(generator(), null, 2);
                }
            });
        }
    })();
    </script>

    <?php
    require __DIR__ . '/_footer.php';
    exit;
}

// ===========================================================================
// LIST view (default)
// ===========================================================================

// Discover all pages on disk
$paths_on_disk = router_discover_pages();

// Load existing SEO records
$existing = [];
try {
    foreach ($pdo->query('SELECT path, title, meta_description, is_published, updated_at FROM pages') as $row) {
        $existing[$row['path']] = $row;
    }
} catch (PDOException $e) {
    // table may not exist yet — fine
}

$admin_page_title = 'Pages & SEO';
$admin_active     = 'pages';
require __DIR__ . '/_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>Pages &amp; SEO</h1>
        <div class="subtitle">All page templates discovered on disk. Click a page to edit its SEO metadata.</div>
    </div>
</div>

<div class="admin-card">
    <table class="admin-table">
        <thead>
            <tr>
                <th>URL path</th>
                <th>SEO title</th>
                <th>Status</th>
                <th>Last edited</th>
                <th class="col-actions">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($paths_on_disk)): ?>
                <tr><td colspan="5" class="empty-state">No page templates found in <code>/pages/</code>.</td></tr>
            <?php else: ?>
                <?php foreach ($paths_on_disk as $p):
                    $rec = $existing[$p] ?? null;
                ?>
                <tr>
                    <td><code class="text-mono"><?= e($p) ?></code></td>
                    <td>
                        <?php if ($rec && $rec['title']): ?>
                            <?= e($rec['title']) ?>
                        <?php else: ?>
                            <span class="text-muted">— uses template default —</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!$rec): ?>
                            <span class="pill pill--draft">No metadata</span>
                        <?php elseif ($rec['is_published']): ?>
                            <span class="pill pill--published">Active</span>
                        <?php else: ?>
                            <span class="pill pill--archived">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td class="no-wrap text-muted">
                        <?= $rec ? e(substr((string) $rec['updated_at'], 0, 16)) : '—' ?>
                    </td>
                    <td class="col-actions">
                        <a href="<?= attr($p) ?>" target="_blank" rel="noopener" class="admin-btn admin-btn--ghost admin-btn--small">View</a>
                        <a href="?action=edit&amp;path=<?= attr(urlencode($p)) ?>" class="admin-btn admin-btn--small">Edit SEO</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
