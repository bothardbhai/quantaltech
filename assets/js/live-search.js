/**
 * Live search (autocomplete) for the header search popup.
 *
 * Progressive enhancement over the existing <form id="site-search-form">
 * (partials/header.php) which still works standalone (GET /search) with
 * JS disabled. Hits /api/search.php (see api/search.php + core/search.php).
 */
(function () {
    'use strict';

    var DEBOUNCE_MS = 280;
    var MIN_CHARS = 2;

    var form = document.getElementById('site-search-form');
    var input = document.getElementById('site-search-input');
    var resultsBox = document.getElementById('site-search-results');
    if (!form || !input || !resultsBox || !window.fetch) {
        return;
    }

    var apiUrl = window.QUANTAL_SEARCH_API || '/api/search.php';
    var debounceTimer = null;
    var activeRequest = null;
    var activeIndex = -1;
    var currentItems = [];

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function escapeRegExp(str) {
        return String(str).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    /** Wrap the matched substring in <mark>, escaping everything else. */
    function highlight(text, query) {
        var safeText = escapeHtml(text);
        var q = query.trim();
        if (!q) {
            return safeText;
        }
        var re = new RegExp('(' + escapeRegExp(escapeHtml(q)) + ')', 'ig');
        return safeText.replace(re, '<mark>$1</mark>');
    }

    function closeResults() {
        resultsBox.hidden = true;
        resultsBox.innerHTML = '';
        input.setAttribute('aria-expanded', 'false');
        activeIndex = -1;
        currentItems = [];
    }

    function renderMessage(text) {
        resultsBox.innerHTML = '<div class="site-search-empty">' + escapeHtml(text) + '</div>';
        resultsBox.hidden = false;
        input.setAttribute('aria-expanded', 'true');
    }

    function renderResults(query, results) {
        if (!results.length) {
            renderMessage('No results found.');
            return;
        }

        var html = results.map(function (item, i) {
            return '' +
                '<a href="' + escapeHtml(item.url) + '" class="site-search-result" role="option" data-index="' + i + '">' +
                    '<span class="site-search-result-type">' + escapeHtml(item.type_label) + '</span>' +
                    '<span class="site-search-result-title">' + highlight(item.title, query) + '</span>' +
                    (item.excerpt ? '<span class="site-search-result-excerpt">' + highlight(item.excerpt, query) + '</span>' : '') +
                '</a>';
        }).join('');

        resultsBox.innerHTML = html;
        resultsBox.hidden = false;
        input.setAttribute('aria-expanded', 'true');
        activeIndex = -1;
        currentItems = Array.prototype.slice.call(resultsBox.querySelectorAll('.site-search-result'));
    }

    function runSearch(query) {
        if (activeRequest) {
            activeRequest.abort();
        }

        var controller = ('AbortController' in window) ? new AbortController() : null;
        activeRequest = controller;

        fetch(apiUrl + '?q=' + encodeURIComponent(query), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            signal: controller ? controller.signal : undefined
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                activeRequest = null;
                if (input.value.trim() !== query) {
                    return; // stale response for an outdated keystroke
                }
                renderResults(query, data.results || []);
            })
            .catch(function (err) {
                activeRequest = null;
                if (err && err.name === 'AbortError') {
                    return;
                }
                renderMessage('No results found.');
            });
    }

    function onInput() {
        var query = input.value.trim();

        clearTimeout(debounceTimer);

        if (query.length < MIN_CHARS) {
            closeResults();
            return;
        }

        debounceTimer = setTimeout(function () {
            runSearch(query);
        }, DEBOUNCE_MS);
    }

    function moveActive(delta) {
        if (!currentItems.length) {
            return;
        }
        if (activeIndex >= 0) {
            currentItems[activeIndex].classList.remove('is-active');
        }
        activeIndex = (activeIndex + delta + currentItems.length) % currentItems.length;
        currentItems[activeIndex].classList.add('is-active');
        currentItems[activeIndex].scrollIntoView({ block: 'nearest' });
    }

    input.addEventListener('input', onInput);

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeResults();
            return;
        }
        if (e.key === 'ArrowDown' && !resultsBox.hidden) {
            e.preventDefault();
            moveActive(1);
        } else if (e.key === 'ArrowUp' && !resultsBox.hidden) {
            e.preventDefault();
            moveActive(-1);
        } else if (e.key === 'Enter' && activeIndex >= 0 && currentItems[activeIndex]) {
            e.preventDefault();
            window.location.href = currentItems[activeIndex].href;
        }
    });

    document.addEventListener('click', function (e) {
        if (!form.contains(e.target) && !resultsBox.contains(e.target)) {
            closeResults();
        }
    });

    // Close the dropdown whenever the search popup itself closes.
    document.addEventListener('click', function (e) {
        if (e.target.closest('.close-search, .search-back-drop')) {
            closeResults();
            input.value = '';
        }
    });
})();
