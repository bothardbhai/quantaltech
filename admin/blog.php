<?php
/**
 * Blog admin — full CRUD for posts. Uses CKEditor 5 from CDN, with a custom
 * upload adapter that posts to /admin/api/upload-image.php.
 */

require __DIR__ . '/bootstrap.php';

$pdo = db();
if (!$pdo) {
    die('Database connection failed.');
}

$action = $_GET['action'] ?? 'list';
$user   = auth_user();

// ===========================================================================
// Helpers
// ===========================================================================

/**
 * Slugify a string. Keep ASCII only, lowercase, hyphens.
 */
function slugify(string $text): string
{
    $text = strtolower(trim($text));
    // Convert non-ASCII to ASCII where possible (transliterate)
    if (function_exists('iconv')) {
        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        if ($converted !== false) {
            $text = $converted;
        }
    }
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    $text = trim($text, '-');
    return $text === '' ? 'post' : substr($text, 0, 180);
}

/**
 * Make sure a slug is unique. If not, append -2, -3, ...
 */
function unique_slug(PDO $pdo, string $base, ?int $excluding_id = null): string
{
    $slug = $base;
    $i = 2;
    while (true) {
        $sql = 'SELECT id FROM posts WHERE slug = :s' . ($excluding_id ? ' AND id != :id' : '');
        $stmt = $pdo->prepare($sql);
        $params = [':s' => $slug];
        if ($excluding_id) { $params[':id'] = $excluding_id; }
        $stmt->execute($params);
        if (!$stmt->fetch()) {
            return $slug;
        }
        $slug = $base . '-' . $i++;
        if ($i > 999) { return $slug . '-' . bin2hex(random_bytes(3)); } // give up gracefully
    }
}

/**
 * Replace all FAQs for a post. Full delete-then-reinsert — simpler than
 * diffing and cheap since a post rarely has more than a handful of FAQs.
 * Blank rows (no question) are dropped.
 */
function save_post_faqs(PDO $pdo, int $post_id, array $questions, array $answers): void
{
    $pdo->prepare('DELETE FROM post_faqs WHERE post_id = :pid')->execute([':pid' => $post_id]);

    $stmt = $pdo->prepare(
        'INSERT INTO post_faqs (post_id, question, answer, sort_order) VALUES (:pid, :q, :a, :so)'
    );
    $order = 0;
    foreach ($questions as $i => $question) {
        $question = trim((string) $question);
        $answer   = trim((string) ($answers[$i] ?? ''));
        if ($question === '') {
            continue;
        }
        $stmt->execute([':pid' => $post_id, ':q' => $question, ':a' => $answer, ':so' => $order++]);
    }
}

/**
 * Sanitize CKEditor HTML output. Allow a generous subset of tags suitable for
 * blog content; strip <script>, on* attrs, javascript: hrefs.
 */
function sanitize_post_html(string $html): string
{
    // Strip <script> blocks entirely
    $html = preg_replace('#<script\b[^>]*>.*?</script>#is', '', $html) ?? '';
    // Strip <style> blocks
    $html = preg_replace('#<style\b[^>]*>.*?</style>#is', '', $html) ?? '';
    // Remove on* attributes (event handlers)
    $html = preg_replace('#\s+on[a-z]+\s*=\s*"[^"]*"#i', '', $html) ?? '';
    $html = preg_replace("#\s+on[a-z]+\s*=\s*'[^']*'#i", '', $html) ?? '';
    // Block javascript: in href/src
    $html = preg_replace('#(href|src)\s*=\s*"javascript:[^"]*"#i', '$1="#"', $html) ?? '';
    return $html;
}

// ===========================================================================
// DELETE
// ===========================================================================
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_or_die();
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt = $pdo->prepare('DELETE FROM posts WHERE id = :id');
        $stmt->execute([':id' => $id]);
        flash('success', 'Post deleted.');
    }
    header('Location: ' . ADMIN_URL . '/blog.php');
    exit;
}

