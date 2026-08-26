<?php
/**
 * Admin — Podcast episodes (full CRUD).
 *
 * Structurally the same tabbed-form + repeater pattern as
 * admin/success-stories.php / admin/hire.php / admin/services.php. See
 * db/migrations/2026-08-26-001-create-podcasts-table.sql for the full
 * column list. Key Takeaways and Episode Highlights are repeaters so an
 * admin can add any number of items per episode.
 */

require __DIR__ . '/bootstrap.php';

$pdo = db();
if (!$pdo) { die('Database connection failed.'); }

$action = $_GET['action'] ?? 'list';
$user   = auth_user();

// ===========================================================================
// Helpers
// ===========================================================================

function podcast_slugify(string $t): string
{
    $t = strtolower(trim($t));
    if (function_exists('iconv')) {
        $c = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $t);
        if ($c !== false) $t = $c;
    }
    $t = preg_replace('/[^a-z0-9]+/', '-', $t) ?? '';
    return trim($t, '-') ?: 'episode';
}

function podcast_unique_slug(PDO $pdo, string $base, ?int $excl = null): string
{
    $slug = $base; $i = 2;
    while (true) {
        $stmt = $pdo->prepare('SELECT id FROM podcasts WHERE slug = :s' . ($excl ? ' AND id != :id' : ''));
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
        $pdo->prepare('DELETE FROM podcasts WHERE id = :id')->execute([':id' => $del_id]);
        flash('success', 'Podcast episode deleted.');
    }
    header('Location: ' . ADMIN_URL . '/podcasts.php'); exit;
}

