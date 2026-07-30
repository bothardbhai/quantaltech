/**
 * Generic repeater widget — one shared implementation reused for every
 * object-array field in the Service Master (impact stats, benefit cards,
 * industries, engagement models, ...) instead of hand-rolling add/remove/
 * reorder logic per section the way blog.php's FAQ repeater originally did.
 *
 * Row markup convention (uses the existing .repeater-row* classes already
 * defined in admin.css for blog's FAQ repeater):
 *   <div class="repeater-row">
 *     <div class="repeater-row__head">
 *       <span class="repeater-row__title">...</span>
 *       <div class="repeater-row__actions">
 *         <button type="button" data-repeater-action="up">&uarr;</button>
 *         <button type="button" data-repeater-action="down">&darr;</button>
 *         <button type="button" data-repeater-action="remove">&times;</button>
 *       </div>
 *     </div>
 *     ...fields, named e.g. name="impact_stats[][number]"...
 *   </div>
 *
 * Fields use unindexed array syntax (name="foo[][bar]") deliberately: PHP
 * builds that array from the POST body in DOM order, so reordering/removing
 * rows in the browser "just works" with no re-indexing logic needed here.
 *
 * @param {Object} opts
 * @param {HTMLElement} opts.container - element the rows live in
 * @param {HTMLElement} [opts.emptyEl] - "no rows yet" placeholder, toggled automatically
 * @param {HTMLElement} opts.addBtn - the "+ Add" button
 * @param {HTMLTemplateElement} opts.template - <template> holding one row's markup
 * @param {function(HTMLElement, any=)} [opts.onAdd] - called with the new row
 *        element and (if adding from existing data) that row's data object,
 *        so the caller can populate fields / init icon pickers / etc.
 * @returns {{ addRow: function(any=): HTMLElement }}
 */
function initRepeater(opts) {
    const { container, emptyEl, addBtn, template, onAdd } = opts;
    if (!container || !addBtn || !template) {
        return { addRow: function () { return null; } };
    }

    function updateEmptyState() {
        if (emptyEl) {
            emptyEl.style.display = container.children.length ? 'none' : '';
        }
    }

    function addRow(data) {
        const frag = template.content.cloneNode(true);
        container.appendChild(frag);
        const row = container.lastElementChild;
        if (typeof onAdd === 'function') {
            onAdd(row, data);
        }
        updateEmptyState();
        return row;
    }

    addBtn.addEventListener('click', function () {
        addRow();
    });

    container.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-repeater-action]');
        if (!btn) return;
        const row = btn.closest('.repeater-row');
        if (!row) return;
        const action = btn.dataset.repeaterAction;
        if (action === 'remove') {
            row.remove();
            updateEmptyState();
        } else if (action === 'up') {
            const prev = row.previousElementSibling;
            if (prev) container.insertBefore(row, prev);
        } else if (action === 'down') {
            const next = row.nextElementSibling;
            if (next) container.insertBefore(next, row);
        }
    });

    updateEmptyState();
    return { addRow: addRow };
}

/**
 * Fill a freshly-added repeater row's fields from a plain data object.
 * fieldMap maps a CSS selector (scoped to the row) to either a data key
 * (plain text/textarea field) or { key, type } where type is
 * 'list' (textarea, one item per line, from an array of strings),
 * 'checkbox', or 'icon' (also wires up the icon picker).
 */
function populateRepeaterRow(row, data, fieldMap) {
    if (!data) return;
    Object.keys(fieldMap).forEach(function (selector) {
        const el = row.querySelector(selector);
        if (!el) return;
        const spec = fieldMap[selector];
        const key = typeof spec === 'string' ? spec : spec.key;
        const type = typeof spec === 'string' ? 'text' : (spec.type || 'text');
        const val = data[key];

        if (type === 'list') {
            el.value = Array.isArray(val) ? val.join('\n') : '';
        } else if (type === 'checkbox') {
            el.checked = !!val;
        } else {
            el.value = val || '';
        }
        if (type === 'icon' && window.initIconPicker) {
            window.initIconPicker(el);
        }
    });
}
