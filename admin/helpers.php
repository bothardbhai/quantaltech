<?php
/**
 * Shared admin form-building helpers — the generic repeater engine and
 * JSON-column normalizers used by more than one admin module (Service
 * Master, Pages & SEO Master, ...). Extracted out of admin/services.php,
 * which originally defined these for its own ~20 repeater sections, so
 * other modules (e.g. admin/pages.php's multi-schema JSON-LD manager) can
 * reuse the exact same engine instead of duplicating it.
 */

declare(strict_types=1);

/**
 * "One item per line" textarea -> array of strings. Reused everywhere a
 * section just needs a flat list (platforms, tag lists, bullet points).
 */
function svc_lines_to_array(string $raw): array
{
    return array_values(array_filter(array_map('trim', explode("\n", $raw))));
}

/**
 * Build a JSON-ready array of rows from parallel POST arrays — one shared
 * builder for every object-repeater section instead of hand-rolling the
 * same loop per section. $fields maps output key => POST field name (plain
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

function svc_json_decode(?string $raw): array
{
    if (!$raw) { return []; }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

/**
 * Admin UI for a page's "Success Stories" teaser section (services + hire
 * pages): a "hide section" toggle and an optional hand-picked story list.
 * Posts `cs_hidden` and `cs_story_ids[]` — see save handlers in
 * admin/services.php / admin/hire.php and the frontend resolve in
 * pages/{services,hire}/_*-dynamic.php (no picks = latest 4 stories).
 *
 * @param array $stories   published success_stories rows (id, title, slug)
 * @param int[] $selected  currently picked story IDs
 */
function svc_case_study_picker(array $stories, array $selected, bool $hidden): void
{
    ?>
    <div class="admin-card"><div class="admin-card__body">
        <label style="display:block;padding:0 0 12px;">
            <input type="checkbox" name="cs_hidden" value="1" <?= $hidden ? 'checked' : '' ?>>
            <strong>Hide this section entirely</strong>
            <span class="text-muted">(nothing is shown on the page)</span>
        </label>
        <p class="text-muted" style="font-size:13px;margin-top:0;">
            Pick up to 4
            <a href="<?= ADMIN_URL ?>/success-stories.php" target="_blank" rel="noopener">Success Stories</a>
            to show here. If none are picked, the 4 most recently published stories are shown automatically.
        </p>
        <div class="repeater-row__title" style="margin-bottom:10px;">Choose Stories</div>
        <?php if (empty($stories)): ?>
            <p class="text-muted" style="font-size:13px;">No published stories yet.</p>
        <?php endif; ?>
        <?php foreach ($stories as $st): ?>
            <label style="display:block;padding:6px 0;">
                <input type="checkbox" name="cs_story_ids[]" value="<?= (int) $st['id'] ?>"
                    <?= in_array((int) $st['id'], $selected, true) ? 'checked' : '' ?>>
                <?= e($st['title']) ?> <span class="text-muted">(<?= e($st['slug']) ?>)</span>
            </label>
        <?php endforeach; ?>
    </div></div>
    <?php
}

/**
 * Holder for the page being edited's decoded `section_cta_json`, so the
 * section-header helpers (which only receive a prefix) can pre-fill their CTA
 * fields without threading another argument through ~30 call sites.
 */
function svc_section_cta_store(?array $set = null): array
{
    static $data = [];
    if ($set !== null) {
        $data = $set;
    }
    return $data;
}

/**
 * Admin fields for one section's optional CTA block (text above a button, the
 * button, text below it). Posts sec_cta[<key>][before|btn_text|btn_url|new_tab|after];
 * the rich-text boxes are upgraded by each page's `.js-seccta-rich` CKEditor
 * init. Pass $card = true to wrap in its own admin-card (for sections that
 * don't render through a header helper). All blank = nothing on the page.
 */