// ===========================================================================
// NEW / EDIT
// ===========================================================================
if ($action === 'new' || $action === 'edit') {
    $id      = (int) ($_GET['id'] ?? 0);
    $episode = null;

    if ($action === 'edit' && $id > 0) {
        $s = $pdo->prepare('SELECT * FROM podcasts WHERE id = :id');
        $s->execute([':id' => $id]);
        $episode = $s->fetch();
        if (!$episode) {
            flash('error', 'Podcast episode not found.');
            header('Location: ' . ADMIN_URL . '/podcasts.php'); exit;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_verify_or_die();

        $title      = trim((string) ($_POST['title'] ?? ''));
        $guest_name = trim((string) ($_POST['guest_name'] ?? ''));

        $errors = [];
        if ($title      === '') $errors[] = 'Title is required.';
        if ($guest_name === '') $errors[] = 'Guest name is required.';

        // JSON-LD schema blocks
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
            $episode = array_merge((array) $episode, $_POST, ['id' => $episode['id'] ?? null]);
        } else {
            $slug_raw  = trim((string) ($_POST['slug'] ?? ''));
            $slug_base = $slug_raw !== '' ? podcast_slugify($slug_raw) : podcast_slugify($title);
            $slug      = podcast_unique_slug($pdo, $slug_base, $episode['id'] ?? null);
            $status    = in_array($_POST['status'] ?? '', ['draft', 'published', 'archived'], true)
                            ? $_POST['status'] : 'draft';
            $pub_at    = $status === 'published'
                            ? (($episode['published_at'] ?? null) ?: date('Y-m-d H:i:s'))
                            : null;
            $is_featured = isset($_POST['featured']) ? 1 : 0;
            $publish_date = trim((string) ($_POST['publish_date'] ?? ''));

            $plain_fields = [
                'guest_designation', 'guest_company', 'guest_company_logo',
                'youtube_url', 'thumbnail_override',
                'meta_title', 'meta_description', 'meta_keywords', 'og_image', 'canonical', 'robots',
            ];

            $cols = [
                'slug'               => $slug,
                'title'              => $title,
                'guest_name'         => $guest_name,
                'short_description'  => trim((string) ($_POST['short_description'] ?? '')),
                'description_html'   => sanitize_html_fragment((string) ($_POST['description_html'] ?? '')),
                'publish_date'       => $publish_date !== '' ? $publish_date : null,
                'schema_json'        => $schema_json,
                'status'             => $status,
                'published_at'       => $pub_at,
                'featured'           => $is_featured,
                'author_id'          => $user['id'] ?? null,
            ];
            foreach ($plain_fields as $fld) {
                $cols[$fld] = trim((string) ($_POST[$fld] ?? ''));
            }

            // Repeaters
            $cols['key_takeaways_json'] = json_encode(svc_build_repeater($_POST,
                ['text' => 'takeaway_text'], [], ['active' => 'takeaway_active']));
            $cols['highlights_json'] = json_encode(svc_build_repeater($_POST,
                ['text' => 'highlight_text'], [], ['active' => 'highlight_active']));

            $bind = [];
            foreach ($cols as $k => $v) $bind[":$k"] = $v;

            if ($is_featured) {
                // Only one episode can be the featured episode at a time.
                $pdo->exec('UPDATE podcasts SET featured = 0' . (isset($episode['id']) ? ' WHERE id != ' . (int) $episode['id'] : ''));
            }

            if (isset($episode['id'])) {
                $sets = implode(', ', array_map(fn($k) => "`$k` = :$k", array_keys($cols)));
                $stmt = $pdo->prepare("UPDATE podcasts SET $sets WHERE id = :where_id");
                $bind[':where_id'] = $episode['id'];
                $stmt->execute($bind);
                flash('success', 'Podcast episode updated.');
                header('Location: ' . ADMIN_URL . '/podcasts.php?action=edit&id=' . $episode['id']); exit;
            } else {
                $keys = implode(', ', array_map(fn($k) => "`$k`", array_keys($cols)));
                $phs  = implode(', ', array_map(fn($k) => ":$k", array_keys($cols)));
                $stmt = $pdo->prepare("INSERT INTO podcasts ($keys) VALUES ($phs)");
                $stmt->execute($bind);
                $newId = (int) $pdo->lastInsertId();
                flash('success', 'Podcast episode created.');
                header('Location: ' . ADMIN_URL . '/podcasts.php?action=edit&id=' . $newId); exit;
            }
        }
    }

    // ---- Defaults for the form ----
    $f = array_merge([
        'id' => null, 'slug' => '', 'title' => '',
        'guest_name' => '', 'guest_designation' => '', 'guest_company' => '', 'guest_company_logo' => '',
        'youtube_url' => '', 'thumbnail_override' => '',
        'short_description' => '', 'description_html' => '',
        'publish_date' => '', 'status' => 'draft', 'featured' => 0, 'published_at' => null,
        'meta_title' => '', 'meta_description' => '', 'meta_keywords' => '',
        'og_image' => '', 'canonical' => '', 'robots' => '', 'schema_json' => '',
    ], (array) $episode);

    $repeater_data = [
        'key_takeaways'   => svc_json_decode($episode['key_takeaways_json'] ?? null),
        'highlights'      => svc_json_decode($episode['highlights_json'] ?? null),
        'json_ld_schemas' => svc_normalize_schemas($episode['schema_json'] ?? null),
    ];

    $admin_page_title = $episode ? 'Edit Podcast Episode' : 'New Podcast Episode';
    $admin_active     = 'podcasts';
    require __DIR__ . '/_header.php';
    ?>

    <script src="<?= ADMIN_URL ?>/assets/js/repeater.js"></script>
    <script src="<?= ADMIN_URL ?>/assets/js/fa-icons.js"></script>
    <script src="<?= ADMIN_URL ?>/assets/js/icon-picker.js"></script>

    <div class="admin-page-header">
        <div>
            <h1><?= $episode ? 'Edit Podcast Episode' : 'New Podcast Episode' ?></h1>
            <?php if ($episode): ?>
                <div class="subtitle">
                    Slug: <code class="text-mono"><?= e($f['slug']) ?></code>
                    <?php if ($f['status'] === 'published'): ?>
                        &middot; <a href="<?= url('/podcast/' . $f['slug']) ?>" target="_blank" rel="noopener">View live &rarr;</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <a href="<?= ADMIN_URL ?>/podcasts.php" class="admin-btn admin-btn--ghost">Back to list</a>
    </div>

    <form method="post" class="admin-form" id="podcast-form">
        <?= csrf_field() ?>

        <div class="section-tabs">
            <div class="section-tabs__nav" id="section-tabs-nav">
                <?php
                $tabs = [
                    'core' => 'Core', 'guest' => 'Guest', 'video' => 'Video', 'content' => 'Content',
                    'takeaways' => 'Key Takeaways', 'highlights' => 'Episode Highlights', 'seo' => 'SEO',
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
                            <label for="title">Episode Title *</label>
                            <input type="text" id="title" name="title" required value="<?= attr($f['title']) ?>">
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                            <div class="form-row">
                                <label for="slug">Slug</label>
                                <input type="text" id="slug" name="slug" value="<?= attr($f['slug']) ?>" placeholder="auto-generated">
                                <div class="help">Live at <code class="text-mono">/podcast/<?= e($f['slug'] ?: '{slug}') ?></code></div>
                            </div>
                            <div class="form-row">
                                <label for="publish_date">Publish Date</label>
                                <input type="date" id="publish_date" name="publish_date" value="<?= attr(substr((string) $f['publish_date'], 0, 10)) ?>">
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
                        <div class="form-row" style="margin-bottom:0;">
                            <label>
                                <input type="checkbox" name="featured" <?= $f['featured'] ? 'checked' : '' ?>>
                                Featured Episode <span class="text-muted">(shown in the /podcast Featured Episode section — only one episode can be featured; checking this unfeatures any other. If none is featured, the most recent published episode is shown instead.)</span>
                            </label>
                        </div>
                        <?php if ($f['published_at']): ?>
                            <div class="text-muted" style="font-size:12px;margin-top:8px;">Published <?= e(substr((string) $f['published_at'], 0, 16)) ?></div>
                        <?php endif; ?>
                    </div></div>
                </div>

                <!-- ============ GUEST ============ -->
                <div class="section-tabs__panel" data-panel="guest">
                    <div class="admin-card"><div class="admin-card__body">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                            <div class="form-row">
                                <label for="guest_name">Guest Name *</label>
                                <input type="text" id="guest_name" name="guest_name" required value="<?= attr($f['guest_name']) ?>">
                            </div>
                            <div class="form-row">
                                <label for="guest_designation">Guest Designation</label>
                                <input type="text" id="guest_designation" name="guest_designation" value="<?= attr($f['guest_designation']) ?>" placeholder="Founder of ...">
                            </div>
                        </div>
                        <div class="form-row">
                            <label for="guest_company">Guest Company</label>
                            <input type="text" id="guest_company" name="guest_company" value="<?= attr($f['guest_company']) ?>">
                        </div>
                        <div class="form-row" style="margin-bottom:0;">
                            <label for="guest_company_logo">Guest Company Logo <span class="text-muted">(shown on "Watch More Episodes" cards)</span></label>
                            <input type="text" id="guest_company_logo" name="guest_company_logo" value="<?= attr($f['guest_company_logo']) ?>" placeholder="/uploads/podcast/...">
                            <div class="help">Copy a path from the <a href="<?= ADMIN_URL ?>/media.php" target="_blank" rel="noopener">Media Library</a>, or paste any URL.</div>
                        </div>
                        <?php if (!empty($f['guest_company_logo'])): ?>
                            <img src="<?= attr(media_url($f['guest_company_logo'])) ?>" style="max-width:160px;border-radius:4px;border:1px solid var(--admin-border);margin-top:8px;" alt="">
                        <?php endif; ?>
                    </div></div>
                </div>

                <!-- ============ VIDEO ============ -->
                <div class="section-tabs__panel" data-panel="video">
                    <div class="admin-card"><div class="admin-card__body">
                        <div class="form-row">
                            <label for="youtube_url">YouTube URL *</label>
                            <input type="text" id="youtube_url" name="youtube_url" value="<?= attr($f['youtube_url']) ?>" placeholder="https://youtu.be/VIDEO_ID">
                            <div class="help">Supports youtu.be/ID and youtube.com/watch?v=ID links. The thumbnail below is derived automatically from this URL unless overridden.</div>
                        </div>
                        <div class="form-row" style="margin-bottom:0;">
                            <label for="thumbnail_override">Thumbnail Override <span class="text-muted">(optional)</span></label>
                            <input type="text" id="thumbnail_override" name="thumbnail_override" value="<?= attr($f['thumbnail_override']) ?>" placeholder="/uploads/podcast/...">
                            <div class="help">Leave blank to use the YouTube video's own thumbnail automatically.</div>
                        </div>
                        <?php if (!empty($f['thumbnail_override'])): ?>
                            <img src="<?= attr(media_url($f['thumbnail_override'])) ?>" style="max-width:220px;border-radius:4px;border:1px solid var(--admin-border);margin-top:8px;" alt="">
                        <?php endif; ?>
                    </div></div>
                </div>

                <!-- ============ CONTENT ============ -->
                <div class="section-tabs__panel" data-panel="content">
                    <div class="admin-card"><div class="admin-card__body">
                        <div class="form-row">
                            <label for="short_description">Short Description <span class="text-muted">(used for card/listing excerpts and as the SEO description fallback)</span></label>
                            <textarea id="short_description" name="short_description" rows="2"><?= e($f['short_description']) ?></textarea>
                        </div>
                        <div class="form-row" style="margin-bottom:0;">
                            <label for="description_html">Episode Description</label>
                            <textarea id="description_html" name="description_html" rows="6"><?= e($f['description_html']) ?></textarea>
                        </div>
                    </div></div>
                </div>

                <!-- ============ KEY TAKEAWAYS ============ -->
                <div class="section-tabs__panel" data-panel="takeaways">
                    <div class="admin-card"><div class="admin-card__body">
                        <?php svc_repeater_field('takeaways', 'Key Takeaways', '+ Add takeaway',
                            '<div class="form-row"><label>Text</label><textarea name="takeaway_text[]" rows="2"></textarea></div>' .
                            '<div class="form-row" style="margin-bottom:0;"><label><input type="checkbox" class="active-checkbox" name="takeaway_active[]" value="1" checked> Active</label></div>',
                            $repeater_data['key_takeaways'],
                            ['textarea[name="takeaway_text[]"]' => 'text', 'input.active-checkbox' => ['key' => 'active', 'type' => 'checkbox']]
                        ); ?>
                    </div></div>
                </div>

                <!-- ============ EPISODE HIGHLIGHTS ============ -->
                <div class="section-tabs__panel" data-panel="highlights">
                    <div class="admin-card"><div class="admin-card__body">
                        <?php svc_repeater_field('highlights', 'Episode Highlights', '+ Add highlight',
                            '<div class="form-row"><label>Text</label><textarea name="highlight_text[]" rows="2"></textarea></div>' .
                            '<div class="form-row" style="margin-bottom:0;"><label><input type="checkbox" class="active-checkbox" name="highlight_active[]" value="1" checked> Active</label></div>',
                            $repeater_data['highlights'],
                            ['textarea[name="highlight_text[]"]' => 'text', 'input.active-checkbox' => ['key' => 'active', 'type' => 'checkbox']]
                        ); ?>
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
                            <label for="meta_description">Meta Description <span class="text-muted">(optional — defaults to Short Description)</span></label>
                            <textarea id="meta_description" name="meta_description" rows="2" maxlength="320"><?= e($f['meta_description']) ?></textarea>
                        </div>
                        <div class="form-row">
                            <label for="meta_keywords">Meta Keywords</label>
                            <input type="text" id="meta_keywords" name="meta_keywords" value="<?= attr($f['meta_keywords']) ?>">
                        </div>
                        <div class="form-row">
                            <label for="og_image">OG Image <span class="text-muted">(optional — defaults to the episode thumbnail)</span></label>
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
                                JSON-LD Schemas <span class="text-muted">(PodcastEpisode, BreadcrumbList, or any custom schema — add as many as you need)</span>
                                <a href="#" id="gen-podcast-schema" class="admin-btn admin-btn--ghost admin-btn--small" style="float:right;">+ Generate schema</a>
                            </label>
                        </div>
                        <?php svc_repeater_field('schemas', 'JSON-LD Schemas', '+ Add Another Schema',
                            '<div class="form-row"><label>Schema Name / Label <span class="text-muted">(optional)</span></label>' .
                            '<input type="text" name="schema_label[]" placeholder="e.g. PodcastEpisode Schema"></div>' .
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
                <button type="submit" class="admin-btn"><?= $episode ? 'Update Episode' : 'Create Episode' ?></button>
                <a href="<?= ADMIN_URL ?>/podcasts.php" class="admin-btn admin-btn--ghost">Cancel</a>
            </div>
        </div>
    </form>

    <!-- CKEditor 5 (rich content field: description_html) -->
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

        // ---- CKEditor on rich-content field ----
        var descEl = document.getElementById('description_html');
        if (descEl) {
            ClassicEditor.create(descEl, {
                toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList',
                          '|', 'blockQuote', '|', 'undo', 'redo', '|', 'sourceEditing']
            }).catch(function (err) { console.error(err); });
        }

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
        var genBtn = document.getElementById('gen-podcast-schema');
        if (genBtn) {
            genBtn.addEventListener('click', function (e) {
                e.preventDefault();
                var SITE = <?= json_encode(defined('SITE_URL') ? SITE_URL : '') ?>;
                var slug = slugEl ? slugEl.value : '';
                var shortDescEl = document.getElementById('short_description');
                var guestEl = document.getElementById('guest_name');
                var data = {
                    '@context': 'https://schema.org',
                    '@type': 'PodcastEpisode',
                    'name': titleEl ? titleEl.value : '',
                    'description': shortDescEl ? shortDescEl.value : '',
                    'url': SITE + '/podcast/' + slug,
                    'associatedMedia': { '@type': 'MediaObject' },
                    'actor': guestEl ? [{ '@type': 'Person', 'name': guestEl.value }] : []
                };
                var repeater = window.svcRepeaters && window.svcRepeaters['schemas'];
                if (repeater) {
                    repeater.addRow({ label: 'PodcastEpisode Schema', code: JSON.stringify(data, null, 2) });
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
    $where = ' WHERE status = :st';
    $params[':st'] = $status_filter;
}

$stmt = $pdo->prepare(
    "SELECT id, slug, title, guest_name, status, featured, publish_date, created_at, updated_at
     FROM podcasts
     $where
     ORDER BY COALESCE(publish_date, DATE(updated_at)) DESC, id DESC LIMIT 200"
);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$admin_page_title = 'Podcast Episodes';
$admin_active     = 'podcasts';
require __DIR__ . '/_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>Podcast Episodes</h1>
        <div class="subtitle"><?= count($rows) ?> episode<?= count($rows) === 1 ? '' : 's' ?></div>
    </div>
    <a href="?action=new" class="admin-btn">New Episode</a>
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
                <th>Title</th>
                <th>Guest</th>
                <th>Featured</th>
                <th>Status</th>
                <th>Publish Date</th>
                <th class="col-actions">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="6" class="empty-state">
                    <h3>No podcast episodes yet</h3>
                    <a href="?action=new" class="admin-btn">New Episode</a>
                </td></tr>
            <?php else: ?>
                <?php foreach ($rows as $r): ?>
                <tr>
                    <td><a href="?action=edit&amp;id=<?= (int) $r['id'] ?>"><?= e($r['title']) ?></a></td>
                    <td><?= e($r['guest_name']) ?></td>
                    <td><?= $r['featured'] ? '★' : '' ?></td>
                    <td><span class="pill pill--<?= attr($r['status']) ?>"><?= e($r['status']) ?></span></td>
                    <td><?= e($r['publish_date'] ? substr((string) $r['publish_date'], 0, 10) : '') ?></td>
                    <td class="col-actions">
                        <?php if ($r['status'] === 'published'): ?>
                            <a href="<?= url('/podcast/' . $r['slug']) ?>" target="_blank" rel="noopener" class="admin-btn admin-btn--ghost admin-btn--small">View</a>
                        <?php endif; ?>
                        <a href="?action=edit&amp;id=<?= (int) $r['id'] ?>" class="admin-btn admin-btn--small">Edit</a>
                        <form method="post" action="?action=delete" style="display:inline;"
                              onsubmit="return confirm('Delete this episode permanently?');">
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