// ===========================================================================
// EDIT / NEW (same form, different load source)
// ===========================================================================
if ($action === 'new' || $action === 'edit') {
    $id = (int) ($_GET['id'] ?? 0);
    $post = null;
    $faqs = [];

    if ($action === 'edit' && $id > 0) {
        $stmt = $pdo->prepare('SELECT * FROM posts WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $post = $stmt->fetch();
        if (!$post) {
            flash('error', 'Post not found.');
            header('Location: ' . ADMIN_URL . '/blog.php'); exit;
        }
    }

    // ---------- Save ----------
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_verify_or_die();

        $title           = trim((string) ($_POST['title'] ?? ''));
        $slug_raw        = trim((string) ($_POST['slug'] ?? ''));
        $excerpt         = trim((string) ($_POST['excerpt'] ?? ''));
        $body_html       = sanitize_post_html((string) ($_POST['body_html'] ?? ''));
        $featured_image  = trim((string) ($_POST['featured_image'] ?? ''));
        $featured_alt    = trim((string) ($_POST['featured_alt'] ?? ''));
        $status          = in_array($_POST['status'] ?? 'draft', ['draft', 'published', 'archived'], true)
                              ? $_POST['status'] : 'draft';
        $meta_title      = trim((string) ($_POST['meta_title'] ?? ''));
        $meta_description= trim((string) ($_POST['meta_description'] ?? ''));
        $meta_keywords   = trim((string) ($_POST['meta_keywords'] ?? ''));
        $og_image        = trim((string) ($_POST['og_image'] ?? ''));
        $schema_json     = trim((string) ($_POST['schema_json'] ?? ''));
        $faq_questions   = is_array($_POST['faq_question'] ?? null) ? $_POST['faq_question'] : [];
        $faq_answers     = is_array($_POST['faq_answer'] ?? null) ? $_POST['faq_answer'] : [];

        // Validations
        $errors = [];
        if ($title === '')      { $errors[] = 'Title is required.'; }
        if ($body_html === '')  { $errors[] = 'Post body cannot be empty.'; }
        if ($schema_json !== '') {
            json_decode($schema_json);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $errors[] = 'JSON-LD schema is invalid: ' . json_last_error_msg();
            }
        }

        if ($errors) {
            foreach ($errors as $err) flash('error', $err);
            // Repopulate from POST so user doesn't lose input
            $post = array_merge((array) $post, $_POST);
            foreach ($faq_questions as $i => $q) {
                $faqs[] = ['question' => (string) $q, 'answer' => (string) ($faq_answers[$i] ?? '')];
            }
        } else {
            $slug_base = $slug_raw !== '' ? slugify($slug_raw) : slugify($title);
            $slug      = unique_slug($pdo, $slug_base, $post['id'] ?? null);

            $published_at = null;
            if ($status === 'published') {
                $published_at = ($post['published_at'] ?? null) ?: date('Y-m-d H:i:s');
            }

            if ($post && isset($post['id'])) {
                // Update
                $stmt = $pdo->prepare(
                    'UPDATE posts SET
                       slug = :slug, title = :title, excerpt = :excerpt, body_html = :body,
                       featured_image = :fi, featured_alt = :fa, status = :st, published_at = :pa,
                       meta_title = :mt, meta_description = :md, meta_keywords = :mk,
                       og_image = :og, schema_json = :sj
                     WHERE id = :id'
                );
                $stmt->execute([
                    ':slug'   => $slug,
                    ':title'  => $title,
                    ':excerpt'=> $excerpt,
                    ':body'   => $body_html,
                    ':fi'     => $featured_image,
                    ':fa'     => $featured_alt,
                    ':st'     => $status,
                    ':pa'     => $published_at,
                    ':mt'     => $meta_title,
                    ':md'     => $meta_description,
                    ':mk'     => $meta_keywords,
                    ':og'     => $og_image,
                    ':sj'     => $schema_json,
                    ':id'     => $post['id'],
                ]);
                save_post_faqs($pdo, (int) $post['id'], $faq_questions, $faq_answers);
                flash('success', 'Post updated.');
                header('Location: ' . ADMIN_URL . '/blog.php?action=edit&id=' . $post['id']);
                exit;
            } else {
                // Insert
                $stmt = $pdo->prepare(
                    'INSERT INTO posts (slug, title, excerpt, body_html, featured_image, featured_alt,
                       author_id, status, published_at, meta_title, meta_description, meta_keywords,
                       og_image, schema_json)
                     VALUES (:slug,:title,:excerpt,:body,:fi,:fa,:auth,:st,:pa,:mt,:md,:mk,:og,:sj)'
                );
                $stmt->execute([
                    ':slug'  => $slug,
                    ':title' => $title,
                    ':excerpt'=> $excerpt,
                    ':body'  => $body_html,
                    ':fi'    => $featured_image,
                    ':fa'    => $featured_alt,
                    ':auth'  => $user['id'],
                    ':st'    => $status,
                    ':pa'    => $published_at,
                    ':mt'    => $meta_title,
                    ':md'    => $meta_description,
                    ':mk'    => $meta_keywords,
                    ':og'    => $og_image,
                    ':sj'    => $schema_json,
                ]);
                $newId = (int) $pdo->lastInsertId();
                save_post_faqs($pdo, $newId, $faq_questions, $faq_answers);
                flash('success', 'Post created.');
                header('Location: ' . ADMIN_URL . '/blog.php?action=edit&id=' . $newId);
                exit;
            }
        }
    }

    // On a plain GET (no pending validation errors), load FAQs from the DB.
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $post && isset($post['id'])) {
        $stmt = $pdo->prepare('SELECT question, answer FROM post_faqs WHERE post_id = :pid ORDER BY sort_order ASC, id ASC');
        $stmt->execute([':pid' => $post['id']]);
        $faqs = $stmt->fetchAll();
    }

    // Defaults for the form
    $f = $post ?: [
        'id' => null, 'title' => '', 'slug' => '', 'excerpt' => '', 'body_html' => '',
        'featured_image' => '', 'featured_alt' => '', 'status' => 'draft',
        'meta_title' => '', 'meta_description' => '', 'meta_keywords' => '',
        'og_image' => '', 'schema_json' => '', 'published_at' => null,
    ];

    $admin_page_title = $post ? 'Edit Post' : 'New Post';
    $admin_active     = 'blog';
    require __DIR__ . '/_header.php';
    ?>

    <div class="admin-page-header">
        <div>
            <h1><?= $post ? 'Edit Post' : 'New Post' ?></h1>
            <?php if ($post): ?>
                <div class="subtitle">URL: <code class="text-mono">/blog/<?= e($f['slug']) ?></code></div>
            <?php endif; ?>
        </div>
        <div>
            <?php if ($post && $f['status'] === 'published'): ?>
                <a href="/blog/<?= attr($f['slug']) ?>" target="_blank" rel="noopener" class="admin-btn admin-btn--ghost">View live →</a>
            <?php endif; ?>
            <a href="<?= ADMIN_URL ?>/blog.php" class="admin-btn admin-btn--ghost">Back to list</a>
        </div>
    </div>

    <form method="post" class="admin-form" id="post-form">
        <?= csrf_field() ?>
        <div style="display:grid;grid-template-columns:minmax(0,1fr) 320px;gap:24px;align-items:flex-start;">

            <!-- LEFT: title, body, SEO -->
            <div>
                <div class="admin-card">
                    <div class="admin-card__body">
                        <div class="form-row">
                            <label for="title">Title</label>
                            <input type="text" id="title" name="title" maxlength="255" required value="<?= attr($f['title']) ?>">
                        </div>
                        <div class="form-row">
                            <label for="slug">URL slug</label>
                            <input type="text" id="slug" name="slug" maxlength="200" value="<?= attr($f['slug']) ?>" placeholder="auto-generated from title">
                            <div class="help">Lowercase letters, numbers, hyphens. Leave blank to auto-generate from title.</div>
                        </div>
                        <div class="form-row">
                            <label for="excerpt">Excerpt</label>
                            <textarea id="excerpt" name="excerpt" rows="2" maxlength="500"><?= e($f['excerpt']) ?></textarea>
                            <div class="help">Short summary shown on the blog listing.</div>
                        </div>
                    </div>
                </div>

                <div class="admin-card">
                    <div class="admin-card__head">Body</div>
                    <div class="admin-card__body">
                        <div class="editor-wrap">
                            <textarea id="body_html" name="body_html"><?= e($f['body_html']) ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="admin-card">
                    <div class="admin-card__head">
                        FAQs
                        <button type="button" id="add-faq-row" class="admin-btn admin-btn--ghost admin-btn--small">+ Add FAQ</button>
                    </div>
                    <div class="admin-card__body">
                        <div class="help" style="margin-top:0;margin-bottom:14px;">Shown on the blog post page and included as FAQ structured data for search engines.</div>
                        <div id="faq-rows">
                            <?php foreach ($faqs as $faq): ?>
                                <div class="repeater-row">
                                    <div class="repeater-row__head">
                                        <span class="repeater-row__title">FAQ</span>
                                        <div class="repeater-row__actions">
                                            <button type="button" class="faq-move-up" title="Move up">&uarr;</button>
                                            <button type="button" class="faq-move-down" title="Move down">&darr;</button>
                                            <button type="button" class="faq-remove" title="Remove">&times;</button>
                                        </div>
                                    </div>
                                    <div class="form-row" style="margin-bottom:8px;">
                                        <input type="text" name="faq_question[]" maxlength="500" placeholder="Question" value="<?= attr($faq['question']) ?>">
                                    </div>
                                    <div class="form-row" style="margin-bottom:0;">
                                        <textarea name="faq_answer[]" rows="2" placeholder="Answer"><?= e($faq['answer']) ?></textarea>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div id="faq-empty" class="text-muted" style="<?= empty($faqs) ? '' : 'display:none;' ?>font-size:13px;">No FAQs yet. Click "+ Add FAQ" to add one.</div>
                    </div>
                </div>

                <div class="admin-card">
                    <div class="admin-card__head">SEO</div>
                    <div class="admin-card__body">
                        <div class="form-row">
                            <label for="meta_title">Meta title <span class="text-muted">(optional)</span></label>
                            <input type="text" id="meta_title" name="meta_title" maxlength="255" value="<?= attr($f['meta_title']) ?>" placeholder="Defaults to post title">
                        </div>
                        <div class="form-row">
                            <label for="meta_description">Meta description</label>
                            <textarea id="meta_description" name="meta_description" rows="2" maxlength="320"><?= e($f['meta_description']) ?></textarea>
                            <div class="help">Defaults to excerpt if blank.</div>
                        </div>
                        <div class="form-row">
                            <label for="meta_keywords">Meta keywords</label>
                            <input type="text" id="meta_keywords" name="meta_keywords" maxlength="255" value="<?= attr($f['meta_keywords']) ?>">
                        </div>
                        <div class="form-row">
                            <label for="og_image">OG image override <span class="text-muted">(optional)</span></label>
                            <input type="text" id="og_image" name="og_image" maxlength="500" value="<?= attr($f['og_image']) ?>" placeholder="Defaults to featured image">
                        </div>
                        <div class="form-row">
                            <label for="schema_json">
                                JSON-LD schema
                                <a href="#" id="gen-article" class="admin-btn admin-btn--ghost admin-btn--small" style="float:right;">Generate Article schema</a>
                            </label>
                            <textarea id="schema_json" name="schema_json" rows="6" class="json-textarea"><?= e($f['schema_json']) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT: status, featured image, save -->
            <div>
                <div class="admin-card">
                    <div class="admin-card__head">Publish</div>
                    <div class="admin-card__body">
                        <div class="form-row">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="draft"     <?= $f['status'] === 'draft'     ? 'selected' : '' ?>>Draft</option>
                                <option value="published" <?= $f['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                                <option value="archived"  <?= $f['status'] === 'archived'  ? 'selected' : '' ?>>Archived</option>
                            </select>
                        </div>
                        <?php if ($f['published_at']): ?>
                            <div class="text-muted" style="font-size:12px;">Published <?= e(substr((string)$f['published_at'], 0, 16)) ?></div>
                        <?php endif; ?>
                        <button type="submit" class="admin-btn" style="width:100%;margin-top:10px;">
                            <?= $post ? 'Update' : 'Create post' ?>
                        </button>
                    </div>
                </div>

                <div class="admin-card">
                    <div class="admin-card__head">Featured image</div>
                    <div class="admin-card__body">
                        <div class="form-row">
                            <input type="text" id="featured_image" name="featured_image" maxlength="500" value="<?= attr($f['featured_image']) ?>" placeholder="/uploads/blog/...">
                            <div class="help">Use the editor's image upload, then copy the URL here, or paste any path.</div>
                        </div>
                        <?php if (!empty($f['featured_image'])): ?>
                            <img src="<?= attr(media_url($f['featured_image'])) ?>" style="max-width:100%;border-radius:4px;border:1px solid var(--admin-border);" alt="">
                        <?php endif; ?>
                        <div class="form-row" style="margin-top:10px;">
                            <label for="featured_alt">Alt text</label>
                            <input type="text" id="featured_alt" name="featured_alt" maxlength="255" value="<?= attr($f['featured_alt']) ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- CKEditor 5 from CDN. SimpleUploadAdapter posts to our endpoint. -->
    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
    <script>
    (function() {
        const csrfToken = <?= json_encode(csrf_token()) ?>;
        ClassicEditor
            .create(document.querySelector('#body_html'), {
                toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList',
                          '|', 'blockQuote', 'insertTable', 'imageUpload', 'mediaEmbed',
                          '|', 'undo', 'redo', '|', 'sourceEditing'],
                simpleUpload: {
                    uploadUrl: '<?= ADMIN_URL ?>/api/upload-image.php',
                    headers: { 'X-CSRF-Token': csrfToken }
                }
            })
            .catch(err => console.error(err));

        // Slug auto-generation: only fill when slug field is empty
        const titleEl = document.getElementById('title');
        const slugEl  = document.getElementById('slug');
        let userTouchedSlug = slugEl.value.trim() !== '';
        slugEl.addEventListener('input', () => userTouchedSlug = slugEl.value.trim() !== '');
        titleEl.addEventListener('input', () => {
            if (userTouchedSlug) return;
            slugEl.value = titleEl.value
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '')
                .substring(0, 180);
        });

        // Schema generator
        document.getElementById('gen-article').addEventListener('click', function(e) {
            e.preventDefault();
            const titleVal = titleEl.value;
            const desc = document.getElementById('meta_description').value
                       || document.getElementById('excerpt').value;
            const featured = document.getElementById('featured_image').value;
            const slug = slugEl.value;
            const SITE = <?= json_encode(defined('SITE_URL') ? SITE_URL : '') ?>;
            const data = {
                '@context': 'https://schema.org',
                '@type':    'Article',
                'headline': titleVal,
                'description': desc,
                'image':    featured ? [featured] : [],
                'mainEntityOfPage': SITE + '/blog/' + slug
            };
            document.getElementById('schema_json').value = JSON.stringify(data, null, 2);
        });
    })();
    </script>

    <!-- FAQ repeater: add / remove / reorder rows -->
    <script>
    (function() {
        const rowsWrap = document.getElementById('faq-rows');
        const emptyMsg = document.getElementById('faq-empty');
        const addBtn   = document.getElementById('add-faq-row');

        function updateEmptyState() {
            emptyMsg.style.display = rowsWrap.children.length ? 'none' : '';
        }

        function makeRow() {
            const row = document.createElement('div');
            row.className = 'repeater-row';
            row.innerHTML = [
                '<div class="repeater-row__head">',
                    '<span class="repeater-row__title">FAQ</span>',
                    '<div class="repeater-row__actions">',
                        '<button type="button" class="faq-move-up" title="Move up">&uarr;</button>',
                        '<button type="button" class="faq-move-down" title="Move down">&darr;</button>',
                        '<button type="button" class="faq-remove" title="Remove">&times;</button>',
                    '</div>',
                '</div>',
                '<div class="form-row" style="margin-bottom:8px;">',
                    '<input type="text" name="faq_question[]" maxlength="500" placeholder="Question">',
                '</div>',
                '<div class="form-row" style="margin-bottom:0;">',
                    '<textarea name="faq_answer[]" rows="2" placeholder="Answer"></textarea>',
                '</div>',
            ].join('');
            return row;
        }

        addBtn.addEventListener('click', function() {
            rowsWrap.appendChild(makeRow());
            updateEmptyState();
        });

        rowsWrap.addEventListener('click', function(e) {
            const row = e.target.closest('.repeater-row');
            if (!row) return;
            if (e.target.classList.contains('faq-remove')) {
                row.remove();
                updateEmptyState();
            } else if (e.target.classList.contains('faq-move-up')) {
                const prev = row.previousElementSibling;
                if (prev) rowsWrap.insertBefore(row, prev);
            } else if (e.target.classList.contains('faq-move-down')) {
                const next = row.nextElementSibling;
                if (next) rowsWrap.insertBefore(next, row);
            }
        });

        updateEmptyState();
    })();
    </script>

    <?php
    require __DIR__ . '/_footer.php';
    exit;
}

