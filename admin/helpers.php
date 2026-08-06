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
