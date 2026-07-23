# Site search

Live search (autocomplete) in the header, backed by `/api/search.php`. Also
powers `/search`, the full-results page the search form falls back to when
JavaScript is unavailable.

## How it works

- **Frontend:** `assets/js/live-search.js` debounces keystrokes (280ms, 2+
  chars) and calls `/api/search.php?q=...`, rendering results into the
  `#site-search-results` dropdown added to `partials/header.php`. Styling
  lives in `assets/css/live-search.css`.
- **Backend:** `core/search.php` holds all the matching logic
  (`search_site()`); `api/search.php` is the thin JSON endpoint, and
  `pages/search.php` is the full HTML results page — both just call
  `search_site()`.
- All DB access uses prepared statements with positional placeholders
  (required — `PDO::ATTR_EMULATE_PREPARES` is off, see `core/db.php`), and
  `%`/`_` in the user's query are escaped before being wrapped in a `LIKE`
  pattern. User input is never concatenated into SQL or echoed into HTML
  unescaped.

Search covers two kinds of content:

1. **Statically-routed pages** — about, contact, services/*, etc. Found
   automatically via `router_discover_pages()` (the same function the
   admin's page picker uses), so a new file dropped into `/pages` is
   searchable immediately with no extra step. Title/description come from
   the `pages` table (admin → Pages) when set, otherwise from the label map
   in `search_static_page_labels()`, otherwise a title-cased guess from the
   filename.
2. **Database content** — blog posts (`posts`), success stories /
   case studies (`success_stories`), and podcasts (`webinars` — see the
   comment in `pages/podcast/index.php` for why podcasts live in that
   table). Each has its own `search_*()` function in `core/search.php`
   querying the columns relevant to that content type (title, slug,
   excerpt, rich-text body, meta fields). Only `status = 'published'` rows
   are matched.

Results are scored (title match > slug > description > full-body match,
with a bonus for a match at the very start of the field), merged, and
sorted; the top N are returned.

## Adding future content

- **New static page** (`/pages/whatever.php`): nothing required — it shows
  up in search on its next request. Optionally add an entry to
  `search_static_page_labels()` in `core/search.php` for a nicer title/type
  than the auto-generated one, or fill in the page's SEO row in the admin
  so the real meta title/description are used and searchable.
- **New blog post / success story / podcast**: nothing required — publish
  it (`status = 'published'`) and it's searchable on the next request.
- **A genuinely new content type** (e.g. a `case_studies` table distinct
  from success stories): copy the shape of `search_success_stories()` in
  `core/search.php`, add a `'case_study'` entry to the label map in
  `search_type_label()`, and add the new function's results to the
  `array_merge()` call inside `search_site()`.
- **File that exists under `/pages` but isn't a real page** (a POST-only
  handler, a stray backup/copy file, etc.): add its path to
  `search_excluded_paths()` in `core/search.php` — this is also where
  `/404`, `/newsletter`, `/contact-submit`, `/thank-you`, and `/search`
  itself are excluded today.

## Known limitation

Static marketing pages are matched on title + slug + SEO meta fields, not
their full rendered HTML body — the templates are large hand-built theme
markup, not structured content, so scraping them reliably (and quickly, on
every keystroke) isn't practical. Blog posts, success stories, and podcasts
*do* get full body-content search since those come from structured DB
columns. If a static page needs to be found by body text, the practical
fix is to fill in its `meta_description`/`meta_keywords` in the admin
Pages screen rather than teaching search to parse HTML.
