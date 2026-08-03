<?php
/**
 * Admin — Service Master (full CRUD).
 *
 * Every dynamic section of pages/services/_subservice.php is editable here,
 * organized into tabs (see .section-tabs in admin.css). Scalar fields map
 * 1:1 to `services` columns; repeater sections (impact stats, benefit
 * cards, industries, ...) are built from parallel `name="field[]"` POST
 * arrays via svc_build_repeater() and stored as JSON. See
 * db/migrations/2026-07-28-001-extend-services-table.sql for the full
 * column list and _subservice.php's doc block for the section contract.
 */

require __DIR__ . '/bootstrap.php';

$pdo = db();
if (!$pdo) { die('Database connection failed.'); }

$action = $_GET['action'] ?? 'list';
$user   = auth_user();

// ===========================================================================
// Helpers
// ===========================================================================

function svc_slugify(string $t): string
{
    $t = strtolower(trim($t));
    if (function_exists('iconv')) {
        $c = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $t);
        if ($c !== false) $t = $c;
    }
    $t = preg_replace('/[^a-z0-9]+/', '-', $t) ?? '';
    return trim($t, '-') ?: 'service';
}

function svc_unique_slug(PDO $pdo, string $base, ?int $excl = null): string
{
    $slug = $base; $i = 2;
    while (true) {
        $stmt = $pdo->prepare('SELECT id FROM services WHERE slug = :s' . ($excl ? ' AND id != :id' : ''));
        $p = [':s' => $slug];
        if ($excl) $p[':id'] = $excl;
        $stmt->execute($p);
        if (!$stmt->fetch()) return $slug;
        $slug = $base . '-' . $i++;
    }
}

/**
 * "One item per line" textarea -> array of strings. Same convention this
 * admin already used for the old Key Features textarea, reused everywhere
 * a section just needs a flat list (platforms, tag lists, bullet points).
 */
function svc_lines_to_array(string $raw): array
{
    return array_values(array_filter(array_map('trim', explode("\n", $raw))));
}

/**
 * Build a JSON-ready array of rows from parallel POST arrays — one shared
 * builder for every object-repeater section instead of hand-rolling the
 * same loop ~15 times. $fields maps output key => POST field name (plain
 * text/textarea inputs); $listFields maps output key => POST field name for
 * a "one per line" textarea (converted to an array of strings);
 * $boolFields maps output key => POST field name for a checkbox. Row count
 * is taken from the first $fields entry. Rows where every $fields/$listFields
 * value is blank are dropped (a checkbox alone never keeps an otherwise-
 * empty row).
 */
function svc_build_repeater(array $post, array $fields, array $listFields = [], array $boolFields = []): array
{
    $firstKey = array_key_first($fields);
    $count = ($firstKey !== null && isset($post[$fields[$firstKey]]) && is_array($post[$fields[$firstKey]]))
        ? count($post[$fields[$firstKey]])
        : 0;

    $rows = [];
    for ($i = 0; $i < $count; $i++) {
        $row = [];
        $hasContent = false;
        foreach ($fields as $outKey => $postName) {
            $val = trim((string) ($post[$postName][$i] ?? ''));
            $row[$outKey] = $val;
            if ($val !== '') { $hasContent = true; }
        }
        foreach ($listFields as $outKey => $postName) {
            $items = svc_lines_to_array((string) ($post[$postName][$i] ?? ''));
            $row[$outKey] = $items;
            if (!empty($items)) { $hasContent = true; }
        }
        foreach ($boolFields as $outKey => $postName) {
            $row[$outKey] = isset($post[$postName][$i]) && $post[$postName][$i] !== '';
        }
        if ($hasContent) {
            $rows[] = $row;
        }
    }
    return $rows;
}

/**
 * Normalize the overview feature cards (features_json) for the edit form:
 * legacy rows may still hold a flat string array from before this column
 * was repurposed for {icon,title,description} cards.
 */
function svc_normalize_features(?string $raw): array
{
    if (!$raw) { return []; }
    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) { return []; }
    return array_map(static function ($item) {
        if (is_array($item)) {
            return ['icon' => $item['icon'] ?? '', 'title' => $item['title'] ?? '', 'description' => $item['description'] ?? ($item['desc'] ?? '')];
        }
        return ['icon' => '', 'title' => (string) $item, 'description' => ''];
    }, $decoded);
}

function svc_json_decode(?string $raw): array
{
    if (!$raw) { return []; }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

// ============ Standard "sub / title / text + repeater" sections ============
// 13 of the ~20 sections share the same 3-field header shape, so it's
// rendered generically here instead of repeating the same markup block 13
// times. Repeater row markup + field maps still differ per section (icons,
// tags, checkboxes...), so those stay explicit at each call site.
function svc_section_header(string $prefix, array $f, string $titlePlaceholder = ''): void
{
    ?>
    <div class="form-row">
        <label for="<?= $prefix ?>_sub">Subtitle</label>
        <input type="text" id="<?= $prefix ?>_sub" name="<?= $prefix ?>_sub" value="<?= attr($f[$prefix . '_sub']) ?>">
    </div>
    <div class="form-row">
        <label for="<?= $prefix ?>_title_html">Heading <span class="text-muted">(HTML allowed)</span></label>
        <textarea id="<?= $prefix ?>_title_html" name="<?= $prefix ?>_title_html" rows="2" placeholder="<?= attr($titlePlaceholder) ?>"><?= e($f[$prefix . '_title_html']) ?></textarea>
    </div>
    <div class="form-row">
        <label for="<?= $prefix ?>_text">Intro Text</label>
        <textarea id="<?= $prefix ?>_text" name="<?= $prefix ?>_text" rows="2"><?= e($f[$prefix . '_text']) ?></textarea>
    </div>
    <?php
}

/**
 * Echo the shared repeater scaffold: an empty rows container, an "add"
 * button, a <template> holding one row's markup, and a JSON script tag with
 * the existing data — all consumed by repeater.js's
 * initRepeater()/populateRepeaterRow() on the JS side.
 */
function svc_repeater_field(string $id, string $title, string $addLabel, string $rowHtml, array $existingData, array $fieldMap): void
{
    ?>
    <div class="repeater-row__title" style="margin-bottom:10px;"><?= e($title) ?></div>
    <div id="<?= $id ?>-rows"></div>
    <div id="<?= $id ?>-empty" class="text-muted" style="font-size:13px;">No rows yet.</div>
    <button type="button" id="<?= $id ?>-add" class="admin-btn admin-btn--ghost admin-btn--small mt-2">
        <?= e($addLabel) ?>
    </button>
    <template id="<?= $id ?>-template">
        <div class="repeater-row">
            <div class="repeater-row__head">
                <span class="repeater-row__title">Item</span>
                <div class="repeater-row__actions">
                    <button type="button" data-repeater-action="up" title="Move up">&uarr;</button>
                    <button type="button" data-repeater-action="down" title="Move down">&darr;</button>
                    <button type="button" data-repeater-action="remove" title="Remove">&times;</button>
                </div>
            </div>
            <?= $rowHtml ?>
        </div>
    </template>
    <script type="application/json" id="<?= $id ?>-data"><?= json_encode($existingData) ?></script>
    <script>
    (function () {
        var container = document.getElementById('<?= $id ?>-rows');
        var data = JSON.parse(document.getElementById('<?= $id ?>-data').textContent || '[]');
        var repeater = initRepeater({
            container: container,
            emptyEl: document.getElementById('<?= $id ?>-empty'),
            addBtn: document.getElementById('<?= $id ?>-add'),
            template: document.getElementById('<?= $id ?>-template'),
            onAdd: function (row, rowData) {
                populateRepeaterRow(row, rowData, <?= json_encode($fieldMap) ?>);
            }
        });
        data.forEach(function (item) { repeater.addRow(item); });
    })();
    </script>
    <?php
}

// ===========================================================================
// DELETE
// ===========================================================================
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_or_die();
    $del_id = (int) ($_POST['id'] ?? 0);
    if ($del_id > 0) {
        delete_service($pdo, $del_id);
        flash('success', 'Service deleted.');
    }
    header('Location: ' . ADMIN_URL . '/services.php'); exit;
}