function svc_section_cta_fields(string $key, bool $card = false): void
{
    $c = svc_section_cta_store()[$key] ?? [];
    $name = 'sec_cta[' . $key . ']';
    $id = 'seccta_' . $key;
    if ($card) {
        echo '<div class="admin-card"><div class="admin-card__body">';
    }
    ?>
    <div style="border-top:1px solid #3a3a3a;margin:18px 0 14px;padding-top:14px;">
        <div class="repeater-row__title" style="margin-bottom:4px;">Call to Action <span class="text-muted">(optional — shown at the bottom of this section only if filled in)</span></div>
        <div class="form-row">
            <label for="<?= attr($id) ?>_before">Text above the button</label>
            <textarea id="<?= attr($id) ?>_before" class="js-seccta-rich" name="<?= attr($name) ?>[before]" rows="2"><?= e($c['before'] ?? '') ?></textarea>
        </div>
        <div class="form-row">
            <label for="<?= attr($id) ?>_label">Button Text</label>
            <input type="text" id="<?= attr($id) ?>_label" name="<?= attr($name) ?>[btn_text]" value="<?= attr($c['btn_text'] ?? '') ?>" placeholder="Book a Free Scoping Call">
        </div>
        <div class="form-row">
            <label for="<?= attr($id) ?>_url">Button URL <span class="text-muted">(a site path like /contact, an #anchor, or a full https:// link — the button only shows when both text and URL are set)</span></label>
            <input type="text" id="<?= attr($id) ?>_url" name="<?= attr($name) ?>[btn_url]" value="<?= attr($c['btn_url'] ?? '') ?>" placeholder="/contact">
            <label style="display:block;margin-top:6px;font-weight:normal;">
                <input type="checkbox" name="<?= attr($name) ?>[new_tab]" value="1" <?= !empty($c['new_tab']) ? 'checked' : '' ?>> Open in a new tab
            </label>
        </div>
        <div class="form-row" style="margin-bottom:0;">
            <label for="<?= attr($id) ?>_after">Text below the button</label>
            <textarea id="<?= attr($id) ?>_after" class="js-seccta-rich" name="<?= attr($name) ?>[after]" rows="2"><?= e($c['after'] ?? '') ?></textarea>
        </div>
    </div>
    <?php
    if ($card) {
        echo '</div></div>';
    }
}

/**
 * JSON for a page's `section_cta_json` column from the posted sec_cta[] fields.
 * Sections with nothing meaningful are dropped; a button needs both a label
 * and a valid URL (see section_cta_safe_url) — a label with a bad URL is
 * reported via $warnings so the caller can flash it.
 *
 * @param string[] $warnings
 */
function svc_section_cta_from_post(array $post, array &$warnings = []): string
{
    $clean_rich = static function ($v): string {
        $v = trim(sanitize_html_fragment((string) $v));
        $plain = trim(str_replace(['&nbsp;', "\xC2\xA0"], ' ', strip_tags($v)));
        return ($plain === '' && stripos($v, '<img') === false) ? '' : $v;
    };

    $out = [];
    foreach (($post['sec_cta'] ?? []) as $key => $c) {
        if (!is_array($c) || !preg_match('/^[a-z0-9_]+$/', (string) $key)) {
            continue;
        }
        $label = trim(strip_tags((string) ($c['btn_text'] ?? '')));
        $raw_url = trim((string) ($c['btn_url'] ?? ''));
        $url = section_cta_safe_url($raw_url);
        if ($label !== '' && $url === '') {
            $warnings[] = 'The CTA button for "' . $key . '" was not saved: its URL must start with /, #, http://, https://, mailto: or tel:.';
            $label = '';
        } elseif ($label === '') {
            $url = '';
        }
        $entry = [
            'before'   => $clean_rich($c['before'] ?? ''),
            'btn_text' => $label,
            'btn_url'  => $url,
            'new_tab'  => !empty($c['new_tab']) ? 1 : 0,
            'after'    => $clean_rich($c['after'] ?? ''),
        ];
        if ($entry['before'] !== '' || $entry['after'] !== '' || $entry['btn_text'] !== '') {
            $out[(string) $key] = $entry;
        }
    }
    return json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

/**
 * JSON for a Success Story's `hero_buttons_json` from the posted hero_btn[btn1|btn2][text|url|new_tab|hide]
 * fields. A form that doesn't post hero_btn at all (opened before the fields existed) keeps the saved value
 * instead of wiping it. A link that isn't a site path, #anchor, http(s), mailto: or tel: is dropped with a flash.
 */
function ss_hero_buttons_from_post(array $post, ?string $existing_json): ?string
{
    if (!isset($post['hero_btn']) || !is_array($post['hero_btn'])) {
        return $existing_json;
    }
    $out = [];
    foreach (['btn1' => 1, 'btn2' => 2] as $key => $n) {
        $c = is_array($post['hero_btn'][$key] ?? null) ? $post['hero_btn'][$key] : [];
        $raw_url = trim((string) ($c['url'] ?? ''));
        $url = section_cta_safe_url($raw_url);
        if ($raw_url !== '' && $url === '') {
            flash('error', 'The link for hero button ' . $n . ' was not saved: it must start with /, #, http://, https://, mailto: or tel:.');
        }
        $out[$key] = [
            'text'    => trim(strip_tags((string) ($c['text'] ?? ''))),
            'url'     => $url,
            'new_tab' => !empty($c['new_tab']) ? 1 : 0,
            'hide'    => !empty($c['hide']) ? 1 : 0,
        ];
    }
    return json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

/**
 * JSON for a Success Story's `final_cta_buttons_json` from the posted final_cta_btn[...] fields: an extra
 * "walkthrough" button (text/url/new_tab) shown with the phone + email, and the contact form's Send / Reset
 * labels (hide_reset hides Reset). Not posting final_cta_btn at all (a form opened before these existed)
 * keeps the saved value. Unsafe links are dropped with a flash.
 */
function ss_final_cta_buttons_from_post(array $post, ?string $existing_json): ?string
{
    if (!isset($post['final_cta_btn']) || !is_array($post['final_cta_btn'])) {
        return $existing_json;
    }
    $p = $post['final_cta_btn'];
    $raw_url = trim((string) ($p['walk_url'] ?? ''));
    $url = section_cta_safe_url($raw_url);
    if ($raw_url !== '' && $url === '') {
        flash('error', 'The walkthrough button link was not saved: it must start with /, #, http://, https://, mailto: or tel:.');
    }
    return json_encode([
        'walk' => [
            'text'    => trim(strip_tags((string) ($p['walk_text'] ?? ''))),
            'url'     => $url,
            'new_tab' => !empty($p['walk_new_tab']) ? 1 : 0,
        ],
        'send_text'  => trim(strip_tags((string) ($p['send_text'] ?? ''))),
        'reset_text' => trim(strip_tags((string) ($p['reset_text'] ?? ''))),
        'hide_reset' => !empty($p['hide_reset']) ? 1 : 0,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

/**
 * Cleaned posted value for a page's `cs_story_ids_json` column (max 4 IDs).
 */
function svc_case_study_ids_from_post(array $post): string
{
    return json_encode(array_slice(
        array_values(array_unique(array_filter(array_map('intval', $post['cs_story_ids'] ?? [])))),
        0, 4
    ));
}

/**
 * Normalize a *_schema_json-style column for the edit form's repeater: rows
 * may already be the new [{type, label, code}, ...] shape (type is optional
 * — only the Pages module's dropdown uses it), or (pre-existing rows) the
 * old shape where the whole column held one raw JSON-LD string — wrap that
 * as a single unlabeled block so old data still shows up and round-trips
 * as-is instead of being lost.
 */
function svc_normalize_schemas(?string $raw): array
{
    if (!$raw) { return []; }
    $decoded = json_decode($raw, true);
    if (is_array($decoded) && array_is_list($decoded)) {
        $looksNew = true;
        foreach ($decoded as $item) {
            if (!is_array($item) || !array_key_exists('code', $item)) { $looksNew = false; break; }
        }
        if ($looksNew) {
            return array_map(static function ($item) {
                $code = $item['code'] ?? '';
                return [
                    'type' => (string) ($item['type'] ?? ''),
                    'label' => (string) ($item['label'] ?? ''),
                    'code' => is_string($code) ? $code : json_encode($code, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                ];
            }, $decoded);
        }
    }
    return [['type' => '', 'label' => '', 'code' => $raw]];
}

/**
 * Echo the shared repeater scaffold: an empty rows container, an "add"
 * button, a <template> holding one row's markup, and a JSON script tag with
 * the existing data — all consumed by repeater.js's
 * initRepeater()/populateRepeaterRow() on the JS side. Also stashes the
 * live repeater instance on window.svcRepeaters[id] so page-specific script
 * (e.g. a per-row "Generate" button) can push/inspect rows after the fact.
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
        window.svcRepeaters = window.svcRepeaters || {};
        window.svcRepeaters['<?= $id ?>'] = repeater;
        data.forEach(function (item) { repeater.addRow(item); });
    })();
    </script>
    <?php
}
