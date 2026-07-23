# Quantal AI — PHP rebuild

PHP rebuild of `quantaltech.ai` using the eDigitaal theme, with file-path-based routing and DB-driven SEO.

**Status: Stages 1, 2, and 3 complete.**

- **Stage 1** — file-path router with underscore-prefixed-segment rejection, .htaccess, header/footer/seo/nav partials, theme assets, DB schema (9 tables).
- **Stage 2** — full admin at `/admin/` with login, dashboard, Pages & SEO, Blog (CKEditor 5), Media library, Settings — verified end-to-end.
- **Stage 3** — Quantal content ported into the eDigitaal theme. Every public page is built from theme markup with surgical content/image swaps; theme animations (WOW fadeIn / GSAP / split-text / float-bob / counters / marquees / swipers) and decorative shapes are preserved everywhere. Pages: home, about, services index + 4 detail pages (voice/text/image/process-auto), hire, contact, blog, terms-of-service, refund-policy. Real Quantal logo, founders, partners, clients, case-study images, full contact info.

- **Stage 1** — file-path router, .htaccess, header/footer/seo/nav partials, 21 theme pages converted, DB schema (9 tables)
- **Stage 2** — full admin at `/admin/` with login, dashboard, Pages & SEO, Blog (CKEditor 5), Media library, Settings — verified end-to-end
- **Stage 3** — Quantal content ported into the theme. The eDigitaal home keeps every section, animation (WOW fadeIn / GSAP scroll-trigger / split-text / float-bob / counters / marquees / swipers), and decorative shape — only the copy and content imagery are swapped to Quantal. Inner pages (about, services index + 4 sub-services, hire, contact, terms, refund-policy) match the live site copy. Real Quantal logo, founders, partners, clients, case-study images.

- **Stage 1** — file-path router, .htaccess, header/footer/seo/nav partials, 21 theme pages converted, DB schema (9 tables)
- **Stage 2** — full admin at `/admin/` with login, dashboard, Pages & SEO, Blog (CKEditor 5), Media library, Settings — verified end-to-end
- **Stage 3** — content ported from quantaltech.ai. Live pages: home, about, services index + 4 sub-services (voice, text, image, process-auto), hire, contact, blog, terms-of-service, refund-policy. Real Quantal logo, founders, partners, clients, case-study images. Real contact info (contact@quantaltech.ai, +1 315 809 3225, Mumbai office, GSTIN). Theme demo content kept under `pages/alt/_unused/` for reference.

---

## Quick start (local — XAMPP / MAMP)

1. **Drop the project anywhere outside `htdocs`.** For XAMPP, you can use `htdocs/quantal-php/`, but keep in mind the public folder is `public/` — visiting `/quantal-php/public/` works, but cleaner is to set a virtual host pointing at `public/`.

2. **Set up a virtual host** (optional but recommended for clean URLs without the `/public/` prefix). In `httpd-vhosts.conf`:

   ```apache
   <VirtualHost *:80>
       ServerName quantal.local
       DocumentRoot "C:/xampp/htdocs/quantal-php/public"
       <Directory "C:/xampp/htdocs/quantal-php/public">
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

   Add `127.0.0.1 quantal.local` to your hosts file.

3. **Create the database** in phpMyAdmin (or CLI):

   ```sql
   CREATE DATABASE quantal_php CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

   Then import `db/schema.sql` via phpMyAdmin's Import tab, or:

   ```bash
   mysql -u root -p quantal_php < db/schema.sql
   ```

4. **Configure**: copy `config/config.example.php` to `config/config.php` and edit DB credentials and `SITE_URL`.

5. **(Stage 2)** Seed the admin user:

   ```bash
   php db/seed_admin.php admin admin@quantaltech.ai 'YourStrongPassword!'
   ```

6. Visit `http://quantal.local/` — the homepage should render with the eDigitaal theme intact.

> **Note**: PHP's built-in dev server also works for quick testing:
>
> ```bash
> php -S 127.0.0.1:8000 -t public
> ```
>
> The front-controller fallback handles routing automatically; no `.htaccess` needed for the built-in server.

## Quick start (shared cPanel hosting)

1. Upload the project via FTP / File Manager. Place everything **outside** your `public_html` document root, EXCEPT the contents of `public/`, which goes inside `public_html/`.

   Recommended layout on the server:

   ```
   /home/youruser/
       quantal-app/          ← project root (private)
           pages/
           partials/
           core/
           config/
           db/
           admin/
   /home/youruser/public_html/   ← public assets and index.php
       index.php
       .htaccess
       assets/
       uploads/
   ```

2. Edit `index.php` so its path constants point to the right place. The lines to change:

   ```php
   define('ROOT_DIR', '/home/youruser/quantal-app');  // not dirname(__DIR__)
   ```

3. Create the DB in cPanel → MySQL Databases. Import `db/schema.sql` via phpMyAdmin.

4. Update `config/config.php` with credentials.

5. (Stage 2) Run admin seed via cPanel Terminal or temporarily upload a one-off setup script.

## What works in Stages 1 + 2

