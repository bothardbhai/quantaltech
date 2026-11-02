/**
 * Services mega menu — tab switching + panel positioning.
 *
 * Markup: partials/nav-menu.php → render_mega_nav_item(). Styles:
 * css/mega-menu.css. script.js clones the desktop nav into the sticky header
 * (and mobile drawer), so every listener here is delegated from `document`
 * and works on all copies.
 *
 * - Hovering, clicking or focusing a subcategory tab shows its services
 *   (tabs are <button>s; they never navigate).
 * - ArrowUp/ArrowDown move between tabs.
 * - On open, the panel is shifted so it sits centred in the viewport instead
 *   of hanging off the "Services" item; on close it resets to the first tab.
 */
(function () {
    'use strict';

    function activate(tab) {
        var mega = tab.closest('.qt-mega');
        if (!mega || tab.classList.contains('is-active')) return;
        var key = tab.getAttribute('data-mega-tab');

        mega.querySelectorAll('.qt-mega__tab').forEach(function (t) {
            var on = t === tab;
            t.classList.toggle('is-active', on);
            t.setAttribute('aria-selected', on ? 'true' : 'false');
            t.setAttribute('tabindex', on ? '0' : '-1');
        });
        mega.querySelectorAll('.qt-mega__panel').forEach(function (p) {
            p.classList.toggle('is-active', p.getAttribute('data-mega-panel') === key);
        });
    }

    function position(li) {
        var mega = li.querySelector(':scope > .qt-mega');
        if (!mega) return;
        var liLeft = li.getBoundingClientRect().left;
        var width = mega.offsetWidth;
        var vw = document.documentElement.clientWidth;
        var target = Math.max(20, (vw - width) / 2);
        mega.style.left = Math.round(target - liLeft) + 'px';
    }

    function reset(li) {
        var first = li.querySelector('.qt-mega__tab');
        if (first) activate(first);
    }

    function megaItemFrom(target) {
        return target && target.closest ? target.closest('.main-menu li.has-mega') : null;
    }

    // Tabs: hover / click / keyboard focus
    document.addEventListener('mouseover', function (e) {
        var tab = e.target.closest && e.target.closest('.qt-mega__tab');
        if (tab) activate(tab);

        var li = megaItemFrom(e.target);
        if (li && !li.contains(e.relatedTarget)) position(li);
    });

    document.addEventListener('mouseout', function (e) {
        var li = megaItemFrom(e.target);
        if (li && !li.contains(e.relatedTarget)) reset(li);
    });

    document.addEventListener('click', function (e) {
        var tab = e.target.closest && e.target.closest('.qt-mega__tab');
        if (tab) {
            e.preventDefault();
            activate(tab);
        }
    });

    document.addEventListener('focusin', function (e) {
        var li = megaItemFrom(e.target);
        if (li) position(li);
        var tab = e.target.closest && e.target.closest('.qt-mega__tab');
        if (tab) activate(tab);
    });

    document.addEventListener('keydown', function (e) {
        if (e.key !== 'ArrowDown' && e.key !== 'ArrowUp') return;
        var tab = e.target.closest && e.target.closest('.qt-mega__tab');
        if (!tab) return;
        var tabs = Array.prototype.slice.call(tab.parentNode.querySelectorAll('.qt-mega__tab'));
        var i = tabs.indexOf(tab) + (e.key === 'ArrowDown' ? 1 : -1);
        if (i < 0 || i >= tabs.length) return;
        e.preventDefault();
        tabs[i].focus();
    });

    // Pre-position on load (incl. the sticky-header clone script.js creates)
    // and keep it centred on resize.
    function positionAll() {
        document.querySelectorAll('.main-menu li.has-mega').forEach(position);
    }
    window.addEventListener('load', positionAll);
    window.addEventListener('resize', positionAll);
})();