// ===========================================================================
// NEW / EDIT
// ===========================================================================
if ($action === 'new' || $action === 'edit') {
    $id      = (int) ($_GET['id'] ?? 0);
    $service = null;

    if ($action === 'edit' && $id > 0) {
        $service = get_service($pdo, $id);
        if (!$service) {
            flash('error', 'Service not found.');
            header('Location: ' . ADMIN_URL . '/services.php'); exit;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_verify_or_die();

        $name    = trim((string) ($_POST['name'] ?? ''));
        $title   = trim((string) ($_POST['title'] ?? ''));
        $excerpt = trim((string) ($_POST['excerpt'] ?? ''));
        $schema_json = trim((string) ($_POST['schema_json'] ?? ''));
        $service_number_raw = (string) ($_POST['service_number'] ?? '1');

        $errors = [];
        if ($name === '') { $errors[] = 'Service Name is required.'; }
        if ($title === '') { $errors[] = 'Display Title is required.'; }
        if ($excerpt === '') { $errors[] = 'Short Description is required.'; }
        if (!ctype_digit($service_number_raw) && !is_numeric($service_number_raw)) {
            $errors[] = 'Display Order must be a number.';
        }
        if ($schema_json !== '') {
            json_decode($schema_json);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $errors[] = 'JSON-LD schema is invalid: ' . json_last_error_msg();
            }
        }

        if ($errors) {
            foreach ($errors as $e) { flash('error', $e); }
            $service = array_merge((array) $service, $_POST, ['id' => $service['id'] ?? null]);
        } else {
            $status = in_array($_POST['status'] ?? '', ['draft', 'published', 'archived'], true)
                ? $_POST['status'] : 'draft';
            $pub_at = $status === 'published'
                ? (($service['published_at'] ?? null) ?: date('Y-m-d H:i:s'))
                : null;

            $slug_raw  = trim((string) ($_POST['slug'] ?? ''));
            $slug_base = $slug_raw !== '' ? svc_slugify($slug_raw) : svc_slugify($title);
            $slug      = svc_unique_slug($pdo, $slug_base, $service['id'] ?? null);

            // Plain scalar fields — pass straight through, trimmed.
            $plain_fields = [
                'page_label', 'crumb', 'hero_tag', 'hero_desc',
                'platform_title', 'overview_sub', 'overview_btn_text',
                'benefits_sub', 'benefits_text', 'grid_sub', 'grid_text',
                'whatyouget_sub', 'whatyouget_text', 'industries_sub', 'industries_text',
                'framework_sub', 'framework_text', 'why_sub', 'why_text',
                'engagement_sub', 'engagement_text', 'process_sub', 'process_text',
                'cta_tag', 'cta_text', 'cs_sub', 'cs_text', 'tech_sub', 'tech_text',
                'security_sub', 'security_text', 'related_sub', 'related_text', 'related_group_title',
                'blog_sub', 'blog_text', 'faq_intro',
                'final_cta_desc', 'final_cta_btn_text', 'final_cta_btn_url',
                'meta_title', 'meta_description', 'meta_keywords', 'og_image', 'canonical', 'robots',
                'overview_html', 'features_html', 'use_cases_html', 'benefits_html',
                'description',
            ];
            // Fields that allow trusted inline HTML (CKEditor) — sanitized +
            // unwrapped rather than escaped.
            $html_fields = [
                'hero_title_html', 'overview_title_html', 'benefits_title_html', 'grid_title_html',
                'whatyouget_title_html', 'industries_title_html', 'framework_title_html', 'why_title_html',
                'engagement_title_html', 'process_title_html', 'cta_title_html', 'cs_title_html',
                'tech_title_html', 'security_title_html', 'related_title_html', 'blog_title_html',
                'final_cta_title_html',
            ];

            $data = [
                'slug' => $slug,
                'name' => $name,
                'title' => $title,
                'excerpt' => $excerpt,
                'icon_class' => trim((string) ($_POST['icon_class'] ?? '')),
                'service_number' => max(1, (int) $service_number_raw),
                'featured_image' => trim((string) ($_POST['featured_image'] ?? '')),
                'featured_alt' => trim((string) ($_POST['featured_alt'] ?? '')),
                'schema_json' => $schema_json,
                'status' => $status,
                'published_at' => $pub_at,
                'display_on_home' => isset($_POST['display_on_home']) ? 1 : 0,
                'author_id' => $user['id'] ?? null,
            ];
            foreach ($plain_fields as $f) {
                $data[$f] = trim((string) ($_POST[$f] ?? ''));
            }
            foreach ($html_fields as $f) {
                $data[$f] = strip_wrapping_p(sanitize_html_fragment((string) ($_POST[$f] ?? '')));
            }

            // Simple string lists
            $data['platforms_json'] = json_encode(svc_lines_to_array((string) ($_POST['platforms'] ?? '')));
            $data['overview_paragraphs_json'] = json_encode(svc_lines_to_array((string) ($_POST['overview_paragraphs'] ?? '')));

            // Object repeaters
            $data['impact_stats_json'] = json_encode(svc_build_repeater($_POST,
                ['number' => 'impact_number', 'title' => 'impact_title', 'desc' => 'impact_desc']));
            $data['features_json'] = json_encode(svc_build_repeater($_POST,
                ['icon' => 'feat_icon', 'title' => 'feat_title', 'description' => 'feat_desc']));
            $data['benefit_cards_json'] = json_encode(svc_build_repeater($_POST,
                ['icon' => 'benefit_icon', 'title' => 'benefit_title', 'desc' => 'benefit_desc', 'example' => 'benefit_example']));
            $data['grid_services_json'] = json_encode(svc_build_repeater($_POST,
                ['icon' => 'grid_icon', 'title' => 'grid_card_title', 'desc' => 'grid_card_desc'],
                ['tags' => 'grid_tags']));
            $data['whatyouget_cards_json'] = json_encode(svc_build_repeater($_POST,
                ['title' => 'wyg_title', 'desc' => 'wyg_desc']));
            $data['industries_json'] = json_encode(svc_build_repeater($_POST,
                ['title' => 'industry_title'], ['items' => 'industry_items']));
            $data['framework_steps_json'] = json_encode(svc_build_repeater($_POST,
                ['icon' => 'fw_icon', 'title' => 'fw_title', 'desc' => 'fw_desc']));
            $data['why_cards_json'] = json_encode(svc_build_repeater($_POST,
                ['title' => 'why_card_title', 'desc' => 'why_card_desc']));
            $data['engagement_models_json'] = json_encode(svc_build_repeater($_POST,
                ['badge' => 'eng_badge', 'title' => 'eng_title', 'desc' => 'eng_desc', 'btn_text' => 'eng_btn_text'],
                ['features' => 'eng_features'], ['featured' => 'eng_featured']));
            $data['process_steps_json'] = json_encode(svc_build_repeater($_POST,
                ['title' => 'process_step_title', 'desc' => 'process_step_desc'],
                ['tags' => 'process_step_tags']));
            $data['case_studies_json'] = json_encode(svc_build_repeater($_POST,
                ['tag' => 'cs_tag', 'title' => 'cs_card_title', 'desc' => 'cs_card_desc', 'result' => 'cs_result']));
            $data['tech_categories_json'] = json_encode(svc_build_repeater($_POST,
                ['title' => 'tech_cat_title'], ['items' => 'tech_cat_items']));
            $data['security_cards_json'] = json_encode(svc_build_repeater($_POST,
                ['title' => 'sec_title', 'desc' => 'sec_desc']));
            $data['faqs_json'] = json_encode(svc_build_repeater($_POST,
                ['question' => 'faq_question', 'answer' => 'faq_answer']));

            // Pickers (store IDs only; frontend resolves to URL/content)
            $data['related_service_ids_json'] = json_encode(array_values(array_filter(array_map('intval', $_POST['related_service_ids'] ?? []))));
            $data['blog_post_ids_json'] = json_encode(array_values(array_filter(array_map('intval', $_POST['blog_post_ids'] ?? []))));

            if (!empty($service['id'])) {
                $data['id'] = $service['id'];
            }
            $result = save_service($pdo, $data);

            if ($result['success']) {
                flash('success', $service && !empty($service['id']) ? 'Service updated.' : 'Service created.');
                header('Location: ' . ADMIN_URL . '/services.php?action=edit&id=' . $result['id']); exit;
            }
            flash('error', 'Could not save service: ' . ($result['error'] ?? 'unknown error'));
            $service = array_merge((array) $service, $_POST, ['id' => $service['id'] ?? null]);
        }
    }

    // ---- Defaults for the form ----
    $f = array_merge([
        'id' => null, 'slug' => '', 'name' => '', 'title' => '', 'excerpt' => '', 'description' => '',
        'service_number' => 1, 'icon_class' => '', 'featured_image' => '', 'featured_alt' => '',
        'overview_html' => '', 'features_html' => '', 'use_cases_html' => '', 'benefits_html' => '',
        'meta_title' => '', 'meta_description' => '', 'meta_keywords' => '', 'og_image' => '',
        'canonical' => '', 'robots' => '', 'schema_json' => '', 'status' => 'draft',
        'display_on_home' => 1, 'published_at' => null,
        'page_label' => '', 'crumb' => '', 'hero_tag' => '', 'hero_title_html' => '', 'hero_desc' => '',
        'platform_title' => '', 'overview_sub' => '', 'overview_title_html' => '', 'overview_btn_text' => '',
        'benefits_sub' => '', 'benefits_title_html' => '', 'benefits_text' => '',
        'grid_sub' => '', 'grid_title_html' => '', 'grid_text' => '',
        'whatyouget_sub' => '', 'whatyouget_title_html' => '', 'whatyouget_text' => '',
        'industries_sub' => '', 'industries_title_html' => '', 'industries_text' => '',
        'framework_sub' => '', 'framework_title_html' => '', 'framework_text' => '',
        'why_sub' => '', 'why_title_html' => '', 'why_text' => '',
        'engagement_sub' => '', 'engagement_title_html' => '', 'engagement_text' => '',
        'process_sub' => '', 'process_title_html' => '', 'process_text' => '',
        'cta_tag' => '', 'cta_title_html' => '', 'cta_text' => '',
        'cs_sub' => '', 'cs_title_html' => '', 'cs_text' => '',
        'tech_sub' => '', 'tech_title_html' => '', 'tech_text' => '',
        'security_sub' => '', 'security_title_html' => '', 'security_text' => '',
        'related_sub' => '', 'related_title_html' => '', 'related_text' => '', 'related_group_title' => '',
        'blog_sub' => '', 'blog_title_html' => '', 'blog_text' => '', 'faq_intro' => '',
        'final_cta_title_html' => '', 'final_cta_desc' => '', 'final_cta_btn_text' => '', 'final_cta_btn_url' => '',
        'platforms_json' => null, 'overview_paragraphs_json' => null,
    ], (array) $service);

    // JSON-backed data for the JS repeaters (edit mode) — empty arrays for "new"
    $platforms_list = svc_lines_to_array(implode("\n", svc_json_decode($f['platforms_json'] ?? null)));
    $overview_paragraphs_list = svc_json_decode($f['overview_paragraphs_json'] ?? null);
    $repeater_data = [
        'impact_stats' => svc_json_decode($service['impact_stats_json'] ?? null),
        'overview_features' => svc_normalize_features($service['features_json'] ?? null),
        'benefit_cards' => svc_json_decode($service['benefit_cards_json'] ?? null),
        'grid_services' => svc_json_decode($service['grid_services_json'] ?? null),
        'whatyouget_cards' => svc_json_decode($service['whatyouget_cards_json'] ?? null),
        'industries' => svc_json_decode($service['industries_json'] ?? null),
        'framework_steps' => svc_json_decode($service['framework_steps_json'] ?? null),
        'why_cards' => svc_json_decode($service['why_cards_json'] ?? null),
        'engagement_models' => svc_json_decode($service['engagement_models_json'] ?? null),
        'process_steps' => svc_json_decode($service['process_steps_json'] ?? null),
        'case_studies' => svc_json_decode($service['case_studies_json'] ?? null),
        'tech_categories' => svc_json_decode($service['tech_categories_json'] ?? null),
        'security_cards' => svc_json_decode($service['security_cards_json'] ?? null),
        'faqs' => svc_json_decode($service['faqs_json'] ?? null),
    ];
    $selected_related_ids = array_map('intval', svc_json_decode($service['related_service_ids_json'] ?? null));
    $selected_blog_ids = array_map('intval', svc_json_decode($service['blog_post_ids_json'] ?? null));

    // Pickers: other published services, published blog posts
    $other_services = array_filter(
        get_services($pdo, ['status' => 'published']),
        static fn($s) => (int) $s['id'] !== (int) ($f['id'] ?? 0)
    );
    $blog_posts_stmt = $pdo->query("SELECT id, title FROM posts WHERE status = 'published' ORDER BY COALESCE(published_at, updated_at) DESC LIMIT 300");
    $all_blog_posts = $blog_posts_stmt->fetchAll();

    $admin_page_title = $service ? 'Edit Service' : 'New Service';
    $admin_active     = 'services';
    require __DIR__ . '/_header.php';
    ?>

    <!-- Loaded up-front: the per-section bootstrap scripts embedded further
         down (one per repeater field, via svc_repeater_field()) call
         initRepeater()/populateRepeaterRow()/initIconPicker() as soon as
         they're parsed, so these must already be defined by then. -->
    <script src="<?= ADMIN_URL ?>/assets/js/repeater.js"></script>
    <script src="<?= ADMIN_URL ?>/assets/js/fa-icons.js"></script>
    <script src="<?= ADMIN_URL ?>/assets/js/icon-picker.js"></script>

    <div class="admin-page-header">
        <div>
            <h1><?= $service ? 'Edit Service' : 'New Service' ?></h1>
            <?php if ($service): ?>
                <div class="subtitle">
                    Slug: <code class="text-mono"><?= e($f['slug']) ?></code>
                    <?php if ($f['status'] === 'published'): ?>
                        &middot; <a href="<?= url('/services/' . $f['slug']) ?>" target="_blank" rel="noopener">View live &rarr;</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <a href="<?= ADMIN_URL ?>/services.php" class="admin-btn admin-btn--ghost">Back to list</a>
    </div>

    <form method="post" class="admin-form" id="service-form">
        <?= csrf_field() ?>

        <div class="section-tabs">
            <div class="section-tabs__nav" id="section-tabs-nav">
                <?php
                $tabs = [
                    'core' => 'Core', 'hero' => 'Hero', 'platforms' => 'Platforms',
                    'impact' => 'Impact Stats', 'overview' => 'Overview', 'benefits' => 'Benefits',
                    'grid' => 'Services Grid', 'whatyouget' => 'What You Get', 'industries' => 'Industries',
                    'framework' => 'Framework', 'why' => 'Why Choose Us', 'engagement' => 'Engagement Models',
                    'process' => 'Process Timeline', 'cta' => 'Mid CTA', 'final_cta' => 'Final CTA', 'cases' => 'Case Studies',
                    'tech' => 'Technology Stack', 'security' => 'Security', 'related' => 'Related Services',
                    'blog' => 'Knowledge Hub', 'faq' => 'FAQ', 'seo' => 'SEO', 'legacy' => 'Legacy Fields',
                ];
                foreach ($tabs as $key => $label): ?>
                    <button type="button" data-tab="<?= $key ?>"><?= e($label) ?></button>
                <?php endforeach; ?>
            </div>

            <div class="section-tabs__panels">

                <!-- ============ CORE ============ -->
                <div class="section-tabs__panel" data-panel="core">
                    <div class="admin-card"><div class="admin-card__body">
                        <div class="form-row">
                            <label for="name">Service Name *</label>
                            <input type="text" id="name" name="name" required value="<?= attr($f['name']) ?>" placeholder="e.g. Voice AI">
                        </div>
                        <div class="form-row">
                            <label for="title">Display Title *</label>
                            <input type="text" id="title" name="title" required value="<?= attr($f['title']) ?>">
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                            <div class="form-row">
                                <label for="slug">Slug</label>
                                <input type="text" id="slug" name="slug" value="<?= attr($f['slug']) ?>" placeholder="auto-generated">
                                <div class="help">Live at <code class="text-mono">/services/<?= e($f['slug'] ?: '{slug}') ?></code></div>
                            </div>
                            <div class="form-row">
                                <label for="service_number">Display Order</label>
                                <input type="number" id="service_number" name="service_number" min="1" value="<?= (int) $f['service_number'] ?>">
                            </div>
                        </div>
                        <div class="form-row">
                            <label for="excerpt">Short Description *</label>
                            <textarea id="excerpt" name="excerpt" rows="2" required maxlength="500"><?= e($f['excerpt']) ?></textarea>
                            <div class="help">Shown on the Services listing page card.</div>
                        </div>
                        <div class="form-row">
                            <label for="description">Full Description <span class="text-muted">(internal / admin reference)</span></label>
                            <textarea id="description" name="description" rows="3"><?= e($f['description']) ?></textarea>
                        </div>
                        <div class="form-row">
                            <label for="icon_class">Icon Class</label>
                            <input type="text" id="icon_class" name="icon_class" value="<?= attr($f['icon_class']) ?>" placeholder="flaticon-voice or fas fa-microphone">
                            <div class="help">Used on the Services listing card.</div>
                        </div>
                        <div class="form-row">
                            <label for="featured_image">Featured Image</label>
                            <input type="text" id="featured_image" name="featured_image" value="<?= attr($f['featured_image']) ?>" placeholder="/uploads/service/...">
                            <div class="help">Copy a path from the <a href="<?= ADMIN_URL ?>/media.php" target="_blank" rel="noopener">Media Library</a>, or paste any URL.</div>
                        </div>
                        <?php if (!empty($f['featured_image'])): ?>
                            <img src="<?= attr(media_url($f['featured_image'])) ?>" style="max-width:220px;border-radius:4px;border:1px solid var(--admin-border);margin-top:8px;" alt="">
                        <?php endif; ?>
                        <div class="form-row" style="margin-top:10px;">
                            <label for="featured_alt">Alt Text</label>
                            <input type="text" id="featured_alt" name="featured_alt" value="<?= attr($f['featured_alt']) ?>">
                        </div>
                        <div class="form-row">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="draft"     <?= $f['status'] === 'draft'     ? 'selected' : '' ?>>Draft</option>
                                <option value="published" <?= $f['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                                <option value="archived"  <?= $f['status'] === 'archived'  ? 'selected' : '' ?>>Archived</option>
                            </select>
                        </div>
                        <div class="form-row">
                            <label>
                                <input type="checkbox" name="display_on_home" <?= $f['display_on_home'] ? 'checked' : '' ?>>
                                Show on Homepage
                            </label>
                        </div>
                        <?php if ($f['published_at']): ?>
                            <div class="text-muted" style="font-size:12px;">Published <?= e(substr((string) $f['published_at'], 0, 16)) ?></div>
                        <?php endif; ?>
                    </div></div>
                </div>

                <!-- ============ HERO ============ -->
                <div class="section-tabs__panel" data-panel="hero">
                    <div class="admin-card"><div class="admin-card__body">
                        <div class="form-row">
                            <label for="page_label">Page Banner Heading <span class="text-muted">(optional — defaults to Display Title)</span></label>
                            <input type="text" id="page_label" name="page_label" value="<?= attr($f['page_label']) ?>">
                        </div>
                        <div class="form-row">
                            <label for="crumb">Breadcrumb Label <span class="text-muted">(optional — defaults to Service Name)</span></label>
                            <input type="text" id="crumb" name="crumb" value="<?= attr($f['crumb']) ?>">
                        </div>
                        <div class="form-row">
                            <label for="hero_tag">Hero Tag</label>
                            <input type="text" id="hero_tag" name="hero_tag" value="<?= attr($f['hero_tag']) ?>" placeholder="e.g. Voice AI Services">
                        </div>
                        <div class="form-row">
                            <label for="hero_title_html">Hero Title <span class="text-muted">(HTML allowed: span, strong, em, br)</span></label>
                            <textarea id="hero_title_html" name="hero_title_html" rows="3"><?= e($f['hero_title_html']) ?></textarea>
                        </div>
                        <div class="form-row">
                            <label for="hero_desc">Hero Description</label>
                            <textarea id="hero_desc" name="hero_desc" rows="3"><?= e($f['hero_desc']) ?></textarea>
                        </div>
                    </div></div>
                </div>

                <!-- ============ PLATFORMS ============ -->
                <div class="section-tabs__panel" data-panel="platforms">
                    <div class="admin-card"><div class="admin-card__body">
                        <div class="form-row">
                            <label for="platform_title">Section Title</label>
                            <input type="text" id="platform_title" name="platform_title" value="<?= attr($f['platform_title']) ?>" placeholder="Powered by the World's Leading AI &amp; ML Platforms">
                        </div>
                        <div class="form-row">
                            <label for="platforms">Platforms <span class="text-muted">(one per line)</span></label>
                            <textarea id="platforms" name="platforms" rows="8" placeholder="PyTorch&#10;TensorFlow&#10;OpenAI"><?= e(implode("\n", $platforms_list)) ?></textarea>
                        </div>
                        <div class="help">Leave both blank to hide this section entirely.</div>
                    </div></div>
                </div>

                <!-- ============ IMPACT STATS ============ -->
                <div class="section-tabs__panel" data-panel="impact">
                    <div class="admin-card"><div class="admin-card__body">
                        <?php svc_repeater_field(
                            'impact-stats', 'Impact Stats', '+ Add stat',
                            '<div class="form-row"><label>Number</label><input type="text" name="impact_number[]" placeholder="40+"></div>' .
                            '<div class="form-row"><label>Title</label><input type="text" name="impact_title[]" placeholder="Production AI"></div>' .
                            '<div class="form-row" style="margin-bottom:0;"><label>Description</label><textarea name="impact_desc[]" rows="2" placeholder="Projects Successfully Delivered"></textarea></div>',
                            $repeater_data['impact_stats'],
                            [
                                'input[name="impact_number[]"]' => 'number',
                                'input[name="impact_title[]"]' => 'title',
                                'textarea[name="impact_desc[]"]' => 'desc',
                            ]
                        ); ?>
                    </div></div>
                </div>

                <!-- ============ OVERVIEW ============ -->
                <div class="section-tabs__panel" data-panel="overview">
                    <div class="admin-card"><div class="admin-card__body">
                        <div class="form-row">
                            <label for="overview_sub">Subtitle</label>
                            <input type="text" id="overview_sub" name="overview_sub" value="<?= attr($f['overview_sub']) ?>">
                        </div>
                        <div class="form-row">
                            <label for="overview_title_html">Heading <span class="text-muted">(HTML allowed)</span></label>
                            <textarea id="overview_title_html" name="overview_title_html" rows="2"><?= e($f['overview_title_html']) ?></textarea>
                        </div>
                        <div class="form-row">
                            <label for="overview_paragraphs">Paragraphs <span class="text-muted">(one per line)</span></label>
                            <textarea id="overview_paragraphs" name="overview_paragraphs" rows="6"><?= e(implode("\n", $overview_paragraphs_list)) ?></textarea>
                        </div>
                        <div class="form-row">
                            <label for="overview_btn_text">Button Text</label>
                            <input type="text" id="overview_btn_text" name="overview_btn_text" value="<?= attr($f['overview_btn_text']) ?>" placeholder="Talk With Our Experts">
                        </div>
                    </div></div>
                    <div class="admin-card"><div class="admin-card__body">
                        <?php svc_repeater_field(
                            'overview-features', 'Feature Cards', '+ Add feature',
                            '<div class="form-row"><label>Icon</label><input type="text" class="icon-input" name="feat_icon[]"></div>' .
                            '<div class="form-row"><label>Title</label><input type="text" name="feat_title[]"></div>' .
                            '<div class="form-row" style="margin-bottom:0;"><label>Description</label><textarea name="feat_desc[]" rows="2"></textarea></div>',
                            $repeater_data['overview_features'],
                            [
                                'input.icon-input' => ['key' => 'icon', 'type' => 'icon'],
                                'input[name="feat_title[]"]' => 'title',
                                'textarea[name="feat_desc[]"]' => ['key' => 'description'],
                            ]
                        ); ?>
                    </div></div>
                </div>

                <!-- ============ BENEFITS ============ -->
                <div class="section-tabs__panel" data-panel="benefits">
                    <div class="admin-card"><div class="admin-card__body"><?php svc_section_header('benefits', $f, 'How AI Creates <span>Business Value</span>'); ?></div></div>
                    <div class="admin-card"><div class="admin-card__body">
                        <?php svc_repeater_field('benefit-cards', 'Benefit Cards', '+ Add card',
                            '<div class="form-row"><label>Icon</label><input type="text" class="icon-input" name="benefit_icon[]"></div>' .
                            '<div class="form-row"><label>Title</label><input type="text" name="benefit_title[]"></div>' .
                            '<div class="form-row"><label>Description</label><textarea name="benefit_desc[]" rows="2"></textarea></div>' .
                            '<div class="form-row" style="margin-bottom:0;"><label>Real Example</label><textarea name="benefit_example[]" rows="2"></textarea></div>',
                            $repeater_data['benefit_cards'],
                            ['input.icon-input' => ['key' => 'icon', 'type' => 'icon'], 'input[name="benefit_title[]"]' => 'title',
                             'textarea[name="benefit_desc[]"]' => 'desc', 'textarea[name="benefit_example[]"]' => 'example']
                        ); ?>
                    </div></div>
                </div>

                <!-- ============ SERVICES GRID ============ -->
                <div class="section-tabs__panel" data-panel="grid">
                    <div class="admin-card"><div class="admin-card__body"><?php svc_section_header('grid', $f, 'Our <span>AI ML Services</span>'); ?></div></div>
                    <div class="admin-card"><div class="admin-card__body">
                        <?php svc_repeater_field('grid-services', 'Service Cards', '+ Add card',
                            '<div class="form-row"><label>Icon</label><input type="text" class="icon-input" name="grid_icon[]"></div>' .
                            '<div class="form-row"><label>Title</label><input type="text" name="grid_card_title[]"></div>' .
                            '<div class="form-row"><label>Description</label><textarea name="grid_card_desc[]" rows="2"></textarea></div>' .
                            '<div class="form-row" style="margin-bottom:0;"><label>Technology Tags <span class="text-muted">(one per line)</span></label><textarea name="grid_tags[]" rows="3" placeholder="Python&#10;TensorFlow"></textarea></div>',
                            $repeater_data['grid_services'],
                            ['input.icon-input' => ['key' => 'icon', 'type' => 'icon'], 'input[name="grid_card_title[]"]' => 'title',
                             'textarea[name="grid_card_desc[]"]' => 'desc', 'textarea[name="grid_tags[]"]' => ['key' => 'tags', 'type' => 'list']]
                        ); ?>
                    </div></div>
                </div>

                <!-- ============ WHAT YOU GET ============ -->
                <div class="section-tabs__panel" data-panel="whatyouget">
                    <div class="admin-card"><div class="admin-card__body"><?php svc_section_header('whatyouget', $f, 'Benefits of Our <span>AI/ML Services</span>'); ?></div></div>
                    <div class="admin-card"><div class="admin-card__body">
                        <?php svc_repeater_field('wyg-cards', 'Cards', '+ Add card',
                            '<div class="form-row"><label>Title</label><input type="text" name="wyg_title[]"></div>' .
                            '<div class="form-row" style="margin-bottom:0;"><label>Description</label><textarea name="wyg_desc[]" rows="2"></textarea></div>',
                            $repeater_data['whatyouget_cards'],
                            ['input[name="wyg_title[]"]' => 'title', 'textarea[name="wyg_desc[]"]' => 'desc']
                        ); ?>
                    </div></div>
                </div>

                <!-- ============ INDUSTRIES ============ -->
                <div class="section-tabs__panel" data-panel="industries">
                    <div class="admin-card"><div class="admin-card__body"><?php svc_section_header('industries', $f, 'Industries We Serve &amp; <span>Use Cases</span>'); ?></div></div>
                    <div class="admin-card"><div class="admin-card__body">
                        <?php svc_repeater_field('industries', 'Industries', '+ Add industry',
                            '<div class="form-row"><label>Industry Name</label><input type="text" name="industry_title[]" placeholder="Healthcare"></div>' .
                            '<div class="form-row" style="margin-bottom:0;"><label>Use Cases <span class="text-muted">(one per line, unlimited)</span></label><textarea name="industry_items[]" rows="4" placeholder="Medical imaging &amp; diagnostics&#10;Patient risk prediction"></textarea></div>',
                            $repeater_data['industries'],
                            ['input[name="industry_title[]"]' => 'title', 'textarea[name="industry_items[]"]' => ['key' => 'items', 'type' => 'list']]
                        ); ?>
                    </div></div>
                </div>

                <!-- ============ FRAMEWORK ============ -->
                <div class="section-tabs__panel" data-panel="framework">
                    <div class="admin-card"><div class="admin-card__body"><?php svc_section_header('framework', $f, 'Turning AI Into <span>Enterprise Reality</span>'); ?></div></div>
                    <div class="admin-card"><div class="admin-card__body">
                        <?php svc_repeater_field('framework-steps', 'Steps', '+ Add step',
                            '<div class="form-row"><label>Icon</label><input type="text" class="icon-input" name="fw_icon[]"></div>' .
                            '<div class="form-row"><label>Title</label><input type="text" name="fw_title[]"></div>' .
                            '<div class="form-row" style="margin-bottom:0;"><label>Description</label><textarea name="fw_desc[]" rows="2"></textarea></div>',
                            $repeater_data['framework_steps'],
                            ['input.icon-input' => ['key' => 'icon', 'type' => 'icon'], 'input[name="fw_title[]"]' => 'title', 'textarea[name="fw_desc[]"]' => 'desc']
                        ); ?>
                    </div></div>
                </div>

                <!-- ============ WHY CHOOSE US ============ -->
                <div class="section-tabs__panel" data-panel="why">
                    <div class="admin-card"><div class="admin-card__body"><?php svc_section_header('why', $f, 'Why Choose Quantal for <span>AI ML Services?</span>'); ?></div></div>
                    <div class="admin-card"><div class="admin-card__body">
                        <?php svc_repeater_field('why-cards', 'Cards', '+ Add card',
                            '<div class="form-row"><label>Title</label><input type="text" name="why_card_title[]"></div>' .
                            '<div class="form-row" style="margin-bottom:0;"><label>Description</label><textarea name="why_card_desc[]" rows="2"></textarea></div>',
                            $repeater_data['why_cards'],
                            ['input[name="why_card_title[]"]' => 'title', 'textarea[name="why_card_desc[]"]' => 'desc']
                        ); ?>
                    </div></div>
                </div>

                <!-- ============ ENGAGEMENT MODELS ============ -->
                <div class="section-tabs__panel" data-panel="engagement">
                    <div class="admin-card"><div class="admin-card__body"><?php svc_section_header('engagement', $f, 'Three Ways to Engage with <span>Quantal AI</span>'); ?></div></div>
                    <div class="admin-card"><div class="admin-card__body">
                        <?php svc_repeater_field('engagement-models', 'Models', '+ Add model',
                            '<div class="form-row"><label>Badge <span class="text-muted">(e.g. "Model 01" or "Most Popular")</span></label><input type="text" name="eng_badge[]"></div>' .
                            '<div class="form-row"><label>Title</label><input type="text" name="eng_title[]"></div>' .
                            '<div class="form-row"><label>Description</label><textarea name="eng_desc[]" rows="2"></textarea></div>' .
                            '<div class="form-row"><label>Features <span class="text-muted">(one per line)</span></label><textarea name="eng_features[]" rows="4"></textarea></div>' .
                            '<div class="form-row"><label>Button Text</label><input type="text" name="eng_btn_text[]"></div>' .
                            '<div class="form-row" style="margin-bottom:0;"><label><input type="checkbox" class="featured-checkbox" name="eng_featured[]" value="1"> Featured (highlighted)</label></div>',
                            $repeater_data['engagement_models'],
                            ['input[name="eng_badge[]"]' => 'badge', 'input[name="eng_title[]"]' => 'title', 'textarea[name="eng_desc[]"]' => 'desc',
                             'textarea[name="eng_features[]"]' => ['key' => 'features', 'type' => 'list'], 'input[name="eng_btn_text[]"]' => 'btn_text',
                             'input.featured-checkbox' => ['key' => 'featured', 'type' => 'checkbox']]
                        ); ?>
                    </div></div>
                </div>

                <!-- ============ PROCESS TIMELINE ============ -->
                <div class="section-tabs__panel" data-panel="process">
                    <div class="admin-card"><div class="admin-card__body"><?php svc_section_header('process', $f, 'Our AI/ML <span>Development Process</span>'); ?></div></div>
                    <div class="admin-card"><div class="admin-card__body">
                        <?php svc_repeater_field('process-steps', 'Steps', '+ Add step',
                            '<div class="form-row"><label>Title</label><input type="text" name="process_step_title[]"></div>' .
                            '<div class="form-row"><label>Description</label><textarea name="process_step_desc[]" rows="2"></textarea></div>' .
                            '<div class="form-row" style="margin-bottom:0;"><label>Process Tags <span class="text-muted">(one per line)</span></label><textarea name="process_step_tags[]" rows="3" placeholder="Discovery&#10;Strategy"></textarea></div>',
                            $repeater_data['process_steps'],
                            ['input[name="process_step_title[]"]' => 'title', 'textarea[name="process_step_desc[]"]' => 'desc',
                             'textarea[name="process_step_tags[]"]' => ['key' => 'tags', 'type' => 'list']]
                        ); ?>
                    </div></div>
                </div>

                <!-- ============ MID CTA ============ -->
                <div class="section-tabs__panel" data-panel="cta">
                    <div class="admin-card"><div class="admin-card__body">
                        <div class="form-row">
                            <label for="cta_tag">Tag</label>
                            <input type="text" id="cta_tag" name="cta_tag" value="<?= attr($f['cta_tag']) ?>" placeholder="READY TO BUILD WITH AI?">
                        </div>
                        <div class="form-row">
                            <label for="cta_title_html">Heading <span class="text-muted">(HTML allowed)</span></label>
                            <textarea id="cta_title_html" name="cta_title_html" rows="2"><?= e($f['cta_title_html']) ?></textarea>
                        </div>
                        <div class="form-row">
                            <label for="cta_text">Description</label>
                            <textarea id="cta_text" name="cta_text" rows="2"><?= e($f['cta_text']) ?></textarea>
                        </div>
                        <div class="help">Buttons stay fixed ("Schedule a Demo" / "View Case Studies") — not editable here.</div>
                    </div></div>
                </div>

                <!-- ============ FINAL CTA ============ -->
                <div class="section-tabs__panel" data-panel="final_cta">
                    <div class="admin-card"><div class="admin-card__body">
                        <p class="text-muted" style="font-size:13px;margin-top:0;">Bottom-of-page CTA band. Leave any field blank to hide that element; the contact-info line beneath the button is shared site-wide and isn't editable here.</p>
                        <div class="form-row">
                            <label for="final_cta_title_html">Title <span class="text-muted">(HTML allowed)</span></label>
                            <textarea id="final_cta_title_html" name="final_cta_title_html" rows="2" placeholder="Ready to Engineer Your AI System?"><?= e($f['final_cta_title_html']) ?></textarea>
                        </div>
                        <div class="form-row">
                            <label for="final_cta_desc">Description</label>
                            <textarea id="final_cta_desc" name="final_cta_desc" rows="3"><?= e($f['final_cta_desc']) ?></textarea>
                        </div>
                        <div class="form-row">
                            <label for="final_cta_btn_text">Button Text</label>
                            <input type="text" id="final_cta_btn_text" name="final_cta_btn_text" value="<?= attr($f['final_cta_btn_text']) ?>" placeholder="Book a Free Scoping Call">
                        </div>
                        <div class="form-row" style="margin-bottom:0;">
                            <label for="final_cta_btn_url">Button URL <span class="text-muted">(optional — defaults to /contact)</span></label>
                            <input type="text" id="final_cta_btn_url" name="final_cta_btn_url" value="<?= attr($f['final_cta_btn_url']) ?>" placeholder="/contact">
                        </div>
                    </div></div>
                </div>

                <!-- ============ CASE STUDIES ============ -->
                <div class="section-tabs__panel" data-panel="cases">
                    <div class="admin-card"><div class="admin-card__body"><?php svc_section_header('cs', $f, 'Case Studies <span>&amp; Success Stories</span>'); ?></div></div>
                    <div class="admin-card"><div class="admin-card__body">
                        <?php svc_repeater_field('case-studies', 'Case Studies', '+ Add case study',
                            '<div class="form-row"><label>Tag</label><input type="text" name="cs_tag[]" placeholder="Business Impact"></div>' .
                            '<div class="form-row"><label>Title</label><input type="text" name="cs_card_title[]"></div>' .
                            '<div class="form-row"><label>Description</label><textarea name="cs_card_desc[]" rows="2"></textarea></div>' .
                            '<div class="form-row" style="margin-bottom:0;"><label>Result</label><textarea name="cs_result[]" rows="2"></textarea></div>',
                            $repeater_data['case_studies'],
                            ['input[name="cs_tag[]"]' => 'tag', 'input[name="cs_card_title[]"]' => 'title',
                             'textarea[name="cs_card_desc[]"]' => 'desc', 'textarea[name="cs_result[]"]' => 'result']
                        ); ?>
                    </div></div>
                </div>

                <!-- ============ TECH STACK ============ -->
                <div class="section-tabs__panel" data-panel="tech">
                    <div class="admin-card"><div class="admin-card__body"><?php svc_section_header('tech', $f, 'Platforms &amp; <span>Technologies</span>'); ?></div></div>
                    <div class="admin-card"><div class="admin-card__body">
                        <?php svc_repeater_field('tech-categories', 'Categories', '+ Add category',
                            '<div class="form-row"><label>Category Name</label><input type="text" name="tech_cat_title[]" placeholder="Machine Learning Frameworks"></div>' .
                            '<div class="form-row" style="margin-bottom:0;"><label>Technologies <span class="text-muted">(one per line, unlimited)</span></label><textarea name="tech_cat_items[]" rows="4" placeholder="PyTorch&#10;TensorFlow"></textarea></div>',
                            $repeater_data['tech_categories'],
                            ['input[name="tech_cat_title[]"]' => 'title', 'textarea[name="tech_cat_items[]"]' => ['key' => 'items', 'type' => 'list']]
                        ); ?>
                    </div></div>
                </div>

                <!-- ============ SECURITY ============ -->
                <div class="section-tabs__panel" data-panel="security">
                    <div class="admin-card"><div class="admin-card__body"><?php svc_section_header('security', $f, 'Data Security &amp; <span>Compliance</span>'); ?></div></div>
                    <div class="admin-card"><div class="admin-card__body">
                        <?php svc_repeater_field('security-cards', 'Cards', '+ Add card',
                            '<div class="form-row"><label>Title</label><input type="text" name="sec_title[]"></div>' .
                            '<div class="form-row" style="margin-bottom:0;"><label>Description</label><textarea name="sec_desc[]" rows="2"></textarea></div>',
                            $repeater_data['security_cards'],
                            ['input[name="sec_title[]"]' => 'title', 'textarea[name="sec_desc[]"]' => 'desc']
                        ); ?>
                    </div></div>
                </div>

                <!-- ============ RELATED SERVICES ============ -->
                <div class="section-tabs__panel" data-panel="related">
                    <div class="admin-card"><div class="admin-card__body">
                        <?php svc_section_header('related', $f, 'Explore Related <span>Services</span>'); ?>
                        <div class="form-row">
                            <label for="related_group_title">Group Title</label>
                            <input type="text" id="related_group_title" name="related_group_title" value="<?= attr($f['related_group_title']) ?>" placeholder="Recommended Solutions">
                        </div>
                    </div></div>
                    <div class="admin-card"><div class="admin-card__body">
                        <div class="repeater-row__title" style="margin-bottom:10px;">Choose Services <span class="text-muted">(the URL is generated automatically)</span></div>
                        <?php if (empty($other_services)): ?>
                            <p class="text-muted" style="font-size:13px;">No other published services yet.</p>
                        <?php endif; ?>
                        <?php foreach ($other_services as $os): ?>
                            <label style="display:block;padding:6px 0;">
                                <input type="checkbox" name="related_service_ids[]" value="<?= (int) $os['id'] ?>"
                                    <?= in_array((int) $os['id'], $selected_related_ids, true) ? 'checked' : '' ?>>
                                <?= e($os['name']) ?> <span class="text-muted">(<?= e($os['slug']) ?>)</span>
                            </label>
                        <?php endforeach; ?>
                    </div></div>
                </div>

                <!-- ============ KNOWLEDGE HUB ============ -->
                <div class="section-tabs__panel" data-panel="blog">
                    <div class="admin-card"><div class="admin-card__body"><?php svc_section_header('blog', $f, 'Latest Insights &amp; <span>Resources</span>'); ?></div></div>
                    <div class="admin-card"><div class="admin-card__body">
                        <div class="repeater-row__title" style="margin-bottom:10px;">Choose Blog Posts <span class="text-muted">(image, category &amp; link pulled automatically)</span></div>
                        <?php if (empty($all_blog_posts)): ?>
                            <p class="text-muted" style="font-size:13px;">No published posts yet.</p>
                        <?php endif; ?>
                        <div style="max-height:320px;overflow-y:auto;">
                        <?php foreach ($all_blog_posts as $bp): ?>
                            <label style="display:block;padding:6px 0;">
                                <input type="checkbox" name="blog_post_ids[]" value="<?= (int) $bp['id'] ?>"
                                    <?= in_array((int) $bp['id'], $selected_blog_ids, true) ? 'checked' : '' ?>>
                                <?= e($bp['title']) ?>
                            </label>
                        <?php endforeach; ?>
                        </div>
                    </div></div>
                </div>

                <!-- ============ FAQ ============ -->
                <div class="section-tabs__panel" data-panel="faq">
                    <div class="admin-card"><div class="admin-card__body">
                        <div class="form-row">
                            <label for="faq_intro">Intro Text</label>
                            <textarea id="faq_intro" name="faq_intro" rows="2"><?= e($f['faq_intro']) ?></textarea>
                        </div>
                    </div></div>
                    <div class="admin-card"><div class="admin-card__body">
                        <?php svc_repeater_field('faqs', 'Questions', '+ Add FAQ',
                            '<div class="form-row"><label>Question</label><input type="text" name="faq_question[]"></div>' .
                            '<div class="form-row" style="margin-bottom:0;"><label>Answer</label><textarea name="faq_answer[]" rows="2"></textarea></div>',
                            $repeater_data['faqs'],
                            ['input[name="faq_question[]"]' => 'question', 'textarea[name="faq_answer[]"]' => 'answer']
                        ); ?>
                    </div></div>
                </div>

                <!-- ============ SEO ============ -->
                <div class="section-tabs__panel" data-panel="seo">
                    <div class="admin-card"><div class="admin-card__body">
                        <div class="form-row">
                            <label for="meta_title">Meta Title <span class="text-muted">(optional — defaults to Display Title)</span></label>
                            <input type="text" id="meta_title" name="meta_title" maxlength="255" value="<?= attr($f['meta_title']) ?>">
                        </div>
                        <div class="form-row">
                            <label for="meta_description">Meta Description <span class="text-muted">(optional — defaults to Short Description)</span></label>
                            <textarea id="meta_description" name="meta_description" rows="2" maxlength="320"><?= e($f['meta_description']) ?></textarea>
                        </div>
                        <div class="form-row">
                            <label for="meta_keywords">Meta Keywords</label>
                            <input type="text" id="meta_keywords" name="meta_keywords" value="<?= attr($f['meta_keywords']) ?>">
                        </div>
                        <div class="form-row">
                            <label for="og_image">OG Image <span class="text-muted">(optional — defaults to Featured Image)</span></label>
                            <input type="text" id="og_image" name="og_image" value="<?= attr($f['og_image']) ?>">
                        </div>
                        <div class="form-row">
                            <label for="canonical">Canonical URL <span class="text-muted">(optional — defaults to the live URL)</span></label>
                            <input type="text" id="canonical" name="canonical" value="<?= attr($f['canonical']) ?>">
                        </div>
                        <div class="form-row">
                            <label for="robots">Robots</label>
                            <select id="robots" name="robots">
                                <option value=""                     <?= $f['robots'] === ''                     ? 'selected' : '' ?>>Default</option>
                                <option value="index,follow"         <?= $f['robots'] === 'index,follow'         ? 'selected' : '' ?>>index, follow</option>
                                <option value="noindex,follow"       <?= $f['robots'] === 'noindex,follow'       ? 'selected' : '' ?>>noindex, follow</option>
                                <option value="index,nofollow"       <?= $f['robots'] === 'index,nofollow'       ? 'selected' : '' ?>>index, nofollow</option>
                                <option value="noindex,nofollow"     <?= $f['robots'] === 'noindex,nofollow'     ? 'selected' : '' ?>>noindex, nofollow</option>
                            </select>
                        </div>
                        <div class="form-row">
                            <label for="schema_json">
                                JSON-LD Schema
                                <a href="#" id="gen-service-schema" class="admin-btn admin-btn--ghost admin-btn--small" style="float:right;">Generate Service schema</a>
                            </label>
                            <textarea id="schema_json" name="schema_json" rows="6" class="json-textarea"><?= e($f['schema_json']) ?></textarea>
                        </div>
                    </div></div>
                </div>

                <!-- ============ LEGACY FIELDS ============ -->
                <div class="section-tabs__panel" data-panel="legacy">
                    <div class="admin-card"><div class="admin-card__body">
                        <p class="text-muted" style="font-size:13px;">
                            These fields predate the section-based layout and aren't used by the current design.
                            Kept only so older data isn't lost.
                        </p>
                        <div class="form-row">
                            <label for="overview_html">Overview / Introduction (legacy)</label>
                            <textarea id="overview_html" name="overview_html" rows="3"><?= e($f['overview_html']) ?></textarea>
                        </div>
                        <div class="form-row">
                            <label for="features_html">Features &amp; Capabilities (legacy)</label>
                            <textarea id="features_html" name="features_html" rows="3"><?= e($f['features_html']) ?></textarea>
                        </div>
                        <div class="form-row">
                            <label for="use_cases_html">Use Cases (legacy)</label>
                            <textarea id="use_cases_html" name="use_cases_html" rows="3"><?= e($f['use_cases_html']) ?></textarea>
                        </div>
                        <div class="form-row" style="margin-bottom:0;">
                            <label for="benefits_html">Benefits (legacy)</label>
                            <textarea id="benefits_html" name="benefits_html" rows="3"><?= e($f['benefits_html']) ?></textarea>
                        </div>
                    </div></div>
                </div>

            </div>
        </div>

        <div class="admin-card" style="margin-top:18px;position:sticky;bottom:14px;">
            <div class="admin-card__body" style="display:flex;gap:10px;">
                <button type="submit" class="admin-btn"><?= $service ? 'Update Service' : 'Create Service' ?></button>
                <a href="<?= ADMIN_URL ?>/services.php" class="admin-btn admin-btn--ghost">Cancel</a>
            </div>
        </div>
    </form>

    <!-- CKEditor 5 (minimal inline toolbar — these fields hold short title fragments, not blocks) -->
    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
    <script>
    (function () {
        // ---- Section tabs ----
        var navBtns = document.querySelectorAll('#section-tabs-nav button');
        var panels = document.querySelectorAll('.section-tabs__panel');
        function showTab(key) {
            navBtns.forEach(function (b) { b.classList.toggle('is-active', b.dataset.tab === key); });
            panels.forEach(function (p) { p.classList.toggle('is-active', p.dataset.panel === key); });
        }
        navBtns.forEach(function (b) { b.addEventListener('click', function () { showTab(b.dataset.tab); }); });
        showTab(navBtns[0] ? navBtns[0].dataset.tab : 'core');

        // ---- Icon pickers on static (non-repeater) fields ----
        var iconField = document.getElementById('icon_class');
        if (iconField) initIconPicker(iconField);

        // ---- CKEditor on every "_title_html" field (short inline fragments: bold/italic only) ----
        document.querySelectorAll('textarea[id$="_title_html"]').forEach(function (el) {
            ClassicEditor.create(el, { toolbar: ['bold', 'italic', '|', 'undo', 'redo'] }).catch(function (err) { console.error(err); });
        });

        // ---- Slug auto-generation ----
        var titleEl = document.getElementById('title');
        var slugEl = document.getElementById('slug');
        if (titleEl && slugEl) {
            var userTouchedSlug = slugEl.value.trim() !== '';
            slugEl.addEventListener('input', function () { userTouchedSlug = slugEl.value.trim() !== ''; });
            titleEl.addEventListener('input', function () {
                if (userTouchedSlug) return;
                slugEl.value = titleEl.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').substring(0, 120);
            });
        }

        // ---- JSON-LD generator ----
        var genBtn = document.getElementById('gen-service-schema');
        if (genBtn) {
            genBtn.addEventListener('click', function (e) {
                e.preventDefault();
                var SITE = <?= json_encode(defined('SITE_URL') ? SITE_URL : '') ?>;
                var slug = slugEl ? slugEl.value : '';
                var data = {
                    '@context': 'https://schema.org',
                    '@type': 'Service',
                    'name': titleEl ? titleEl.value : '',
                    'description': document.getElementById('excerpt') ? document.getElementById('excerpt').value : '',
                    'url': SITE + '/services/' + slug,
                    'provider': { '@type': 'Organization', 'name': <?= json_encode(SITE_NAME) ?> }
                };
                document.getElementById('schema_json').value = JSON.stringify(data, null, 2);
            });
        }
    })();
    </script>

    <?php
    require __DIR__ . '/_footer.php';
    exit;
}

// ===========================================================================
// LIST view
// ===========================================================================
$rows = $pdo->query(
    'SELECT id, slug, name, title, service_number, status, display_on_home, updated_at
     FROM services ORDER BY service_number ASC, id ASC LIMIT 200'
)->fetchAll();

$admin_page_title = 'Services';
$admin_active     = 'services';
require __DIR__ . '/_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>Services</h1>
        <div class="subtitle"><?= count($rows) ?> service<?= count($rows) === 1 ? '' : 's' ?></div>
    </div>
    <a href="?action=new" class="admin-btn">New Service</a>
</div>

<div class="admin-card">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Order</th>
                <th>Name</th>
                <th>Title</th>
                <th>Status</th>
                <th>Homepage</th>
                <th class="col-actions">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="6" class="empty-state">
                    <h3>No services yet</h3>
                    <a href="?action=new" class="admin-btn">New Service</a>
                </td></tr>
            <?php else: ?>
                <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= (int) $r['service_number'] ?></td>
                    <td><a href="?action=edit&amp;id=<?= (int) $r['id'] ?>"><?= e($r['name']) ?></a></td>
                    <td><?= e($r['title']) ?></td>
                    <td><span class="pill pill--<?= attr($r['status']) ?>"><?= e($r['status']) ?></span></td>
                    <td><?= $r['display_on_home'] ? '✓' : '' ?></td>
                    <td class="col-actions">
                        <?php if ($r['status'] === 'published'): ?>
                            <a href="<?= url('/services/' . $r['slug']) ?>" target="_blank" rel="noopener" class="admin-btn admin-btn--ghost admin-btn--small">View</a>
                        <?php endif; ?>
                        <a href="?action=edit&amp;id=<?= (int) $r['id'] ?>" class="admin-btn admin-btn--small">Edit</a>
                        <form method="post" action="?action=delete" style="display:inline;"
                              onsubmit="return confirm('Delete this service permanently?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                            <button type="submit" class="admin-btn admin-btn--danger admin-btn--small">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