// ===========================================================================
// LIST view (default)
// ===========================================================================
$status_filter = $_GET['status'] ?? '';
$where = '';
$params = [];
if (in_array($status_filter, ['draft', 'published', 'archived'], true)) {
    $where = ' WHERE status = :st';
    $params[':st'] = $status_filter;
}

$stmt = $pdo->prepare(
    'SELECT id, slug, title, status, published_at, updated_at
     FROM posts'
    . $where . '
     ORDER BY COALESCE(published_at, updated_at) DESC
     LIMIT 200'
);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$admin_page_title = 'Blog';
$admin_active     = 'blog';
require __DIR__ . '/_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>Blog</h1>
        <div class="subtitle"><?= count($rows) ?> post<?= count($rows) === 1 ? '' : 's' ?></div>
    </div>
    <div>
        <a href="?action=new" class="admin-btn">New Post</a>
    </div>
</div>

<div style="margin-bottom:14px;">
    <a href="?" class="<?= $status_filter === '' ? 'admin-btn admin-btn--small' : 'admin-btn admin-btn--ghost admin-btn--small' ?>">All</a>
    <a href="?status=published" class="<?= $status_filter === 'published' ? 'admin-btn admin-btn--small' : 'admin-btn admin-btn--ghost admin-btn--small' ?>">Published</a>
    <a href="?status=draft" class="<?= $status_filter === 'draft' ? 'admin-btn admin-btn--small' : 'admin-btn admin-btn--ghost admin-btn--small' ?>">Drafts</a>
    <a href="?status=archived" class="<?= $status_filter === 'archived' ? 'admin-btn admin-btn--small' : 'admin-btn admin-btn--ghost admin-btn--small' ?>">Archived</a>
