# Database Setup & Migration Guide

## Overview

This project uses automatic database migrations to set up and manage the database schema. Migrations are SQL files stored in `/db/migrations/` that are executed in order to build the database structure.

## Migration Files

The migrations are organized by creation date and purpose:

1. **2025-05-11-001-create-webinars-table.sql**
   - Creates the `webinars` table for managing webinar events
   - Includes speaker information, scheduling, registration tracking
   - SEO fields for per-webinar optimization

2. **2025-05-11-002-create-success-stories-table.sql**
   - Creates the `success_stories` table for case studies
   - Includes company info, challenge/solution/results sections
   - Client testimonial support, featured flag for homepage

3. **2025-05-11-003-create-services-table.sql**
   - Creates the `services` table for AI service offerings
   - Pre-populates with the 4 core services (Voice, Text, Image/Document, Process Automation)
   - Flexible features storage via JSON

4. **2026-08-07-001-create-hire-pages-table.sql**
   - Creates the `hire_pages` table — the Hire Master, mirrors the `services` table's section-based shape
   - Each row is one hire-able role page, served at `/hire-ai-engineers/{slug}`
   - **2026-08-07-002-add-hire-media-category.sql** adds a `hire` value to `media.category` alongside it

5. **2026-08-12-001-create-success-story-categories-table.sql**
   - Creates the `success_story_categories` table — the filter/taxonomy master for Success Stories (Legal Recruitment, HR & Recruitment, etc.)

6. **2026-08-12-002-extend-success-stories-table.sql**
   - Extends `success_stories` with the same section-based shape as `services`/`hire_pages`: category, breadcrumb, sort order, and one repeater/triplet per detail-page section (Objectives, Architecture, Workflow, Results, Deliverables, Technology Stack, Why Choose, Client Responsibilities, Future Enhancements, Related Stories, Final CTA) plus `canonical`/`robots` for SEO parity
   - **2026-08-12-003-add-success-story-media-category.sql** adds a `success-story` value to `media.category` alongside it
   - `related_story_ids_json`/`related_count` are no longer read or written by admin/success-stories.php — "More Success Stories" now always shows the 4 most recent published stories excluding the current one. The columns were left in place (not dropped) in case any row already has data in them.