- Front controller (`public/index.php`) routes URLs to file templates under `pages/`
- `.htaccess` with URL rewriting, security headers, asset caching, HTTPS-ready
- Common header (`partials/header.php`) and footer (`partials/footer.php`) — single edit point for all pages
- Active-nav state per page (driven by `$active_page`)
- Per-page `<title>`, meta description, OG tags, JSON-LD schema (DB-driven, with template fallbacks)
- 21 theme pages converted to PHP templates
- File-path = URL: drop `pages/services/foo.php` → `/services/foo` works automatically
- Blog routes: `/blog` and `/blog/<slug>` rendering DB-backed posts
- DB schema with users, pages, posts, categories, tags, media, settings
- 404 handling
- **Admin panel** at `/admin/`:
  - Login (bcrypt + session + CSRF + brute-force throttle)
  - Dashboard with quick counts
  - Pages & SEO module: auto-discovers all `.php` files in `/pages/`, lets admin attach title/meta description/OG image/canonical/JSON-LD per page, with **Generate WebPage** and **Generate BreadcrumbList** schema helpers
  - Blog module: full CRUD with CKEditor 5 (loaded from CDN), slug auto-gen, draft/published/archived, per-post SEO, **Generate Article schema** helper
  - Image upload endpoint for CKEditor with MIME whitelist + 5MB cap + random filename prefix + media library tracking
  - Media library viewer with copy-path and delete
  - Settings page for site-wide defaults (Organization JSON-LD, default OG image, etc.)

## Folder structure

```
quantal-php/
├── public/                       ← Apache document root
│   ├── index.php                 ← front controller
│   ├── .htaccess                 ← URL rewrites, headers, caching
│   ├── assets/                   ← theme CSS/JS/fonts/images
│   └── uploads/                  ← user uploads (writable)
│       ├── blog/
│       └── pages/
├── pages/                        ← templates; file path = URL
│   ├── home.php                  ← /
│   ├── about.php                 ← /about
│   ├── contact.php               ← /contact
│   ├── services/
│   │   ├── index.php             ← /services
│   │   └── _template.php         ← starting point for /services/<name>.php
│   ├── projects/                 ← (similar)
│   ├── team/                     ← (similar)
│   ├── blog/
│   │   ├── index.php             ← /blog
│   │   └── single.php            ← /blog/<slug> (router fills in $blog_slug)
│   ├── alt/                      ← alternative homepage layouts (theme variants)
│   ├── pricing.php  faq.php  testimonials.php  404.php
├── partials/
│   ├── header.php                ← <head> + nav + chrome (lines 1-216 of theme, parameterized)
│   ├── footer.php                ← footer + 26 theme JS includes + closing tags
│   ├── nav-menu.php              ← data-driven nav with active state
│   └── seo-head.php              ← <title>, meta, OG, JSON-LD
├── core/
│   ├── db.php                    ← PDO singleton, fails gracefully
│   ├── router.php                ← URL → template resolution
│   ├── seo.php                   ← loads metadata from `pages` table
│   └── helpers.php               ← e(), attr(), asset(), url(), jsonld()
├── config/
│   ├── config.example.php        ← copy to config.php
│   └── config.php                ← gitignored
├── db/
│   ├── schema.sql                ← runs in phpMyAdmin
│   └── seed_admin.php            ← Stage 2: creates first admin
└── admin/                        ← Stage 2 builds this
```

## Adding a new page

This is the everyday workflow. To add a new page at `/services/computer-vision`:

1. Create `pages/services/computer-vision.php`. Start by copying `pages/services/_template.php`.
2. Edit the template — set `$page_title` and `$active_page = 'services'` at the top, fill in the content sections.
3. (After Stage 2) In the admin panel, the new page auto-appears under "Pages". Click it and fill in the SEO fields (meta description, OG image, schema).
4. Visit `/services/computer-vision`. Done.

## How the file-path routing works

The router (`core/router.php`) resolves request URLs in this order:

1. `/` → `pages/home.php`
2. `/blog` → `pages/blog/index.php` (DB-driven listing in Stage 2)
3. `/blog/<slug>` → `pages/blog/single.php` (DB-driven single post in Stage 2)
4. `/some/path` → tries `pages/some/path.php`, then `pages/some/path/index.php`
5. No match → `pages/404.php` with HTTP 404

The path fed to step 4 is validated to allow only `[a-z0-9\-_/]` (case-insensitive). Path traversal (`..`) is rejected up front. After resolution, `realpath()` confirms the final template is inside `pages/` — defense in depth.

## How SEO data flows

For every request, the router looks up the `pages` table by the matched URL path. If a row exists, its `title`, `meta_description`, `og_image`, `canonical`, and `schema_json` populate the head. If no row exists (or the DB isn't set up yet), the template's own `$page_title` etc. are used as defaults.

This is what lets the admin manage SEO without touching code: the file lives on disk, but the metadata lives in the DB.

## Theme bugs fixed

- The eDigitaal theme leaves `<div class="page-wrapper">` open without a closing tag. Fixed in `partials/footer.php`.
- The theme has `class="current"` hardcoded on the **Home** menu item across all pages. Fixed via the `$active_page` variable + `partials/nav-menu.php`.
- The theme's HTTrack mirror filled `images/icons/*.svg` with 404 pages. These aren't referenced by any rendered page so they don't affect runtime. Replace if you ever need them.

## What's coming in Stage 3

- **Stage 3** — Port content from your live site (`quantaltech.ai`) into the new templates, section by section, using your existing copy and images. Live-site rendered DOM provides the source of truth for hero copy, services, testimonials, case studies, founders, contact info, and partner logos.
