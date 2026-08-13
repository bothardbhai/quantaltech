<?php
/**
 * Admin — Hire Master (full CRUD).
 *
 * Every dynamic section of pages/hire/_subhire.php is editable here,
 * organized into tabs (see .section-tabs in admin.css) — structurally a
 * direct clone of admin/services.php, renamed for `hire_pages` columns. See
 * db/migrations/2026-08-07-001-create-hire-pages-table.sql for the full
 * column list and _subhire.php's doc block for the section contract.
 */

require __DIR__ . '/bootstrap.php';

$pdo = db();
if (!$pdo) { die('Database connection failed.'); }

$action = $_GET['action'] ?? 'list';
$user   = auth_user();

// ===========================================================================
// Helpers
// ===========================================================================

function hire_slugify(string $t): string
{
    $t = strtolower(trim($t));
    if (function_exists('iconv')) {
        $c = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $t);
        if ($c !== false) $t = $c;
    }
    $t = preg_replace('/[^a-z0-9]+/', '-', $t) ?? '';
    return trim($t, '-') ?: 'hire-page';
}

function hire_unique_slug(PDO $pdo, string $base, ?int $excl = null): string
{
    $slug = $base; $i = 2;
    while (true) {
        $stmt = $pdo->prepare('SELECT id FROM hire_pages WHERE slug = :s' . ($excl ? ' AND id != :id' : ''));
        $p = [':s' => $slug];
        if ($excl) $p[':id'] = $excl;
        $stmt->execute($p);
        if (!$stmt->fetch()) return $slug;
        $slug = $base . '-' . $i++;
    }
}

// ============ Standard "sub / title / text" section header ============
function hire_section_header(string $prefix, array $f, string $titlePlaceholder = ''): void
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

// ===========================================================================
// DELETE
// ===========================================================================
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_or_die();
    $del_id = (int) ($_POST['id'] ?? 0);
    if ($del_id > 0) {
        delete_hire_page($pdo, $del_id);
        flash('success', 'Hire page deleted.');
    }
    header('Location: ' . ADMIN_URL . '/hire.php'); exit;
}

