/**
 * Icon picker — pairs a plain text input (which still just stores the Font
 * Awesome class, e.g. "fas fa-brain", same as before) with a small preview
 * icon and a "Choose…" button that opens a searchable popover. The icon list
 * (admin/assets/js/fa-icons.js, loaded before this file, sets window.FA_ICONS)
 * was generated once from the site's actual bundled Font Awesome CSS.
 * Shipped as a .js file rather than .json: the site's .htaccess denies all
 * *.json requests as a blanket security rule (protects config-like files),
 * which would otherwise 403 a same-named JSON asset too.
 *
 * Usage: initIconPicker(document.querySelector('input.icon-input'));
 * Safe to call more than once on the same input (no-ops after the first).
 */
(function () {
    'use strict';

    function loadIcons() {
        return Promise.resolve(Array.isArray(window.FA_ICONS) ? window.FA_ICONS : []);
    }

    function renderGrid(grid, list, onPick) {
        grid.innerHTML = '';
        list.slice(0, 300).forEach(function (icon) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'icon-picker-item';
            btn.title = icon.class;
            btn.innerHTML = '<i class="' + icon.class + '"></i>';
            btn.addEventListener('click', function () { onPick(icon.class); });
            grid.appendChild(btn);
        });
        if (!list.length) {
            grid.innerHTML = '<div class="icon-picker-empty">No icons match.</div>';
        }
    }

    window.initIconPicker = function (input) {
        if (!input || input.dataset.iconPickerInit) {
            return;
        }
        input.dataset.iconPickerInit = '1';

        const wrap = document.createElement('div');
        wrap.className = 'icon-picker';
        input.parentNode.insertBefore(wrap, input);
        wrap.appendChild(input);

        const preview = document.createElement('span');
        preview.className = 'icon-picker-preview';
        function updatePreview() {
            preview.innerHTML = input.value.trim() ? '<i class="' + input.value.trim() + '"></i>' : '';
        }
        updatePreview();
        wrap.appendChild(preview);
        input.addEventListener('input', updatePreview);

        const chooseBtn = document.createElement('button');
        chooseBtn.type = 'button';
        chooseBtn.className = 'admin-btn admin-btn--ghost admin-btn--small icon-picker-choose';
        chooseBtn.textContent = 'Choose…';
        wrap.appendChild(chooseBtn);

        let pop = null;

        function closePopover() {
            if (pop) {
                pop.remove();
                pop = null;
            }
        }

        function pick(cls) {
            input.value = cls;
            updatePreview();
            input.dispatchEvent(new Event('change', { bubbles: true }));
            closePopover();
        }

        chooseBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            if (pop) {
                closePopover();
                return;
            }
            pop = document.createElement('div');
            pop.className = 'icon-picker-popover';
            pop.innerHTML =
                '<input type="text" class="icon-picker-search" placeholder="Search icons…">' +
                '<div class="icon-picker-grid"></div>';
            wrap.appendChild(pop);

            const grid = pop.querySelector('.icon-picker-grid');
            const search = pop.querySelector('.icon-picker-search');

            loadIcons().then(function (icons) {
                renderGrid(grid, icons, pick);
                search.addEventListener('input', function () {
                    const q = search.value.trim().toLowerCase();
                    // String(): a handful of icon names are pure digits (fa-0..fa-9),
                    // which PHP's array-key coercion turns into JSON numbers rather
                    // than strings — .indexOf would throw on those without this.
                    const filtered = q ? icons.filter(function (i) { return String(i.name).indexOf(q) !== -1; }) : icons;
                    renderGrid(grid, filtered, pick);
                });
            });
            search.focus();
        });

        document.addEventListener('click', function (e) {
            if (pop && !wrap.contains(e.target)) {
                closePopover();
            }
        });
    };
})();
