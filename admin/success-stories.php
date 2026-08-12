<?php
/**
 * Admin — Success Stories (full CRUD).
 *
 * Every dynamic section of the redesigned pages/success-stories/single.php
 * detail template is editable here, organized into tabs (see .section-tabs
 * in admin.css) — structurally the same tabbed-form + repeater pattern as
 * admin/hire.php / admin/services.php. See
 * db/migrations/2026-08-12-002-extend-success-stories-table.sql for the
 * full column list. Every section below is optional — leave its fields
 * blank / its repeater empty and the frontend hides that section entirely.
 */

require __DIR__ . '/bootstrap.php';

$pdo = db();
if (!$pdo) { die('Database connection failed.'); }

$action = $_GET['action'] ?? 'list';
$user   = auth_user();

// ===========================================================================
// Helpers
// ===========================================================================

function story_slugify(string $t): string
{
    $t = strtolower(trim($t));
    if (function_exists('iconv')) {
        $c = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $t);
        if ($c !== false) $t = $c;
    }
    $t = preg_replace('/[^a-z0-9]+/', '-', $t) ?? '';
    return trim($t, '-') ?: 'story';
}

function story_unique_slug(PDO $pdo, string $base, ?int $excl = null): string
{
    $slug = $base; $i = 2;
    while (true) {
        $stmt = $pdo->prepare('SELECT id FROM success_stories WHERE slug = :s' . ($excl ? ' AND id != :id' : ''));
        $p = [':s' => $slug];
        if ($excl) $p[':id'] = $excl;
        $stmt->execute($p);
        if (!$stmt->fetch()) return $slug;
        $slug = $base . '-' . $i++;
    }
}

// ===========================================================================
// DELETE
// ===========================================================================
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_or_die();
    $del_id = (int) ($_POST['id'] ?? 0);
    if ($del_id > 0) {
        $pdo->prepare('DELETE FROM success_stories WHERE id = :id')->execute([':id' => $del_id]);
        flash('success', 'Success story deleted.');
    }
    header('Location: ' . ADMIN_URL . '/success-stories.php'); exit;
}