7. **2026-08-12-004-add-success-story-why-final-content.sql**
   - Adds `why_final_html` to `success_stories` — one optional closing paragraph rendered after all "Why Choose Our Solution" cards, no box/card, plain content
   - `workflow_json` rows gained an optional `items` key (bullet points shown under a step's description) — no migration needed since it's a JSON column; existing rows without `items` keep working unchanged

## Running Migrations

### Automatic (Recommended)

Migrations run automatically when you first access the admin panel or include the migration runner.

### Manual - Using PHP

Add this to your setup script:

```php
require_once __DIR__ . '/db/migrate.php';

$result = run_migrations(DB_MIGRATIONS_DIR);
if ($result['success']) {
    echo "Migrations successful: " . $result['message'];
} else {
    echo "Migration error: " . $result['message'];
}
```

### Manual - Using MySQL CLI

```bash
mysql -u <user> -p <database> < db/schema.sql
mysql -u <user> -p <database> < db/migrations/2025-05-11-001-create-webinars-table.sql
mysql -u <user> -p <database> < db/migrations/2025-05-11-002-create-success-stories-table.sql
mysql -u <user> -p <database> < db/migrations/2025-05-11-003-create-services-table.sql
```

### Manual - Using phpMyAdmin

1. Go to Import tab
2. Select each SQL file from `/db/migrations/`
3. Click Go/Import

## Database Tables

### webinars

Stores webinar event information.

| Column | Type | Notes |
|--------|------|-------|
| id | INT UNSIGNED | Primary Key |
| slug | VARCHAR(200) | Unique URL slug |
| title | VARCHAR(255) | Webinar title |
| excerpt | TEXT | Short description |
| description | MEDIUMTEXT | Full description |
| speaker_name | VARCHAR(120) | Speaker's name |
| speaker_title | VARCHAR(120) | Speaker's job title |
| speaker_bio | TEXT | Speaker biography |
| speaker_image | VARCHAR(500) | URL to speaker photo |
| scheduled_at | DATETIME | Event date/time |
| duration_minutes | INT UNSIGNED | Event length |
| timezone | VARCHAR(50) | Event timezone |
| registration_url | VARCHAR(500) | External registration link |
| max_attendees | INT UNSIGNED | Optional capacity |
| registered_count | INT UNSIGNED | Count of registrations |
| status | ENUM | draft, published, archived, completed, cancelled |
| published_at | DATETIME | When the webinar was published |

### success_stories

Stores case study and success story information.

| Column | Type | Notes |
|--------|------|-------|
| id | INT UNSIGNED | Primary Key |
| slug | VARCHAR(200) | Unique URL slug |
| title | VARCHAR(255) | Story title |
| excerpt | TEXT | Short summary |
| body_html | MEDIUMTEXT | Full case study content |
| company_name | VARCHAR(120) | Client company name |
| company_website | VARCHAR(255) | Client website URL |
| company_logo | VARCHAR(500) | Client logo URL |
| industry | VARCHAR(100) | Industry/vertical |
| company_size | VARCHAR(50) | Employee count or size descriptor |
| challenge_html | MEDIUMTEXT | The challenge they faced |
| solution_html | MEDIUMTEXT | How we solved it |
| results_html | MEDIUMTEXT | Results and impact (legacy free-text; superseded by `results_json` for the redesigned detail page) |
| featured | TINYINT(1) | Show on homepage — exactly one row should be featured at a time (enforced by admin/success-stories.php on save) |
| status | ENUM | draft, published, archived |
| category_id | INT UNSIGNED | FK -> `success_story_categories.id`, nullable |
| sort_order | INT UNSIGNED | Listing/hub display order |
| page_label, crumb | VARCHAR | Breadcrumb (mirrors services/hire_pages) |
| hero_eyebrow | VARCHAR(150) | Optional hero subtitle |
| services_provided, tech_stack_summary, third_party_services, outcome_summary | VARCHAR | Plain info-panel fields (Industry reuses the existing `industry` column) |
| objectives_json | JSON | `[{title, desc, active}]` |
| architecture_json | JSON | `[{icon, title, subtitle, items:[], active}]` |
| challenge_sub, challenge_title, challenge_image | VARCHAR | Challenge section heading/image (body copy stays in `challenge_html`) |
| solution_sub, solution_title, solution_image | VARCHAR | Solution section heading/image (body copy stays in `solution_html`) |
| workflow_json | JSON | `[{title, desc, active}]` |
| results_json | JSON | `[{title, desc, active}]` |
| deliverables_json | JSON | `[{icon, title, desc, active}]` |
| tech_stack_items_json | JSON | `[{icon, name, purpose, active}]` |
| why_cards_json | JSON | `[{title, desc, active}]` |
| responsibilities_sub, responsibilities_title, responsibilities_text, responsibilities_json | VARCHAR/JSON | Client Responsibilities section |
| future_sub, future_title, future_text, future_json | VARCHAR/JSON | Future Enhancements section |
| related_story_ids_json, related_count | JSON/TINYINT | Manual "More Case Studies" picks + auto-fill count |
| final_cta_sub, final_cta_title, final_cta_desc | VARCHAR/TEXT | Final CTA band copy (pairs with the existing Contact Form) |
| canonical, robots | VARCHAR | SEO parity with services/hire_pages |

### services

Stores AI service offering details.

| Column | Type | Notes |
|--------|------|-------|
| id | INT UNSIGNED | Primary Key |
| slug | VARCHAR(120) | Unique URL slug |
| name | VARCHAR(120) | Service name |
| title | VARCHAR(255) | Display title |
| excerpt | TEXT | Short description |
| description | MEDIUMTEXT | Full description |
| service_number | INT UNSIGNED | Display order |
| features_json | JSON | Array of feature objects |
| status | ENUM | draft, published, archived |
| display_on_home | TINYINT(1) | Show on homepage |
| case_studies_json | JSON | **Deprecated** — no longer read or written by admin/services.php. The Service Detail page's "Case Studies" section now always shows the latest 4 published Success Stories (see `success_stories`), the single source of truth. Column left in place, not dropped, so any previously entered data isn't lost. |

### hire_pages

Stores Hire Master role pages (the Service Master's counterpart for `/hire-ai-engineers/{slug}`).

| Column | Type | Notes |
|--------|------|-------|
| id | INT UNSIGNED | Primary Key |
| slug | VARCHAR(120) | Unique URL slug, e.g. `python-developer` |
| name | VARCHAR(120) | Internal/role name |
| title | VARCHAR(255) | Display title |
| excerpt | TEXT | Short description (hub card + SEO fallback) |
| sort_order | INT UNSIGNED | Display order on the `/hire-ai-engineers` hub |
| hero_features_json, impact_stats_json, expertise_cards_json, build_cards_json, engagement_models_json, why_cards_json, industries_json, faqs_json | JSON | One repeater column per page section |
| case_studies_json | JSON | **Deprecated** — no longer read or written by admin/hire.php. The Hire Detail page's "Case Studies" section now always shows the latest 4 published Success Stories (see `success_stories`), the single source of truth. Column left in place, not dropped. |
| related_hire_ids_json | JSON | int[] of other `hire_pages.id` |
| related_service_ids_json | JSON | int[] of `services.id` |
| blog_post_ids_json | JSON | int[] of `posts.id` |
| status | ENUM | draft, published, archived |
| display_on_hub | TINYINT(1) | Show as a card on the `/hire-ai-engineers` hub |

### success_story_categories

Filter/taxonomy master for Success Stories.

| Column | Type | Notes |
|--------|------|-------|
| id | INT UNSIGNED | Primary Key |
| name | VARCHAR(100) | Display name |
| slug | VARCHAR(120) | Unique, used for the frontend filter bar's `data-filter`/`mix` classes |
| status | ENUM | active, inactive |
| sort_order | INT UNSIGNED | Display order |

## Helper Functions

All helper functions are in `/core/content-helpers.php`:

### Webinars

```php
get_webinars($pdo, ['status' => 'published', 'order' => 'scheduled_at DESC']);
get_webinar($pdo, 123); // by ID
get_webinar($pdo, 'webinar-slug'); // by slug
save_webinar($pdo, $data);
delete_webinar($pdo, $id);
```

### Success Stories

```php
get_success_stories($pdo, ['status' => 'published', 'featured_only' => true, 'category_id' => 3, 'exclude_id' => 12]);
get_success_story($pdo, 123);
get_success_story($pdo, 'case-study-slug');
save_success_story($pdo, $data);
delete_success_story($pdo, $id);
get_success_stories_by_ids($pdo, [3, 7, 9]);
get_success_story_categories($pdo, ['status' => 'active']);
```

### Services

```php
get_services($pdo, ['display_on_home' => true]);
get_service($pdo, 123);
get_service($pdo, 'voice-ai');
save_service($pdo, $data);
delete_service($pdo, $id);
```

### Hire Pages

```php
get_hire_pages($pdo, ['status' => 'published']);
get_hire_page($pdo, 123);
get_hire_page($pdo, 'python-developer');
save_hire_page($pdo, $data);
delete_hire_page($pdo, $id);
get_hire_pages_by_ids($pdo, [3, 7, 9]);
```

## Admin Pages

After migrations run, you'll have three new admin management pages:

- `/admin/webinars.php` - Manage webinars
- `/admin/success-stories.php` - Manage case studies
- `/admin/success-story-categories.php` - Manage Success Stories categories/filters
- `/admin/services.php` - Manage service offerings
- `/admin/hire.php` - Manage Hire Master role pages

## Frontend Usage

### Webinars Page

```php
require_once CORE_DIR . '/content-helpers.php';

$pdo = db();
$webinars = get_webinars($pdo, [
    'published_only' => true,
    'order' => 'scheduled_at ASC'
]);

// Now available at /webinar
```

### Success Stories Page

```php
$stories = get_success_stories($pdo, [
    'published_only' => true,
    'featured_only' => true
]);

// Available at /success-stories
```

### Services

```php
$services = get_services($pdo, [
    'status' => 'published',
    'display_on_home' => true
]);

// Available at /services/{slug}
```

## Troubleshooting

### Migration not running automatically

Add this to your `/admin/bootstrap.php` or main entry point:

```php
require_once DB_DIR . '/migrate.php';
if (php_sapi_name() !== 'cli') {
    $migration_status = get_migration_status(DB_MIGRATIONS_DIR);
    if ($migration_status['pending_migrations'] > 0) {
        $result = run_migrations(DB_MIGRATIONS_DIR);
        // Log result or display admin notification
    }
}
```

### Table already exists error

If you get "Table already exists" error, it's safe to ignore. The migrations use `CREATE TABLE IF NOT EXISTS` to be idempotent.

### Foreign key constraint error

Ensure all tables are created in the correct order:
1. `services` (has no dependencies)
2. `webinars` (depends on `users`)
3. `success_stories` (depends on `users`)

The migrations handle this automatically.

## Backup Before Migration

Before running migrations on production:

```bash
mysqldump -u <user> -p <database> > backup-before-migration.sql
```

Then run migrations.