</div>

<div class="admin-card">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Status</th>
                <th>Slug</th>
                <th>Updated</th>
                <th class="col-actions">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="5" class="empty-state">
                    <h3>No posts yet</h3>
                    <p>Create your first blog post to get started.</p>
                    <a href="?action=new" class="admin-btn">New Post</a>
                </td></tr>
            <?php else: ?>
                <?php foreach ($rows as $r): ?>
                <tr>
                    <td><a href="?action=edit&amp;id=<?= (int)$r['id'] ?>"><?= e($r['title']) ?></a></td>
                    <td><span class="pill pill--<?= attr($r['status']) ?>"><?= e($r['status']) ?></span></td>
                    <td><code class="text-mono">/blog/<?= e($r['slug']) ?></code></td>
                    <td class="no-wrap text-muted"><?= e(substr((string)$r['updated_at'], 0, 16)) ?></td>
                    <td class="col-actions">
                        <?php if ($r['status'] === 'published'): ?>
                            <a href="/blog/<?= attr($r['slug']) ?>" target="_blank" rel="noopener" class="admin-btn admin-btn--ghost admin-btn--small">View</a>
                        <?php endif; ?>
                        <a href="?action=edit&amp;id=<?= (int)$r['id'] ?>" class="admin-btn admin-btn--small">Edit</a>
                        <form method="post" action="?action=delete" style="display:inline;" onsubmit="return confirm('Delete this post permanently?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
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