// ===========================================================================
// NEW / EDIT
// ===========================================================================
if ($action === 'new' || $action === 'edit') {
    $id    = (int) ($_GET['id'] ?? 0);
    $story = null;

    if ($action === 'edit' && $id > 0) {
        $s = $pdo->prepare('SELECT * FROM success_stories WHERE id = :id');
        $s->execute([':id' => $id]);
        $story = $s->fetch();
        if (!$story) {
            flash('error', 'Success story not found.');
            header('Location: ' . ADMIN_URL . '/success-stories.php'); exit;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_verify_or_die();

        $title        = trim((string) ($_POST['title'] ?? ''));
        $company_name = trim((string) ($_POST['company_name'] ?? ''));
        $industry     = trim((string) ($_POST['industry'] ?? ''));
        $excerpt      = trim((string) ($_POST['excerpt'] ?? ''));
        $sort_order_raw = (string) ($_POST['sort_order'] ?? '1');

        $errors = [];
        if ($title        === '') $errors[] = 'Title is required.';
        if ($company_name === '') $errors[] = 'Company name is required.';
        if ($industry     === '') $errors[] = 'Industry is required.';
        if ($excerpt       === '') $errors[] = 'Short description (excerpt) is required.';
        if (!ctype_digit($sort_order_raw) && !is_numeric($sort_order_raw)) {
            $errors[] = 'Sort order must be a number.';
        }

        // Multiple JSON-LD schema blocks
        $schema_blocks = svc_build_repeater($_POST, ['label' => 'schema_label', 'code' => 'schema_code']);
        foreach ($schema_blocks as $idx => $block) {
            if ($block['code'] === '') { continue; }
            json_decode($block['code']);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $errors[] = 'JSON-LD schema #' . ($idx + 1) . ($block['label'] !== '' ? " ({$block['label']})" : '')
                    . ' is invalid: ' . json_last_error_msg();
            }
        }
        $schema_json = json_encode($schema_blocks, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($errors) {
            foreach ($errors as $e) flash('error', $e);
            $story = array_merge((array) $story, $_POST, ['id' => $story['id'] ?? null]);
        } else {
            $slug_raw  = trim((string) ($_POST['slug'] ?? ''));
            $slug_base = $slug_raw !== '' ? story_slugify($slug_raw) : story_slugify($title);
            $slug      = story_unique_slug($pdo, $slug_base, $story['id'] ?? null);
            $status    = in_array($_POST['status'] ?? '', ['draft', 'published', 'archived'], true)
                            ? $_POST['status'] : 'draft';
            $pub_at    = $status === 'published'
                            ? (($story['published_at'] ?? null) ?: date('Y-m-d H:i:s'))
                            : null;
            $is_featured = isset($_POST['featured']) ? 1 : 0;
            $category_id = (int) ($_POST['category_id'] ?? 0);

            // Plain scalar fields — trimmed, passed straight through.
            $plain_fields = [
                'company_website', 'company_logo', 'company_size',
                'client_name', 'client_title', 'client_image',
                'page_label', 'crumb', 'hero_eyebrow',
                'services_provided', 'tech_stack_summary', 'third_party_services', 'outcome_summary',
                'challenge_sub', 'challenge_title', 'challenge_image',
                'solution_sub', 'solution_title', 'solution_image',
                'responsibilities_sub', 'responsibilities_title', 'responsibilities_text',
                'future_sub', 'future_title', 'future_text',
                'final_cta_sub', 'final_cta_title',
                'meta_title', 'meta_description', 'meta_keywords', 'og_image', 'canonical', 'robots',
                'featured_image', 'featured_alt',
            ];
            // Rich-content fields (CKEditor) — sanitized, not escaped.
            $html_fields = ['body_html', 'challenge_html', 'solution_html'];

            $cols = [
                'slug'          => $slug,
                'title'         => $title,
                'excerpt'       => $excerpt,
                'company_name'  => $company_name,
                'industry'      => $industry,
                'category_id'   => $category_id > 0 ? $category_id : null,
                'sort_order'    => max(1, (int) $sort_order_raw),
                'schema_json'   => $schema_json,
                'status'        => $status,
                'published_at'  => $pub_at,
                'featured'      => $is_featured,
                'author_id'     => $user['id'] ?? null,
                'final_cta_desc' => trim((string) ($_POST['final_cta_desc'] ?? '')),
            ];
            foreach ($plain_fields as $fld) {
                $cols[$fld] = trim((string) ($_POST[$fld] ?? ''));
            }
            foreach ($html_fields as $fld) {
                $cols[$fld] = sanitize_html_fragment((string) ($_POST[$fld] ?? ''));
            }

            // Repeaters
            $cols['objectives_json'] = json_encode(svc_build_repeater($_POST,
                ['title' => 'obj_title', 'desc' => 'obj_desc'], [], ['active' => 'obj_active']));
            $cols['architecture_json'] = json_encode(svc_build_repeater($_POST,
                ['icon' => 'arch_icon', 'title' => 'arch_title', 'subtitle' => 'arch_subtitle'],
                ['items' => 'arch_items'], ['active' => 'arch_active']));
            $cols['workflow_json'] = json_encode(svc_build_repeater($_POST,
                ['title' => 'wf_title', 'desc' => 'wf_desc'], [], ['active' => 'wf_active']));
            $cols['results_json'] = json_encode(svc_build_repeater($_POST,
                ['title' => 'res_title', 'desc' => 'res_desc'], [], ['active' => 'res_active']));
            $cols['deliverables_json'] = json_encode(svc_build_repeater($_POST,
                ['icon' => 'del_icon', 'title' => 'del_title', 'desc' => 'del_desc'], [], ['active' => 'del_active']));
            $cols['tech_stack_items_json'] = json_encode(svc_build_repeater($_POST,
                ['icon' => 'tech_icon', 'name' => 'tech_name', 'purpose' => 'tech_purpose'], [], ['active' => 'tech_active']));
            $cols['why_cards_json'] = json_encode(svc_build_repeater($_POST,
                ['title' => 'why_title', 'desc' => 'why_desc'], [], ['active' => 'why_active']));
            $cols['responsibilities_json'] = json_encode(svc_build_repeater($_POST,
                ['text' => 'resp_text'], [], ['active' => 'resp_active']));
            $cols['future_json'] = json_encode(svc_build_repeater($_POST,
                ['text' => 'future_text_item'], [], ['active' => 'future_active']));

            // Related stories: manual picks (IDs) + auto-fill count
            $cols['related_story_ids_json'] = json_encode(array_values(array_filter(array_map('intval', $_POST['related_story_ids'] ?? []))));
            $cols['related_count'] = max(0, (int) ($_POST['related_count'] ?? 3));

            $bind = [];
            foreach ($cols as $k => $v) $bind[":$k"] = $v;

            if ($is_featured) {
                // Only one Success Story can be the featured project at a time.
                $pdo->exec('UPDATE success_stories SET featured = 0' . (isset($story['id']) ? ' WHERE id != ' . (int) $story['id'] : ''));
            }

            if (isset($story['id'])) {
                $sets = implode(', ', array_map(fn($k) => "`$k` = :$k", array_keys($cols)));
                $stmt = $pdo->prepare("UPDATE success_stories SET $sets WHERE id = :where_id");
                $bind[':where_id'] = $story['id'];
                $stmt->execute($bind);
                flash('success', 'Success story updated.');
                header('Location: ' . ADMIN_URL . '/success-stories.php?action=edit&id=' . $story['id']); exit;
            } else {
                $keys = implode(', ', array_map(fn($k) => "`$k`", array_keys($cols)));
                $phs  = implode(', ', array_map(fn($k) => ":$k", array_keys($cols)));
                $stmt = $pdo->prepare("INSERT INTO success_stories ($keys) VALUES ($phs)");
                $stmt->execute($bind);
                $newId = (int) $pdo->lastInsertId();
                flash('success', 'Success story created.');
                header('Location: ' . ADMIN_URL . '/success-stories.php?action=edit&id=' . $newId); exit;
            }
        }
    }

    // ---- Defaults for the form ----
    $f = array_merge([
        'id' => null, 'slug' => '', 'title' => '', 'excerpt' => '', 'body_html' => '',
        'company_name' => '', 'company_website' => '', 'company_logo' => '',
        'industry' => '', 'company_size' => '', 'category_id' => null, 'sort_order' => 1,
        'challenge_html' => '', 'solution_html' => '', 'results_html' => '',
        'featured_image' => '', 'featured_alt' => '',
        'client_name' => '', 'client_title' => '', 'client_image' => '',
        'meta_title' => '', 'meta_description' => '', 'meta_keywords' => '',
        'og_image' => '', 'canonical' => '', 'robots' => '', 'schema_json' => '',
        'status' => 'draft', 'featured' => 0, 'published_at' => null,
        'page_label' => '', 'crumb' => '', 'hero_eyebrow' => '',
        'services_provided' => '', 'tech_stack_summary' => '', 'third_party_services' => '', 'outcome_summary' => '',
        'challenge_sub' => '', 'challenge_title' => '', 'challenge_image' => '',
        'solution_sub' => '', 'solution_title' => '', 'solution_image' => '',
        'responsibilities_sub' => '', 'responsibilities_title' => '', 'responsibilities_text' => '',
        'future_sub' => '', 'future_title' => '', 'future_text' => '',
        'final_cta_sub' => '', 'final_cta_title' => '', 'final_cta_desc' => '',
        'related_count' => 3,
    ], (array) $story);

    // JSON-backed data for the JS repeaters (edit mode) — empty arrays for "new"
    $repeater_data = [
        'objectives'     => svc_json_decode($story['objectives_json'] ?? null),
        'architecture'   => svc_json_decode($story['architecture_json'] ?? null),
        'workflow'       => svc_json_decode($story['workflow_json'] ?? null),
        'results'        => svc_json_decode($story['results_json'] ?? null),
        'deliverables'   => svc_json_decode($story['deliverables_json'] ?? null),
        'tech_stack'     => svc_json_decode($story['tech_stack_items_json'] ?? null),
        'why_cards'      => svc_json_decode($story['why_cards_json'] ?? null),
        'responsibilities' => svc_json_decode($story['responsibilities_json'] ?? null),
        'future'         => svc_json_decode($story['future_json'] ?? null),
        'json_ld_schemas' => svc_normalize_schemas($story['schema_json'] ?? null),
    ];
    // Repeater items store `items` (architecture bullet lists) as arrays;
    // svc_repeater_field's JS hydration expects strings for textarea fields,
    // so join them back into "one per line" for the edit-mode template.
    foreach ($repeater_data['architecture'] as &$arch_row) {
        if (isset($arch_row['items']) && is_array($arch_row['items'])) {
            $arch_row['items'] = implode("\n", $arch_row['items']);
        }
    }
    unset($arch_row);

    $selected_related_ids = array_map('intval', svc_json_decode($story['related_story_ids_json'] ?? null));

    $categories = get_success_story_categories($pdo, []);
    $other_stories = array_filter(
        get_success_stories($pdo, ['status' => 'published']),
        static fn($s) => (int) $s['id'] !== (int) ($f['id'] ?? 0)
    );

    $admin_page_title = $story ? 'Edit Success Story' : 'New Success Story';
    $admin_active     = 'success-stories';
    require __DIR__ . '/_header.php';
    ?>

    <script src="<?= ADMIN_URL ?>/assets/js/repeater.js"></script>
    <script src="<?= ADMIN_URL ?>/assets/js/fa-icons.js"></script>
    <script src="<?= ADMIN_URL ?>/assets/js/icon-picker.js"></script>

    <div class="admin-page-header">
        <div>
            <h1><?= $story ? 'Edit Success Story' : 'New Success Story' ?></h1>
            <?php if ($story): ?>
                <div class="subtitle">
                    Slug: <code class="text-mono"><?= e($f['slug']) ?></code>
                    <?php if ($f['status'] === 'published'): ?>
                        &middot; <a href="<?= url('/success-stories/' . $f['slug']) ?>" target="_blank" rel="noopener">View live &rarr;</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <a href="<?= ADMIN_URL ?>/success-stories.php" class="admin-btn admin-btn--ghost">Back to list</a>
    </div>

    <form method="post" class="admin-form" id="story-form">
        <?= csrf_field() ?>

        <div class="section-tabs">
            <div class="section-tabs__nav" id="section-tabs-nav">
                <?php
                $tabs = [
                    'core' => 'Core', 'hero' => 'Hero', 'content' => 'Content & Info',
                    'objectives' => 'Objectives', 'architecture' => 'Architecture',
                    'challenge' => 'Challenge', 'solution' => 'Solution', 'workflow' => 'Workflow',
                    'results' => 'Results & Impact', 'deliverables' => 'What We Delivered',
                    'tech' => 'Tech Stack', 'why' => 'Why Choose',
                    'responsibilities' => 'Client Responsibilities', 'future' => 'Future Enhancements',
                    'related' => 'Related Stories', 'final_cta' => 'Final CTA', 'seo' => 'SEO',
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
                            <label for="title">Title *</label>
                            <input type="text" id="title" name="title" required value="<?= attr($f['title']) ?>">
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                            <div class="form-row">
                                <label for="slug">Slug</label>
                                <input type="text" id="slug" name="slug" value="<?= attr($f['slug']) ?>" placeholder="auto-generated">
                                <div class="help">Live at <code class="text-mono">/success-stories/<?= e($f['slug'] ?: '{slug}') ?></code></div>
                            </div>
                            <div class="form-row">
                                <label for="sort_order">Sort Order</label>
                                <input type="number" id="sort_order" name="sort_order" min="1" value="<?= (int) $f['sort_order'] ?>">
                            </div>
                        </div>
                        <div class="form-row">
                            <label for="excerpt">Short Description / Excerpt *</label>
                            <textarea id="excerpt" name="excerpt" rows="2" required><?= e($f['excerpt']) ?></textarea>
                            <div class="help">Used as the hero intro line and listing-card excerpt.</div>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                            <div class="form-row">
                                <label for="category_id">Category</label>
                                <select id="category_id" name="category_id">
                                    <option value="">— None —</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= (int) $cat['id'] ?>" <?= (int) $f['category_id'] === (int) $cat['id'] ? 'selected' : '' ?>>
                                            <?= e($cat['name']) ?><?= $cat['status'] === 'inactive' ? ' (inactive)' : '' ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="help">Manage categories in <a href="<?= ADMIN_URL ?>/success-story-categories.php" target="_blank" rel="noopener">Story Categories</a>.</div>
                            </div>
                            <div class="form-row">
                                <label for="industry">Industry *</label>
                                <input type="text" id="industry" name="industry" required value="<?= attr($f['industry']) ?>">
                            </div>
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
                                <input type="checkbox" name="featured" <?= $f['featured'] ? 'checked' : '' ?>>
                                Featured Project <span class="text-muted">(shown on the /success-stories main page — only one story can be featured; checking this unfeatures any other)</span>
                            </label>
                        </div>
                        <?php if ($f['published_at']): ?>
                            <div class="text-muted" style="font-size:12px;">Published <?= e(substr((string) $f['published_at'], 0, 16)) ?></div>
                        <?php endif; ?>
                    </div></div>
                    <div class="admin-card"><div class="admin-card__body">
                        <div class="form-row">
                            <label for="featured_image">Featured / Hero Image</label>
                            <input type="text" id="featured_image" name="featured_image" value="<?= attr($f['featured_image']) ?>" placeholder="/uploads/success-story/...">
                            <div class="help">Copy a path from the <a href="<?= ADMIN_URL ?>/media.php" target="_blank" rel="noopener">Media Library</a>, or paste any URL. Used for the hero image, listing cards, and social sharing.</div>
                        </div>
                        <?php if (!empty($f['featured_image'])): ?>
                            <img src="<?= attr(media_url($f['featured_image'])) ?>" style="max-width:220px;border-radius:4px;border:1px solid var(--admin-border);margin-top:8px;" alt="">
                        <?php endif; ?>
                        <div class="form-row" style="margin-top:10px;">
                            <label for="featured_alt">Alt Text</label>
                            <input type="text" id="featured_alt" name="featured_alt" value="<?= attr($f['featured_alt']) ?>">
                        </div>
                    </div></div>
                    <div class="admin-card"><div class="admin-card__body">
                        <div class="repeater-row__title" style="margin-bottom:10px;">Company (legacy / internal reference)</div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                            <div class="form-row">
                                <label for="company_name">Company Name *</label>
                                <input type="text" id="company_name" name="company_name" required value="<?= attr($f['company_name']) ?>">
                            </div>
                            <div class="form-row">
                                <label for="company_size">Company Size</label>
                                <input type="text" id="company_size" name="company_size" value="<?= attr($f['company_size']) ?>">
                            </div>
                        </div>
                        <div class="form-row">
                            <label for="company_website">Website</label>
                            <input type="text" id="company_website" name="company_website" value="<?= attr($f['company_website']) ?>">
                        </div>
                        <div class="form-row" style="margin-bottom:0;">
                            <label for="company_logo">Company Logo URL</label>
                            <input type="text" id="company_logo" name="company_logo" value="<?= attr($f['company_logo']) ?>">
                        </div>
                    </div></div>
                </div>

                <!-- ============ HERO ============ -->
                <div class="section-tabs__panel" data-panel="hero">
                    <div class="admin-card"><div class="admin-card__body">
                        <p class="text-muted" style="font-size:13px;margin-top:0;">The existing hero layout is unchanged — this only fills in its content. Title, image, and category come from the Core tab; Short Description (excerpt) is the hero intro line.</p>
                        <div class="form-row">
                            <label for="page_label">Page Banner Heading <span class="text-muted">(optional — defaults to Title)</span></label>
                            <input type="text" id="page_label" name="page_label" value="<?= attr($f['page_label']) ?>">
                        </div>
                        <div class="form-row">
                            <label for="crumb">Breadcrumb Label <span class="text-muted">(optional — defaults to Title)</span></label>
                            <input type="text" id="crumb" name="crumb" value="<?= attr($f['crumb']) ?>">
                        </div>
                        <div class="form-row" style="margin-bottom:0;">
                            <label for="hero_eyebrow">Hero Eyebrow / Subtitle <span class="text-muted">(optional)</span></label>
                            <input type="text" id="hero_eyebrow" name="hero_eyebrow" value="<?= attr($f['hero_eyebrow']) ?>">
                        </div>
                    </div></div>
                </div>

                <!-- ============ CONTENT & INFO ============ -->
                <div class="section-tabs__panel" data-panel="content">
                    <div class="admin-card"><div class="admin-card__body">
                        <div class="form-row" style="margin-bottom:0;">
                            <label for="body_html">Main Content</label>
                            <textarea id="body_html" name="body_html" rows="8"><?= e($f['body_html']) ?></textarea>
                        </div>
                    </div></div>
                    <div class="admin-card"><div class="admin-card__body">
                        <div class="repeater-row__title" style="margin-bottom:10px;">Information Panel <span class="text-muted">(Industry comes from the Core tab)</span></div>
                        <div class="form-row">
                            <label for="services_provided">Services Provided</label>
                            <input type="text" id="services_provided" name="services_provided" value="<?= attr($f['services_provided']) ?>">
                        </div>
                        <div class="form-row">
                            <label for="tech_stack_summary">Tech Stack <span class="text-muted">(comma-separated, shown in the info panel)</span></label>
                            <input type="text" id="tech_stack_summary" name="tech_stack_summary" value="<?= attr($f['tech_stack_summary']) ?>" placeholder="Claude AI, n8n, PhantomBuster">
                        </div>
                        <div class="form-row">
                            <label for="third_party_services">3rd Party Services</label>
                            <input type="text" id="third_party_services" name="third_party_services" value="<?= attr($f['third_party_services']) ?>">
                        </div>
                        <div class="form-row" style="margin-bottom:0;">
                            <label for="outcome_summary">Outcome</label>
                            <input type="text" id="outcome_summary" name="outcome_summary" value="<?= attr($f['outcome_summary']) ?>">
                        </div>
                    </div></div>
                </div>

                <!-- ============ OBJECTIVES ============ -->
                <div class="section-tabs__panel" data-panel="objectives">
                    <div class="admin-card"><div class="admin-card__body">
                        <?php svc_repeater_field('objectives', 'Objectives', '+ Add objective',
                            '<div class="form-row"><label>Title</label><input type="text" name="obj_title[]"></div>' .
                            '<div class="form-row"><label>Description <span class="text-muted">(optional)</span></label><textarea name="obj_desc[]" rows="2"></textarea></div>' .
                            '<div class="form-row" style="margin-bottom:0;"><label><input type="checkbox" class="active-checkbox" name="obj_active[]" value="1" checked> Active</label></div>',
                            $repeater_data['objectives'],
                            ['input[name="obj_title[]"]' => 'title', 'textarea[name="obj_desc[]"]' => 'desc',
                             'input.active-checkbox' => ['key' => 'active', 'type' => 'checkbox']]
                        ); ?>
                    </div></div>
                </div>

                <!-- ============ ARCHITECTURE ============ -->
                <div class="section-tabs__panel" data-panel="architecture">
                    <div class="admin-card"><div class="admin-card__body">
                        <p class="text-muted" style="font-size:13px;margin-top:0;">Each step renders as one node in the Proposed Architecture flow diagram, in the order listed below.</p>
                        <?php svc_repeater_field('architecture', 'Architecture Steps', '+ Add step',
                            '<div class="form-row"><label>Icon</label><input type="text" class="icon-input" name="arch_icon[]" placeholder="fas fa-brain"></div>' .
                            '<div class="form-row"><label>Title</label><input type="text" name="arch_title[]"></div>' .
                            '<div class="form-row"><label>Subtitle <span class="text-muted">(optional)</span></label><input type="text" name="arch_subtitle[]"></div>' .
                            '<div class="form-row"><label>Bullet Items <span class="text-muted">(optional — one per line)</span></label><textarea name="arch_items[]" rows="3"></textarea></div>' .
                            '<div class="form-row" style="margin-bottom:0;"><label><input type="checkbox" class="active-checkbox" name="arch_active[]" value="1" checked> Active</label></div>',
                            $repeater_data['architecture'],
                            ['input.icon-input' => ['key' => 'icon', 'type' => 'icon'], 'input[name="arch_title[]"]' => 'title',
                             'input[name="arch_subtitle[]"]' => 'subtitle', 'textarea[name="arch_items[]"]' => 'items',
                             'input.active-checkbox' => ['key' => 'active', 'type' => 'checkbox']]
                        ); ?>
                    </div></div>
                </div>

                <!-- ============ CHALLENGE ============ -->
                <div class="section-tabs__panel" data-panel="challenge">
                    <div class="admin-card"><div class="admin-card__body">
                        <div class="form-row">
                            <label for="challenge_sub">Eyebrow / Subtitle <span class="text-muted">(optional)</span></label>
                            <input type="text" id="challenge_sub" name="challenge_sub" value="<?= attr($f['challenge_sub']) ?>">
                        </div>
                        <div class="form-row">
                            <label for="challenge_title">Title</label>
                            <input type="text" id="challenge_title" name="challenge_title" value="<?= attr($f['challenge_title']) ?>">
                        </div>
                        <div class="form-row">
                            <label for="challenge_html">Content</label>
                            <textarea id="challenge_html" name="challenge_html" rows="5"><?= e($f['challenge_html']) ?></textarea>
                        </div>
                        <div class="form-row" style="margin-bottom:0;">
                            <label for="challenge_image">Image <span class="text-muted">(optional)</span></label>
                            <input type="text" id="challenge_image" name="challenge_image" value="<?= attr($f['challenge_image']) ?>">
                        </div>
                    </div></div>
                </div>

                <!-- ============ SOLUTION ============ -->
                <div class="section-tabs__panel" data-panel="solution">
                    <div class="admin-card"><div class="admin-card__body">
                        <div class="form-row">
                            <label for="solution_sub">Eyebrow / Subtitle <span class="text-muted">(optional)</span></label>
                            <input type="text" id="solution_sub" name="solution_sub" value="<?= attr($f['solution_sub']) ?>">
                        </div>
                        <div class="form-row">
                            <label for="solution_title">Title</label>
                            <input type="text" id="solution_title" name="solution_title" value="<?= attr($f['solution_title']) ?>">
                        </div>
                        <div class="form-row">
                            <label for="solution_html">Content</label>
                            <textarea id="solution_html" name="solution_html" rows="5"><?= e($f['solution_html']) ?></textarea>
                        </div>
                        <div class="form-row" style="margin-bottom:0;">
                            <label for="solution_image">Image <span class="text-muted">(optional)</span></label>
                            <input type="text" id="solution_image" name="solution_image" value="<?= attr($f['solution_image']) ?>">
                        </div>
                    </div></div>
                </div>

                <!-- ============ WORKFLOW ============ -->
                <div class="section-tabs__panel" data-panel="workflow">
                    <div class="admin-card"><div class="admin-card__body">
                        <?php svc_repeater_field('workflow', 'Workflow Steps', '+ Add step',
                            '<div class="form-row"><label>Title</label><input type="text" name="wf_title[]"></div>' .
                            '<div class="form-row"><label>Description</label><textarea name="wf_desc[]" rows="3"></textarea></div>' .
                            '<div class="form-row" style="margin-bottom:0;"><label><input type="checkbox" class="active-checkbox" name="wf_active[]" value="1" checked> Active</label></div>',
                            $repeater_data['workflow'],
                            ['input[name="wf_title[]"]' => 'title', 'textarea[name="wf_desc[]"]' => 'desc',
                             'input.active-checkbox' => ['key' => 'active', 'type' => 'checkbox']]
                        ); ?>
                    </div></div>
                </div>

                <!-- ============ RESULTS & IMPACT ============ -->
                <div class="section-tabs__panel" data-panel="results">
                    <div class="admin-card"><div class="admin-card__body">
                        <?php svc_repeater_field('results', 'Results & Impact', '+ Add result',
                            '<div class="form-row"><label>Title</label><input type="text" name="res_title[]"></div>' .
                            '<div class="form-row"><label>Description</label><textarea name="res_desc[]" rows="2"></textarea></div>' .
                            '<div class="form-row" style="margin-bottom:0;"><label><input type="checkbox" class="active-checkbox" name="res_active[]" value="1" checked> Active</label></div>',
                            $repeater_data['results'],
                            ['input[name="res_title[]"]' => 'title', 'textarea[name="res_desc[]"]' => 'desc',
                             'input.active-checkbox' => ['key' => 'active', 'type' => 'checkbox']]
                        ); ?>
                    </div></div>
                </div>

                <!-- ============ WHAT WE DELIVERED ============ -->
                <div class="section-tabs__panel" data-panel="deliverables">
                    <div class="admin-card"><div class="admin-card__body">
                        <?php svc_repeater_field('deliverables', 'Deliverables', '+ Add deliverable',
                            '<div class="form-row"><label>Icon</label><input type="text" class="icon-input" name="del_icon[]"></div>' .
                            '<div class="form-row"><label>Title</label><input type="text" name="del_title[]"></div>' .
                            '<div class="form-row"><label>Description</label><textarea name="del_desc[]" rows="2"></textarea></div>' .
                            '<div class="form-row" style="margin-bottom:0;"><label><input type="checkbox" class="active-checkbox" name="del_active[]" value="1" checked> Active</label></div>',
                            $repeater_data['deliverables'],
                            ['input.icon-input' => ['key' => 'icon', 'type' => 'icon'], 'input[name="del_title[]"]' => 'title',
                             'textarea[name="del_desc[]"]' => 'desc', 'input.active-checkbox' => ['key' => 'active', 'type' => 'checkbox']]
                        ); ?>
                    </div></div>
                </div>

                <!-- ============ TECH STACK ============ -->
                <div class="section-tabs__panel" data-panel="tech">
                    <div class="admin-card"><div class="admin-card__body">
                        <?php svc_repeater_field('tech-stack', 'Technology Stack', '+ Add technology',
                            '<div class="form-row"><label>Icon</label><input type="text" class="icon-input" name="tech_icon[]"></div>' .
                            '<div class="form-row"><label>Name</label><input type="text" name="tech_name[]" placeholder="Apollo"></div>' .
                            '<div class="form-row"><label>Purpose</label><input type="text" name="tech_purpose[]" placeholder="Lead Database"></div>' .
                            '<div class="form-row" style="margin-bottom:0;"><label><input type="checkbox" class="active-checkbox" name="tech_active[]" value="1" checked> Active</label></div>',
                            $repeater_data['tech_stack'],
                            ['input.icon-input' => ['key' => 'icon', 'type' => 'icon'], 'input[name="tech_name[]"]' => 'name',
                             'input[name="tech_purpose[]"]' => 'purpose', 'input.active-checkbox' => ['key' => 'active', 'type' => 'checkbox']]
                        ); ?>
                    </div></div>
                </div>

                <!-- ============ WHY CHOOSE ============ -->
                <div class="section-tabs__panel" data-panel="why">
                    <div class="admin-card"><div class="admin-card__body">
                        <?php svc_repeater_field('why-cards', 'Why Choose Our Solution', '+ Add card',
                            '<div class="form-row"><label>Title</label><input type="text" name="why_title[]"></div>' .
                            '<div class="form-row"><label>Description</label><textarea name="why_desc[]" rows="2"></textarea></div>' .
                            '<div class="form-row" style="margin-bottom:0;"><label><input type="checkbox" class="active-checkbox" name="why_active[]" value="1" checked> Active</label></div>',
                            $repeater_data['why_cards'],
                            ['input[name="why_title[]"]' => 'title', 'textarea[name="why_desc[]"]' => 'desc',
                             'input.active-checkbox' => ['key' => 'active', 'type' => 'checkbox']]
                        ); ?>
                    </div></div>
                </div>

                <!-- ============ CLIENT RESPONSIBILITIES ============ -->
                <div class="section-tabs__panel" data-panel="responsibilities">
                    <div class="admin-card"><div class="admin-card__body">
                        <div class="form-row">
                            <label for="responsibilities_sub">Eyebrow / Subtitle <span class="text-muted">(optional)</span></label>
                            <input type="text" id="responsibilities_sub" name="responsibilities_sub" value="<?= attr($f['responsibilities_sub']) ?>">
                        </div>
                        <div class="form-row">
                            <label for="responsibilities_title">Title</label>
                            <input type="text" id="responsibilities_title" name="responsibilities_title" value="<?= attr($f['responsibilities_title']) ?>">
                        </div>
                        <div class="form-row" style="margin-bottom:0;">
                            <label for="responsibilities_text">Intro Line <span class="text-muted">(optional, e.g. "Ackuity.ai will provide:")</span></label>
                            <input type="text" id="responsibilities_text" name="responsibilities_text" value="<?= attr($f['responsibilities_text']) ?>">
                        </div>
                    </div></div>
                    <div class="admin-card"><div class="admin-card__body">
                        <?php svc_repeater_field('responsibilities', 'Responsibility Items', '+ Add item',
                            '<div class="form-row"><label>Text</label><input type="text" name="resp_text[]"></div>' .
                            '<div class="form-row" style="margin-bottom:0;"><label><input type="checkbox" class="active-checkbox" name="resp_active[]" value="1" checked> Active</label></div>',
                            $repeater_data['responsibilities'],
                            ['input[name="resp_text[]"]' => 'text', 'input.active-checkbox' => ['key' => 'active', 'type' => 'checkbox']]
                        ); ?>
                    </div></div>
                </div>

                <!-- ============ FUTURE ENHANCEMENTS ============ -->
                <div class="section-tabs__panel" data-panel="future">
                    <div class="admin-card"><div class="admin-card__body">
                        <div class="form-row">
                            <label for="future_sub">Eyebrow / Subtitle <span class="text-muted">(optional)</span></label>
                            <input type="text" id="future_sub" name="future_sub" value="<?= attr($f['future_sub']) ?>">
                        </div>
                        <div class="form-row">
                            <label for="future_title">Title</label>
                            <input type="text" id="future_title" name="future_title" value="<?= attr($f['future_title']) ?>">
                        </div>
                        <div class="form-row" style="margin-bottom:0;">
                            <label for="future_text">Intro Line <span class="text-muted">(optional)</span></label>
                            <input type="text" id="future_text" name="future_text" value="<?= attr($f['future_text']) ?>">
                        </div>
                    </div></div>
                    <div class="admin-card"><div class="admin-card__body">
                        <?php svc_repeater_field('future', 'Enhancement Items', '+ Add item',
                            '<div class="form-row"><label>Text</label><input type="text" name="future_text_item[]"></div>' .
                            '<div class="form-row" style="margin-bottom:0;"><label><input type="checkbox" class="active-checkbox" name="future_active[]" value="1" checked> Active</label></div>',
                            $repeater_data['future'],
                            ['input[name="future_text_item[]"]' => 'text', 'input.active-checkbox' => ['key' => 'active', 'type' => 'checkbox']]
                        ); ?>
                    </div></div>
                </div>

                <!-- ============ RELATED STORIES ============ -->
                <div class="section-tabs__panel" data-panel="related">
                    <div class="admin-card"><div class="admin-card__body">
                        <div class="form-row" style="margin-bottom:0;">
                            <label for="related_count">Number to Display <span class="text-muted">(auto-fills with recent published stories if fewer are picked below)</span></label>
                            <input type="number" id="related_count" name="related_count" min="0" max="12" value="<?= (int) $f['related_count'] ?>">
                        </div>
                    </div></div>
                    <div class="admin-card"><div class="admin-card__body">
                        <div class="repeater-row__title" style="margin-bottom:10px;">Manually Choose Stories <span class="text-muted">(optional — otherwise the most recent published stories are used)</span></div>
                        <?php if (empty($other_stories)): ?>
                            <p class="text-muted" style="font-size:13px;">No other published stories yet.</p>
                        <?php endif; ?>
                        <div style="max-height:320px;overflow-y:auto;">
                        <?php foreach ($other_stories as $os): ?>
                            <label style="display:block;padding:6px 0;">
                                <input type="checkbox" name="related_story_ids[]" value="<?= (int) $os['id'] ?>"
                                    <?= in_array((int) $os['id'], $selected_related_ids, true) ? 'checked' : '' ?>>
                                <?= e($os['title']) ?> <span class="text-muted">(<?= e($os['slug']) ?>)</span>
                            </label>
                        <?php endforeach; ?>
                        </div>
                    </div></div>
                </div>

                <!-- ============ FINAL CTA ============ -->
                <div class="section-tabs__panel" data-panel="final_cta">
                    <div class="admin-card"><div class="admin-card__body">
                        <p class="text-muted" style="font-size:13px;margin-top:0;">Pairs with the existing Contact Form band at the bottom of the page — the form itself, phone/email lines, and layout aren't editable here.</p>
                        <div class="form-row">
                            <label for="final_cta_sub">Subtitle</label>
                            <input type="text" id="final_cta_sub" name="final_cta_sub" value="<?= attr($f['final_cta_sub']) ?>">
                        </div>
                        <div class="form-row">
                            <label for="final_cta_title">Title</label>
                            <input type="text" id="final_cta_title" name="final_cta_title" value="<?= attr($f['final_cta_title']) ?>">
                        </div>
                        <div class="form-row" style="margin-bottom:0;">
                            <label for="final_cta_desc">Description</label>
                            <textarea id="final_cta_desc" name="final_cta_desc" rows="3"><?= e($f['final_cta_desc']) ?></textarea>
                        </div>
                    </div></div>
                </div>

                <!-- ============ SEO ============ -->
                <div class="section-tabs__panel" data-panel="seo">
                    <div class="admin-card"><div class="admin-card__body">
                        <div class="form-row">
                            <label for="meta_title">Meta Title <span class="text-muted">(optional — defaults to Title)</span></label>
                            <input type="text" id="meta_title" name="meta_title" maxlength="255" value="<?= attr($f['meta_title']) ?>">
                        </div>
                        <div class="form-row">
                            <label for="meta_description">Meta Description <span class="text-muted">(optional — defaults to Excerpt)</span></label>
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
                                <option value=""                 <?= $f['robots'] === ''                 ? 'selected' : '' ?>>Default</option>
                                <option value="index,follow"     <?= $f['robots'] === 'index,follow'     ? 'selected' : '' ?>>index, follow</option>
                                <option value="noindex,follow"   <?= $f['robots'] === 'noindex,follow'   ? 'selected' : '' ?>>noindex, follow</option>
                                <option value="index,nofollow"   <?= $f['robots'] === 'index,nofollow'   ? 'selected' : '' ?>>index, nofollow</option>
                                <option value="noindex,nofollow" <?= $f['robots'] === 'noindex,nofollow' ? 'selected' : '' ?>>noindex, nofollow</option>
                            </select>
                        </div>
                    </div></div>
                    <div class="admin-card"><div class="admin-card__body">
                        <div class="form-row">
                            <label>
                                JSON-LD Schemas <span class="text-muted">(Article/CaseStudy, BreadcrumbList, FAQPage, or any custom schema — add as many as you need)</span>
                                <a href="#" id="gen-story-schema" class="admin-btn admin-btn--ghost admin-btn--small" style="float:right;">+ Generate schema</a>
                            </label>
                        </div>
                        <?php svc_repeater_field('schemas', 'JSON-LD Schemas', '+ Add Another Schema',
                            '<div class="form-row"><label>Schema Name / Label <span class="text-muted">(optional)</span></label>' .
                            '<input type="text" name="schema_label[]" placeholder="e.g. Article Schema, Breadcrumb Schema"></div>' .
                            '<div class="form-row" style="margin-bottom:0;"><label>JSON-LD Code</label>' .
                            '<textarea name="schema_code[]" rows="6" class="json-textarea" placeholder="{&quot;@context&quot;:&quot;https://schema.org&quot;, ...}"></textarea></div>',
                            $repeater_data['json_ld_schemas'],
                            ['input[name="schema_label[]"]' => 'label', 'textarea[name="schema_code[]"]' => 'code']
                        ); ?>
                    </div></div>
                </div>

            </div>
        </div>

        <div class="admin-card" style="margin-top:18px;position:sticky;bottom:14px;">
            <div class="admin-card__body" style="display:flex;gap:10px;">
                <button type="submit" class="admin-btn"><?= $story ? 'Update Story' : 'Create Story' ?></button>
                <a href="<?= ADMIN_URL ?>/success-stories.php" class="admin-btn admin-btn--ghost">Cancel</a>
            </div>
        </div>
    </form>

    <!-- CKEditor 5 (rich content fields: body/challenge/solution) -->
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

        // ---- CKEditor on rich-content fields ----
        ['body_html', 'challenge_html', 'solution_html'].forEach(function (id) {
            var el = document.getElementById(id);
            if (!el) return;
            ClassicEditor.create(el, {
                toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList',
                          '|', 'blockQuote', '|', 'undo', 'redo', '|', 'sourceEditing']
            }).catch(function (err) { console.error(err); });
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
        var genBtn = document.getElementById('gen-story-schema');
        if (genBtn) {
            genBtn.addEventListener('click', function (e) {
                e.preventDefault();
                var SITE = <?= json_encode(defined('SITE_URL') ? SITE_URL : '') ?>;
                var slug = slugEl ? slugEl.value : '';
                var excerptEl = document.getElementById('excerpt');
                var data = {
                    '@context': 'https://schema.org',
                    '@type': 'Article',
                    'headline': titleEl ? titleEl.value : '',
                    'description': excerptEl ? excerptEl.value : '',
                    'url': SITE + '/success-stories/' + slug
                };
                var repeater = window.svcRepeaters && window.svcRepeaters['schemas'];
                if (repeater) {
                    repeater.addRow({ label: 'Success Story Schema', code: JSON.stringify(data, null, 2) });
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
// LIST view
// ===========================================================================
$status_filter = $_GET['status'] ?? '';
$where  = ''; $params = [];
if (in_array($status_filter, ['draft', 'published', 'archived'], true)) {
    $where = ' WHERE s.status = :st';
    $params[':st'] = $status_filter;
}

$stmt = $pdo->prepare(
    "SELECT s.id, s.slug, s.title, s.status, s.featured, s.sort_order, s.created_at, s.updated_at,
            c.name AS category_name
     FROM success_stories s
     LEFT JOIN success_story_categories c ON c.id = s.category_id
     $where
     ORDER BY s.sort_order ASC, COALESCE(s.published_at, s.updated_at) DESC LIMIT 200"
);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$admin_page_title = 'Success Stories';
$admin_active     = 'success-stories';
require __DIR__ . '/_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>Success Stories</h1>
        <div class="subtitle"><?= count($rows) ?> stor<?= count($rows) === 1 ? 'y' : 'ies' ?></div>
    </div>
    <a href="?action=new" class="admin-btn">New Story</a>
</div>

<div style="margin-bottom:14px;">
    <a href="?" class="<?= $status_filter === '' ? 'admin-btn admin-btn--small' : 'admin-btn admin-btn--ghost admin-btn--small' ?>">All</a>
    <a href="?status=published" class="<?= $status_filter === 'published' ? 'admin-btn admin-btn--small' : 'admin-btn admin-btn--ghost admin-btn--small' ?>">Published</a>
    <a href="?status=draft" class="<?= $status_filter === 'draft' ? 'admin-btn admin-btn--small' : 'admin-btn admin-btn--ghost admin-btn--small' ?>">Drafts</a>
</div>

<div class="admin-card">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Order</th>
                <th>Title</th>
                <th>Category</th>
                <th>Featured</th>
                <th>Status</th>
                <th>Updated</th>
                <th class="col-actions">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="7" class="empty-state">
                    <h3>No success stories yet</h3>
                    <a href="?action=new" class="admin-btn">New Story</a>
                </td></tr>
            <?php else: ?>
                <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= (int) $r['sort_order'] ?></td>
                    <td><a href="?action=edit&amp;id=<?= (int) $r['id'] ?>"><?= e($r['title']) ?></a></td>
                    <td><?= e($r['category_name'] ?? '') ?></td>
                    <td><?= $r['featured'] ? '★' : '' ?></td>
                    <td><span class="pill pill--<?= attr($r['status']) ?>"><?= e($r['status']) ?></span></td>
                    <td><?= e(substr((string) $r['updated_at'], 0, 16)) ?></td>
                    <td class="col-actions">
                        <?php if ($r['status'] === 'published'): ?>
                            <a href="<?= url('/success-stories/' . $r['slug']) ?>" target="_blank" rel="noopener" class="admin-btn admin-btn--ghost admin-btn--small">View</a>
                        <?php endif; ?>
                        <a href="?action=edit&amp;id=<?= (int) $r['id'] ?>" class="admin-btn admin-btn--small">Edit</a>
                        <form method="post" action="?action=delete" style="display:inline;"
                              onsubmit="return confirm('Delete this story permanently?');">
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