// ===========================================================================
// NEW / EDIT
// ===========================================================================
if ($action === 'new' || $action === 'edit') {
    $id        = (int) ($_GET['id'] ?? 0);
    $hire_page = null;

    if ($action === 'edit' && $id > 0) {
        $hire_page = get_hire_page($pdo, $id);
        if (!$hire_page) {
            flash('error', 'Hire page not found.');
            header('Location: ' . ADMIN_URL . '/hire.php'); exit;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_verify_or_die();

        $name       = trim((string) ($_POST['name'] ?? ''));
        $title      = trim((string) ($_POST['title'] ?? ''));
        $excerpt    = trim((string) ($_POST['excerpt'] ?? ''));
        $sort_order_raw = (string) ($_POST['sort_order'] ?? '1');

        $errors = [];
        if ($name === '') { $errors[] = 'Role Name is required.'; }
        if ($title === '') { $errors[] = 'Display Title is required.'; }
        if ($excerpt === '') { $errors[] = 'Short Description is required.'; }
        if (!ctype_digit($sort_order_raw) && !is_numeric($sort_order_raw)) {
            $errors[] = 'Display Order must be a number.';
        }

        // Multiple JSON-LD schema blocks — stored as one JSON array
        // [{label, code}, ...] in the schema_json column, same convention as
        // admin/services.php.
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
            foreach ($errors as $e) { flash('error', $e); }
            $hire_page = array_merge((array) $hire_page, $_POST, ['id' => $hire_page['id'] ?? null]);
        } else {
            $status = in_array($_POST['status'] ?? '', ['draft', 'published', 'archived'], true)
                ? $_POST['status'] : 'draft';
            $pub_at = $status === 'published'
                ? (($hire_page['published_at'] ?? null) ?: date('Y-m-d H:i:s'))
                : null;

            $slug_raw  = trim((string) ($_POST['slug'] ?? ''));
            $slug_base = $slug_raw !== '' ? hire_slugify($slug_raw) : hire_slugify($title);
            $slug      = hire_unique_slug($pdo, $slug_base, $hire_page['id'] ?? null);

            // Plain scalar fields — pass straight through, trimmed.
            $plain_fields = [
                'role_label', 'page_label', 'crumb', 'hero_tag', 'hero_desc',
                'expertise_sub', 'expertise_text', 'build_sub', 'build_text',
                'engagement_sub', 'engagement_text', 'why_sub', 'why_text',
                'industries_sub', 'industries_text',
                'cta_tag', 'cta_text', 'cs_sub', 'cs_text',
                'related_sub', 'related_text', 'blog_sub', 'blog_text', 'faq_intro',
                'final_cta_desc', 'final_cta_btn_text', 'final_cta_btn_url',
                'meta_title', 'meta_description', 'meta_keywords', 'og_image', 'canonical', 'robots',
            ];
            // Fields that allow trusted inline HTML (CKEditor) — sanitized +
            // unwrapped rather than escaped.
            $html_fields = [
                'hero_title_html', 'expertise_title_html', 'build_title_html',
                'engagement_title_html', 'why_title_html', 'industries_title_html',
                'cta_title_html', 'cs_title_html', 'related_title_html', 'blog_title_html',
                'final_cta_title_html',
            ];

            $data = [
                'slug' => $slug,
                'name' => $name,
                'title' => $title,
                'excerpt' => $excerpt,
                'icon_class' => trim((string) ($_POST['icon_class'] ?? '')),
                'sort_order' => max(1, (int) $sort_order_raw),
                'featured_image' => trim((string) ($_POST['featured_image'] ?? '')),
                'featured_alt' => trim((string) ($_POST['featured_alt'] ?? '')),
                'schema_json' => $schema_json,
                'status' => $status,
                'published_at' => $pub_at,
                'display_on_hub' => isset($_POST['display_on_hub']) ? 1 : 0,
                'author_id' => $user['id'] ?? null,
            ];
            foreach ($plain_fields as $f) {
                $data[$f] = trim((string) ($_POST[$f] ?? ''));
            }
            foreach ($html_fields as $f) {
                $data[$f] = strip_wrapping_p(sanitize_html_fragment((string) ($_POST[$f] ?? '')));
            }

            // Object repeaters
            $data['hero_features_json'] = json_encode(svc_build_repeater($_POST,
                ['icon' => 'hf_icon', 'text' => 'hf_text']));
            $data['impact_stats_json'] = json_encode(svc_build_repeater($_POST,
                ['number' => 'impact_number', 'title' => 'impact_title']));
            $data['expertise_cards_json'] = json_encode(svc_build_repeater($_POST,
                ['icon' => 'exp_icon', 'title' => 'exp_title', 'desc' => 'exp_desc']));
            $data['build_cards_json'] = json_encode(svc_build_repeater($_POST,
                ['number' => 'build_number', 'title' => 'build_title', 'desc' => 'build_desc']));
            $data['engagement_models_json'] = json_encode(svc_build_repeater($_POST,
                ['icon' => 'eng_icon', 'title' => 'eng_title', 'desc' => 'eng_desc'],
                [], ['featured' => 'eng_featured']));
            $data['why_cards_json'] = json_encode(svc_build_repeater($_POST,
                ['title' => 'why_card_title', 'desc' => 'why_card_desc']));
            $data['industries_json'] = json_encode(svc_build_repeater($_POST,
                ['icon' => 'industry_icon', 'title' => 'industry_title', 'desc' => 'industry_desc']));
            $data['faqs_json'] = json_encode(svc_build_repeater($_POST,
                ['question' => 'faq_question', 'answer' => 'faq_answer']));

            // Pickers (store IDs only; frontend resolves to URL/content)
            $data['related_hire_ids_json'] = json_encode(array_values(array_filter(array_map('intval', $_POST['related_hire_ids'] ?? []))));
            $data['related_service_ids_json'] = json_encode(array_values(array_filter(array_map('intval', $_POST['related_service_ids'] ?? []))));
            $data['blog_post_ids_json'] = json_encode(array_values(array_filter(array_map('intval', $_POST['blog_post_ids'] ?? []))));

            if (!empty($hire_page['id'])) {
                $data['id'] = $hire_page['id'];
            }
            $result = save_hire_page($pdo, $data);

            if ($result['success']) {
                flash('success', $hire_page && !empty($hire_page['id']) ? 'Hire page updated.' : 'Hire page created.');
                header('Location: ' . ADMIN_URL . '/hire.php?action=edit&id=' . $result['id']); exit;
            }
            flash('error', 'Could not save hire page: ' . ($result['error'] ?? 'unknown error'));
            $hire_page = array_merge((array) $hire_page, $_POST, ['id' => $hire_page['id'] ?? null]);
        }
    }

    // ---- Defaults for the form ----
    $f = array_merge([
        'id' => null, 'slug' => '', 'name' => '', 'title' => '', 'excerpt' => '', 'role_label' => '',
        'sort_order' => 1, 'icon_class' => '', 'featured_image' => '', 'featured_alt' => '',
        'meta_title' => '', 'meta_description' => '', 'meta_keywords' => '', 'og_image' => '',
        'canonical' => '', 'robots' => '', 'schema_json' => '', 'status' => 'draft',
        'display_on_hub' => 1, 'published_at' => null,
        'page_label' => '', 'crumb' => '', 'hero_tag' => '', 'hero_title_html' => '', 'hero_desc' => '',
        'expertise_sub' => '', 'expertise_title_html' => '', 'expertise_text' => '',
        'build_sub' => '', 'build_title_html' => '', 'build_text' => '',
        'engagement_sub' => '', 'engagement_title_html' => '', 'engagement_text' => '',
        'why_sub' => '', 'why_title_html' => '', 'why_text' => '',
        'industries_sub' => '', 'industries_title_html' => '', 'industries_text' => '',
        'cta_tag' => '', 'cta_title_html' => '', 'cta_text' => '',
        'cs_sub' => '', 'cs_title_html' => '', 'cs_text' => '',
        'related_sub' => '', 'related_title_html' => '', 'related_text' => '',
        'blog_sub' => '', 'blog_title_html' => '', 'blog_text' => '', 'faq_intro' => '',
        'final_cta_title_html' => '', 'final_cta_desc' => '', 'final_cta_btn_text' => '', 'final_cta_btn_url' => '',
    ], (array) $hire_page);

    // JSON-backed data for the JS repeaters (edit mode) — empty arrays for "new"
    $repeater_data = [
        'hero_features' => svc_json_decode($hire_page['hero_features_json'] ?? null),
        'impact_stats' => svc_json_decode($hire_page['impact_stats_json'] ?? null),
        'expertise_cards' => svc_json_decode($hire_page['expertise_cards_json'] ?? null),
        'build_cards' => svc_json_decode($hire_page['build_cards_json'] ?? null),
        'engagement_models' => svc_json_decode($hire_page['engagement_models_json'] ?? null),
        'why_cards' => svc_json_decode($hire_page['why_cards_json'] ?? null),
        'industries' => svc_json_decode($hire_page['industries_json'] ?? null),
        'faqs' => svc_json_decode($hire_page['faqs_json'] ?? null),
        'json_ld_schemas' => svc_normalize_schemas($hire_page['schema_json'] ?? null),
    ];
    $selected_related_hire_ids = array_map('intval', svc_json_decode($hire_page['related_hire_ids_json'] ?? null));
    $selected_related_service_ids = array_map('intval', svc_json_decode($hire_page['related_service_ids_json'] ?? null));
    $selected_blog_ids = array_map('intval', svc_json_decode($hire_page['blog_post_ids_json'] ?? null));

    // Pickers: other published hire pages, published services, published blog posts
    $other_hire_pages = array_filter(
        get_hire_pages($pdo, ['status' => 'published']),
        static fn($h) => (int) $h['id'] !== (int) ($f['id'] ?? 0)
    );
    $other_services = get_services($pdo, ['status' => 'published']);
    $blog_posts_stmt = $pdo->query("SELECT id, title FROM posts WHERE status = 'published' ORDER BY COALESCE(published_at, updated_at) DESC LIMIT 300");
    $all_blog_posts = $blog_posts_stmt->fetchAll();

    $admin_page_title = $hire_page ? 'Edit Hire Page' : 'New Hire Page';
    $admin_active     = 'hire';
    require __DIR__ . '/_header.php';
    ?>

    <script src="<?= ADMIN_URL ?>/assets/js/repeater.js"></script>
    <script src="<?= ADMIN_URL ?>/assets/js/fa-icons.js"></script>
    <script src="<?= ADMIN_URL ?>/assets/js/icon-picker.js"></script>

    <div class="admin-page-header">
        <div>
            <h1><?= $hire_page ? 'Edit Hire Page' : 'New Hire Page' ?></h1>
            <?php if ($hire_page): ?>
                <div class="subtitle">
                    Slug: <code class="text-mono"><?= e($f['slug']) ?></code>
                    <?php if ($f['status'] === 'published'): ?>
                        &middot; <a href="<?= url('/hire/' . $f['slug']) ?>" target="_blank" rel="noopener">View live &rarr;</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <a href="<?= ADMIN_URL ?>/hire.php" class="admin-btn admin-btn--ghost">Back to list</a>
    </div>

    <form method="post" class="admin-form" id="hire-form">
        <?= csrf_field() ?>

        <div class="section-tabs">
            <div class="section-tabs__nav" id="section-tabs-nav">
                <?php
                $tabs = [
                    'core' => 'Core', 'hero' => 'Hero', 'impact' => 'Impact Stats',
                    'expertise' => 'Expertise', 'build' => 'What They Build',
                    'engagement' => 'Engagement Models', 'why' => 'Why Hire', 'industries' => 'Industries',
                    'cta' => 'Mid CTA', 'final_cta' => 'Final CTA', 'cases' => 'Success Stories',
                    'related' => 'Related', 'blog' => 'Knowledge Hub', 'faq' => 'FAQ', 'seo' => 'SEO',
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
                            <label for="name">Role Name *</label>
                            <input type="text" id="name" name="name" required value="<?= attr($f['name']) ?>" placeholder="e.g. Python Developer">
                        </div>
                        <div class="form-row">
                            <label for="title">Display Title *</label>
                            <input type="text" id="title" name="title" required value="<?= attr($f['title']) ?>">
                        </div>
                        <div class="form-row">
                            <label for="role_label">Card / Tag Label <span class="text-muted">(optional — shown on hub cards)</span></label>
                            <input type="text" id="role_label" name="role_label" value="<?= attr($f['role_label']) ?>">
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                            <div class="form-row">
                                <label for="slug">Slug</label>
                                <input type="text" id="slug" name="slug" value="<?= attr($f['slug']) ?>" placeholder="auto-generated">
                                <div class="help">Live at <code class="text-mono">/hire/<?= e($f['slug'] ?: '{slug}') ?></code></div>
                            </div>
                            <div class="form-row">
                                <label for="sort_order">Display Order</label>
                                <input type="number" id="sort_order" name="sort_order" min="1" value="<?= (int) $f['sort_order'] ?>">
                            </div>
                        </div>
                        <div class="form-row">
                            <label for="excerpt">Short Description *</label>
                            <textarea id="excerpt" name="excerpt" rows="2" required maxlength="500"><?= e($f['excerpt']) ?></textarea>
                            <div class="help">Shown on the /hire-ai-engineers hub card.</div>
                        </div>
                        <div class="form-row">
                            <label for="icon_class">Icon Class</label>
                            <input type="text" id="icon_class" name="icon_class" value="<?= attr($f['icon_class']) ?>" placeholder="fas fa-brain">
                        </div>
                        <div class="form-row">
                            <label for="featured_image">Featured Image</label>
                            <input type="text" id="featured_image" name="featured_image" value="<?= attr($f['featured_image']) ?>" placeholder="/uploads/hire/...">
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
                                <input type="checkbox" name="display_on_hub" <?= $f['display_on_hub'] ? 'checked' : '' ?>>
                                Show as a card on the /hire-ai-engineers hub
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
                            <label for="crumb">Breadcrumb Label <span class="text-muted">(optional — defaults to Role Name)</span></label>
                            <input type="text" id="crumb" name="crumb" value="<?= attr($f['crumb']) ?>">
                        </div>
                        <div class="form-row">
                            <label for="hero_tag">Hero Badge Text</label>
                            <input type="text" id="hero_tag" name="hero_tag" value="<?= attr($f['hero_tag']) ?>" placeholder="e.g. Trusted by Global Businesses">
                        </div>
                        <div class="form-row">
                            <label for="hero_title_html">Hero Title <span class="text-muted">(HTML allowed: span, strong, em, br)</span></label>
                            <textarea id="hero_title_html" name="hero_title_html" rows="3" placeholder="Hire Experienced &lt;span&gt;Python Developers&lt;/span&gt;"><?= e($f['hero_title_html']) ?></textarea>
                        </div>
                        <div class="form-row">
                            <label for="hero_desc">Hero Description</label>
                            <textarea id="hero_desc" name="hero_desc" rows="3"><?= e($f['hero_desc']) ?></textarea>
                        </div>
                    </div></div>
                    <div class="admin-card"><div class="admin-card__body">
                        <?php svc_repeater_field(
                            'hero-features', 'Hero Feature Bullets', '+ Add bullet',
                            '<div class="form-row"><label>Icon</label><input type="text" class="icon-input" name="hf_icon[]" placeholder="bi bi-check-lg"></div>' .
                            '<div class="form-row" style="margin-bottom:0;"><label>Text</label><input type="text" name="hf_text[]" placeholder="Senior AI Engineers"></div>',
                            $repeater_data['hero_features'],
                            ['input.icon-input' => ['key' => 'icon', 'type' => 'icon'], 'input[name="hf_text[]"]' => 'text']
                        ); ?>
                    </div></div>
                </div>

                <!-- ============ IMPACT STATS ============ -->
                <div class="section-tabs__panel" data-panel="impact">
                    <div class="admin-card"><div class="admin-card__body">
                        <?php svc_repeater_field(
                            'impact-stats', 'Impact Stats', '+ Add stat',
                            '<div class="form-row"><label>Number</label><input type="text" name="impact_number[]" placeholder="40+"></div>' .
                            '<div class="form-row" style="margin-bottom:0;"><label>Title</label><input type="text" name="impact_title[]" placeholder="Production AI"></div>',
                            $repeater_data['impact_stats'],
                            ['input[name="impact_number[]"]' => 'number', 'input[name="impact_title[]"]' => 'title']
                        ); ?>
                    </div></div>
                </div>

                <!-- ============ EXPERTISE ============ -->
                <div class="section-tabs__panel" data-panel="expertise">
                    <div class="admin-card"><div class="admin-card__body"><?php hire_section_header('expertise', $f, 'Core Capabilities of <span>Our AI Engineers</span>'); ?></div></div>
                    <div class="admin-card"><div class="admin-card__body">
                        <?php svc_repeater_field('expertise-cards', 'Cards', '+ Add card',
                            '<div class="form-row"><label>Icon</label><input type="text" class="icon-input" name="exp_icon[]"></div>' .
                            '<div class="form-row"><label>Title</label><input type="text" name="exp_title[]"></div>' .
                            '<div class="form-row" style="margin-bottom:0;"><label>Description</label><textarea name="exp_desc[]" rows="2"></textarea></div>',
                            $repeater_data['expertise_cards'],
                            ['input.icon-input' => ['key' => 'icon', 'type' => 'icon'], 'input[name="exp_title[]"]' => 'title', 'textarea[name="exp_desc[]"]' => 'desc']
                        ); ?>
                    </div></div>
                </div>

                <!-- ============ WHAT THEY BUILD ============ -->
                <div class="section-tabs__panel" data-panel="build">
                    <div class="admin-card"><div class="admin-card__body"><?php hire_section_header('build', $f, 'What You Can Build When You Hire <span>AI Developers</span>'); ?></div></div>
                    <div class="admin-card"><div class="admin-card__body">
                        <?php svc_repeater_field('build-cards', 'Cards', '+ Add card',
                            '<div class="form-row"><label>Number <span class="text-muted">(optional — auto-numbered if blank)</span></label><input type="text" name="build_number[]" placeholder="01"></div>' .
                            '<div class="form-row"><label>Title</label><input type="text" name="build_title[]"></div>' .
                            '<div class="form-row" style="margin-bottom:0;"><label>Description</label><textarea name="build_desc[]" rows="2"></textarea></div>',
                            $repeater_data['build_cards'],
                            ['input[name="build_number[]"]' => 'number', 'input[name="build_title[]"]' => 'title', 'textarea[name="build_desc[]"]' => 'desc']
                        ); ?>
                    </div></div>
                </div>

                <!-- ============ ENGAGEMENT MODELS ============ -->
                <div class="section-tabs__panel" data-panel="engagement">
                    <div class="admin-card"><div class="admin-card__body"><?php hire_section_header('engagement', $f, 'The Right Engagement Model <span>for Your Business</span>'); ?></div></div>
                    <div class="admin-card"><div class="admin-card__body">
                        <?php svc_repeater_field('engagement-models', 'Models', '+ Add model',
                            '<div class="form-row"><label>Icon</label><input type="text" class="icon-input" name="eng_icon[]"></div>' .
                            '<div class="form-row"><label>Title</label><input type="text" name="eng_title[]"></div>' .
                            '<div class="form-row"><label>Description</label><textarea name="eng_desc[]" rows="2"></textarea></div>' .
                            '<div class="form-row" style="margin-bottom:0;"><label><input type="checkbox" class="featured-checkbox" name="eng_featured[]" value="1"> Featured (highlighted)</label></div>',
                            $repeater_data['engagement_models'],
                            ['input.icon-input' => ['key' => 'icon', 'type' => 'icon'], 'input[name="eng_title[]"]' => 'title',
                             'textarea[name="eng_desc[]"]' => 'desc', 'input.featured-checkbox' => ['key' => 'featured', 'type' => 'checkbox']]
                        ); ?>
                    </div></div>
                </div>

                <!-- ============ WHY HIRE ============ -->
                <div class="section-tabs__panel" data-panel="why">
                    <div class="admin-card"><div class="admin-card__body"><?php hire_section_header('why', $f, 'Why Companies Hire Remote <span>AI Engineers From Us</span>'); ?></div></div>
                    <div class="admin-card"><div class="admin-card__body">
                        <?php svc_repeater_field('why-cards', 'Cards', '+ Add card',
                            '<div class="form-row"><label>Title</label><input type="text" name="why_card_title[]"></div>' .
                            '<div class="form-row" style="margin-bottom:0;"><label>Description</label><textarea name="why_card_desc[]" rows="2"></textarea></div>',
                            $repeater_data['why_cards'],
                            ['input[name="why_card_title[]"]' => 'title', 'textarea[name="why_card_desc[]"]' => 'desc']
                        ); ?>
                    </div></div>
                </div>

                <!-- ============ INDUSTRIES ============ -->
                <div class="section-tabs__panel" data-panel="industries">
                    <div class="admin-card"><div class="admin-card__body"><?php hire_section_header('industries', $f, 'Our AI Engineering Solutions <span>for Every Industry</span>'); ?></div></div>
                    <div class="admin-card"><div class="admin-card__body">
                        <?php svc_repeater_field('industries', 'Industries', '+ Add industry',
                            '<div class="form-row"><label>Icon</label><input type="text" class="icon-input" name="industry_icon[]"></div>' .
                            '<div class="form-row"><label>Title</label><input type="text" name="industry_title[]" placeholder="Retail &amp; E-commerce"></div>' .
                            '<div class="form-row" style="margin-bottom:0;"><label>Description</label><textarea name="industry_desc[]" rows="2"></textarea></div>',
                            $repeater_data['industries'],
                            ['input.icon-input' => ['key' => 'icon', 'type' => 'icon'], 'input[name="industry_title[]"]' => 'title', 'textarea[name="industry_desc[]"]' => 'desc']
                        ); ?>
                    </div></div>
                </div>

                <!-- ============ MID CTA ============ -->
                <div class="section-tabs__panel" data-panel="cta">
                    <div class="admin-card"><div class="admin-card__body">
                        <div class="form-row">
                            <label for="cta_tag">Tag</label>
                            <input type="text" id="cta_tag" name="cta_tag" value="<?= attr($f['cta_tag']) ?>" placeholder="Ready to Scale?">
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
                            <textarea id="final_cta_title_html" name="final_cta_title_html" rows="2" placeholder="Let's Build Your &lt;span&gt;AI Team&lt;/span&gt;"><?= e($f['final_cta_title_html']) ?></textarea>
                        </div>
                        <div class="form-row">
                            <label for="final_cta_desc">Description</label>
                            <textarea id="final_cta_desc" name="final_cta_desc" rows="3"><?= e($f['final_cta_desc']) ?></textarea>
                        </div>
                        <div class="form-row">
                            <label for="final_cta_btn_text">Button Text</label>
                            <input type="text" id="final_cta_btn_text" name="final_cta_btn_text" value="<?= attr($f['final_cta_btn_text']) ?>" placeholder="View Case Studies">
                        </div>
                        <div class="form-row" style="margin-bottom:0;">
                            <label for="final_cta_btn_url">Button URL <span class="text-muted">(optional — defaults to /case-studies)</span></label>
                            <input type="text" id="final_cta_btn_url" name="final_cta_btn_url" value="<?= attr($f['final_cta_btn_url']) ?>" placeholder="/case-studies">
                        </div>
                    </div></div>
                </div>

                <!-- ============ SUCCESS STORIES ============ -->
                <div class="section-tabs__panel" data-panel="cases">
                    <div class="admin-card"><div class="admin-card__body"><?php hire_section_header('cs', $f, 'AI Teams That <span>Delivered</span>'); ?></div></div>
                    <div class="admin-card"><div class="admin-card__body">
                        <p class="text-muted" style="font-size:13px;margin:0;">
                            This section now shows the 4 most recently published entries from the
                            <a href="<?= ADMIN_URL ?>/success-stories.php" target="_blank" rel="noopener">Success Stories</a> master automatically —
                            manually picking individual case studies here has been removed. Add or edit Success Stories there to control what appears.
                        </p>
                    </div></div>
                </div>

                <!-- ============ RELATED ============ -->
                <div class="section-tabs__panel" data-panel="related">
                    <div class="admin-card"><div class="admin-card__body">
                        <?php hire_section_header('related', $f, 'Related <span>Roles &amp; Services</span>'); ?>
                    </div></div>
                    <div class="admin-card"><div class="admin-card__body">
                        <div class="repeater-row__title" style="margin-bottom:10px;">Choose Other Hire Pages</div>
                        <?php if (empty($other_hire_pages)): ?>
                            <p class="text-muted" style="font-size:13px;">No other published hire pages yet.</p>
                        <?php endif; ?>
                        <?php foreach ($other_hire_pages as $oh): ?>
                            <label style="display:block;padding:6px 0;">
                                <input type="checkbox" name="related_hire_ids[]" value="<?= (int) $oh['id'] ?>"
                                    <?= in_array((int) $oh['id'], $selected_related_hire_ids, true) ? 'checked' : '' ?>>
                                <?= e($oh['name']) ?> <span class="text-muted">(<?= e($oh['slug']) ?>)</span>
                            </label>
                        <?php endforeach; ?>
                    </div></div>
                    <div class="admin-card"><div class="admin-card__body">
                        <div class="repeater-row__title" style="margin-bottom:10px;">Choose Related Services <span class="text-muted">(the URL is generated automatically)</span></div>
                        <?php if (empty($other_services)): ?>
                            <p class="text-muted" style="font-size:13px;">No published services yet.</p>
                        <?php endif; ?>
                        <?php foreach ($other_services as $os): ?>
                            <label style="display:block;padding:6px 0;">
                                <input type="checkbox" name="related_service_ids[]" value="<?= (int) $os['id'] ?>"
                                    <?= in_array((int) $os['id'], $selected_related_service_ids, true) ? 'checked' : '' ?>>
                                <?= e($os['name']) ?> <span class="text-muted">(<?= e($os['slug']) ?>)</span>
                            </label>
                        <?php endforeach; ?>
                    </div></div>
                </div>

                <!-- ============ KNOWLEDGE HUB ============ -->
                <div class="section-tabs__panel" data-panel="blog">
                    <div class="admin-card"><div class="admin-card__body"><?php hire_section_header('blog', $f, 'From Our <span>Knowledge Hub</span>'); ?></div></div>
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
                    </div></div>
                    <div class="admin-card"><div class="admin-card__body">
                        <div class="form-row">
                            <label>
                                JSON-LD Schemas <span class="text-muted">(WebPage, Breadcrumb, FAQ, Organization, or any custom schema — add as many as you need)</span>
                                <a href="#" id="gen-hire-schema" class="admin-btn admin-btn--ghost admin-btn--small" style="float:right;">+ Generate schema</a>
                            </label>
                        </div>
                        <?php svc_repeater_field('schemas', 'JSON-LD Schemas', '+ Add Another Schema',
                            '<div class="form-row"><label>Schema Name / Label <span class="text-muted">(optional)</span></label>' .
                            '<input type="text" name="schema_label[]" placeholder="e.g. FAQ Schema, Breadcrumb Schema"></div>' .
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
                <button type="submit" class="admin-btn"><?= $hire_page ? 'Update Hire Page' : 'Create Hire Page' ?></button>
                <a href="<?= ADMIN_URL ?>/hire.php" class="admin-btn admin-btn--ghost">Cancel</a>
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
        var genBtn = document.getElementById('gen-hire-schema');
        if (genBtn) {
            genBtn.addEventListener('click', function (e) {
                e.preventDefault();
                var SITE = <?= json_encode(defined('SITE_URL') ? SITE_URL : '') ?>;
                var slug = slugEl ? slugEl.value : '';
                var data = {
                    '@context': 'https://schema.org',
                    '@type': 'WebPage',
                    'name': titleEl ? titleEl.value : '',
                    'description': document.getElementById('excerpt') ? document.getElementById('excerpt').value : '',
                    'url': SITE + '/hire/' + slug
                };
                var repeater = window.svcRepeaters && window.svcRepeaters['schemas'];
                if (repeater) {
                    repeater.addRow({ label: 'Hire Page Schema', code: JSON.stringify(data, null, 2) });
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
$rows = $pdo->query(
    'SELECT id, slug, name, title, sort_order, status, display_on_hub, updated_at
     FROM hire_pages ORDER BY sort_order ASC, id ASC LIMIT 200'
)->fetchAll();

$admin_page_title = 'Hire Master';
$admin_active     = 'hire';
require __DIR__ . '/_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>Hire Master</h1>
        <div class="subtitle"><?= count($rows) ?> hire page<?= count($rows) === 1 ? '' : 's' ?></div>
    </div>
    <a href="?action=new" class="admin-btn">New Hire Page</a>
</div>

<div class="admin-card">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Order</th>
                <th>Name</th>
                <th>Title</th>
                <th>Status</th>
                <th>Hub</th>
                <th class="col-actions">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="6" class="empty-state">
                    <h3>No hire pages yet</h3>
                    <a href="?action=new" class="admin-btn">New Hire Page</a>
                </td></tr>
            <?php else: ?>
                <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= (int) $r['sort_order'] ?></td>
                    <td><a href="?action=edit&amp;id=<?= (int) $r['id'] ?>"><?= e($r['name']) ?></a></td>
                    <td><?= e($r['title']) ?></td>
                    <td><span class="pill pill--<?= attr($r['status']) ?>"><?= e($r['status']) ?></span></td>
                    <td><?= $r['display_on_hub'] ? '✓' : '' ?></td>
                    <td class="col-actions">
                        <?php if ($r['status'] === 'published'): ?>
                            <a href="<?= url('/hire/' . $r['slug']) ?>" target="_blank" rel="noopener" class="admin-btn admin-btn--ghost admin-btn--small">View</a>
                        <?php endif; ?>
                        <a href="?action=edit&amp;id=<?= (int) $r['id'] ?>" class="admin-btn admin-btn--small">Edit</a>
                        <form method="post" action="?action=delete" style="display:inline;"
                              onsubmit="return confirm('Delete this hire page permanently?');">
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
