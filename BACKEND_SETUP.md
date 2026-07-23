# Quantal AI Backend Setup Guide

Complete guide to the database infrastructure, migrations, admin interfaces, and API endpoints for the Quantal AI website.

## Overview

The backend infrastructure consists of:

1. **Database Migrations** — Automated schema creation for content management
2. **Admin Interfaces** — Web UI for managing Services, Webinars, and Success Stories
3. **Content Helpers** — PHP library for CRUD operations
4. **Public API** — JSON endpoints for frontend consumption
5. **Migration System** — Automated, version-controlled database upgrades

## Quick Start

### 1. Database Configuration

Verify `config/config.php` has correct credentials:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'quantal_db');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 2. Run Base Schema

```bash
mysql -u root -p quantal_db < db/schema.sql
```

### 3. Check Migration Status

Open `/admin/setup.php` in your browser. The migration runner will automatically execute any pending migrations.

**Expected output:**
```
✓ Total Migrations: 3
✓ Executed: 3
✓ Pending: 0
```

### 4. Start Managing Content

- Services: `/admin/services.php`
- Webinars: `/admin/webinars.php`
- Success Stories: `/admin/success-stories.php`

## File Structure

```
/db/
  schema.sql                    — Base database schema
  migrate.php                   — Migration runner system
  MIGRATIONS.md                 — Migration documentation
  /migrations/
    2025-05-11-001-create-webinars-table.sql
    2025-05-11-002-create-success-stories-table.sql
    2025-05-11-003-create-services-table.sql

/core/
  content-helpers.php           — CRUD helper functions
  
/api/
  content.php                   — JSON API endpoints

/admin/
  setup.php                     — Database setup & migration status
  services.php                  — Service management interface
  webinars.php                  — Webinar management interface
  success-stories.php           — Success stories management interface

/pages/
  /services/
    index.php                   — Services listing
    image.php                   — Single service detail
  /success-stories/
    index.php                   — Success stories listing
    single.php                  — Single story detail
  /webinar/
    index.php                   — Webinars listing
```

## Database Schema

### webinars

Event management for webinars.

```sql
Fields:
  id (INT UNSIGNED PK)
  slug (VARCHAR 200 UNIQUE)
  title (VARCHAR 255)
  excerpt, description (TEXT/MEDIUMTEXT)
  speaker_name, speaker_title, speaker_bio (VARCHAR/TEXT)
  speaker_image (VARCHAR 500)
  scheduled_at (DATETIME)
  duration_minutes (INT UNSIGNED)
  timezone (VARCHAR 50)
  registration_url (VARCHAR 500)
  max_attendees (INT UNSIGNED)
  registered_count (INT UNSIGNED)
  status (ENUM: draft|published|archived|completed|cancelled)
  published_at (DATETIME)
  created_at, updated_at (TIMESTAMP)
  author_id (INT UNSIGNED FK → users.id)
  featured_image, featured_alt (VARCHAR 500/255)
  meta_title, meta_description, meta_keywords, og_image
  content_html (MEDIUMTEXT)
```

### success_stories

Case studies and client testimonials.

```sql
Fields:
  id (INT UNSIGNED PK)
  slug (VARCHAR 200 UNIQUE)
  title, excerpt (VARCHAR 255 / TEXT)
  body_html (MEDIUMTEXT)
  company_name, company_website, company_logo
  industry, company_size
  challenge_html, solution_html, results_html (MEDIUMTEXT)
  featured (TINYINT 1 - homepage display)
  client_name, client_title, client_image
  featured_image, featured_alt
  status (ENUM: draft|published|archived)
  published_at (DATETIME)
  created_at, updated_at (TIMESTAMP)
  author_id (INT UNSIGNED FK → users.id)
  meta_title, meta_description, meta_keywords, og_image
  schema_json (JSON)
```

### services

AI service offerings.

```sql
Fields:
  id (INT UNSIGNED PK)
  slug (VARCHAR 120 UNIQUE)
  name, title (VARCHAR 120/255)
  excerpt (TEXT)
  description (MEDIUMTEXT)
  service_number (INT UNSIGNED - display order)
  icon_class (VARCHAR 120)
  featured_image, featured_alt
  overview_html, features_html, use_cases_html, benefits_html (MEDIUMTEXT)
  features_json (JSON)
  status (ENUM: draft|published|archived)
  display_on_home (TINYINT 1)
  published_at (DATETIME)
  created_at, updated_at (TIMESTAMP)
  author_id (INT UNSIGNED FK → users.id)
  meta_title, meta_description, meta_keywords, og_image
  schema_json (JSON)
```

### migrations_log

Tracks executed migrations.

```sql
Fields:
  id (INT UNSIGNED PK)
  migration_file (VARCHAR 255 UNIQUE)
  executed_at (TIMESTAMP)
```

## Helper Functions

### Core CRUD Operations

All functions are in `/core/content-helpers.php`.

#### Services

```php
$services = get_services($pdo, [
    'status'          => 'published',
    'display_on_home' => true,
    'order'           => 'service_number ASC',
]);

$service = get_service($pdo, $id_or_slug);

$result = save_service($pdo, [
    'slug'          => 'voice-ai',
    'name'          => 'Voice AI',
    'title'         => 'Voice AI',
    'excerpt'       => '...',
    'description'   => '...',
    'status'        => 'published',
    'author_id'     => 1,
]);

$result = delete_service($pdo, $id);
```

#### Webinars

```php
$webinars = get_webinars($pdo, [
    'status'         => 'published',
    'published_only' => true,
    'order'          => 'scheduled_at ASC',
]);

$webinar = get_webinar($pdo, $id_or_slug);

$result = save_webinar($pdo, [
    'slug'          => 'webinar-slug',
    'title'         => 'Webinar Title',
    'scheduled_at'  => '2025-05-20 14:00:00',
    'speaker_name'  => 'John Doe',
    'status'        => 'published',
    'author_id'     => 1,
]);

$result = delete_webinar($pdo, $id);
```

#### Success Stories

```php
$stories = get_success_stories($pdo, [
    'status'         => 'published',
    'featured_only'  => true,
    'industry'       => 'FinTech',
    'published_only' => true,
    'order'          => 'published_at DESC',
]);

$story = get_success_story($pdo, $id_or_slug);

$result = save_success_story($pdo, [
    'slug'          => 'case-study-1',
    'title'         => 'Case Study Title',
    'company_name'  => 'Acme Corp',
    'industry'      => 'FinTech',
    'featured'      => true,
    'status'        => 'published',
    'author_id'     => 1,
]);

$result = delete_success_story($pdo, $id);
```

### Return Format

All helper functions return consistent formats:

**Success:**
```php
[
    'success' => true,
    'id'      => 123,
    'message' => 'Service saved successfully',
    'data'    => [...] // For retrieval functions
]
```

**Error:**
```php
[
    'success' => false,
    'error'   => 'Error message',
    'message' => 'Error message'
]
```

## Admin Interfaces

### /admin/services.php

Manage AI service offerings.

**Features:**
- List all services with status and display order
- Create/edit service details
- Manage feature lists (JSON)
- SEO metadata per service
- Homepage display toggle
- Status management (draft/published/archived)

**Fields:**
- Service Name, Display Title, Slug
- Excerpt and Full Description
- Overview, Features, Use Cases, Benefits
- Icon Class (Font Awesome or custom)
- Featured Image
- Feature List (one per line)
- SEO: Meta Title, Description, Keywords, OG Image
- Status and Homepage Display Toggle

### /admin/webinars.php

Manage webinar events.

**Features:**
- List all webinars with speaker and scheduled date
- Create/edit webinar details
- Speaker information management
- Event scheduling with timezone
- Registration tracking
- Multiple statuses (draft, published, completed, cancelled, archived)

**Fields:**
- Title, Slug, Status
- Excerpt and Description
- Speaker: Name, Title, Bio, Photo
- Event Details: Date/Time, Duration, Timezone, Registration URL, Capacity
- Featured Image
- SEO: Meta Title, Description, Keywords, OG Image

### /admin/success-stories.php

Manage case studies and client success stories.

**Features:**
- List stories with company, industry, and featured flag
- Create/edit story details
- Company and client information
- Challenge/Solution/Results sections
- Featured flag for homepage display
- Industry categorization

**Fields:**
- Title, Slug, Status
- Excerpt and Full Case Study Details
- Company: Name, Website, Logo, Industry, Size
- Sections: Challenge, Solution, Results
- Client: Name, Title, Photo
- Featured Image
- Featured Homepage Toggle
- SEO: Meta Title, Description, Keywords, OG Image

### /admin/setup.php

Monitor and manage database migrations.

**Features:**
- View migration status (total, executed, pending)
- Run pending migrations with one click
- See list of pending migration files
- View database table creation status
- Quick links to content management pages
- API endpoint reference

## JSON API

Public endpoints for frontend consumption (no authentication).

### Services

```
GET /api/content?endpoint=services&action=list
```
Returns all published services configured for homepage display.

**Response:**
```json
{
  "success": true,
  "count": 4,
  "data": [
    {
      "id": 1,
      "slug": "voice-ai",
      "name": "Voice AI",
      "title": "Voice AI - Conversational AI",
      "excerpt": "Natural language processing...",
      "features_json": "[\"Real-time\", \"Multi-language\", \"99.9% uptime\"]",
      "status": "published",
      "display_on_home": 1
    }
  ]
}
```

```
GET /api/content?endpoint=services&action=detail&slug=voice-ai
```

### Webinars

```
GET /api/content?endpoint=webinars&action=list
```

```
GET /api/content?endpoint=webinars&action=list&filter=upcoming
```

```
GET /api/content?endpoint=webinars&action=list&filter=past
```

```
GET /api/content?endpoint=webinars&action=detail&slug=webinar-slug
```

### Success Stories

```
GET /api/content?endpoint=success-stories&action=list
```

```
GET /api/content?endpoint=success-stories&action=list&featured=1
```

```
GET /api/content?endpoint=success-stories&action=list&industry=FinTech
```

```
GET /api/content?endpoint=success-stories&action=detail&slug=case-study-1
```

## Frontend Integration

### Listing Pages

#### Services (`/pages/services/index.php`)

```php
$pdo = db();
$services = get_services($pdo, [
    'status'          => 'published',
    'display_on_home' => true,
    'order'           => 'service_number ASC',
]);

foreach ($services as $service) {
    echo $service['name'];
    echo $service['excerpt'];
    $features = json_decode($service['features_json']);
}
```

#### Webinars (`/pages/webinar/index.php`)

```php
$webinars = get_webinars($pdo, [
    'published_only' => true,
    'order'          => 'scheduled_at ASC',
]);

foreach ($webinars as $webinar) {
    echo $webinar['title'];
    echo $webinar['speaker_name'];
    echo $webinar['scheduled_at'];
}
```

#### Success Stories (`/pages/success-stories/index.php`)

```php
$stories = get_success_stories($pdo, [
    'published_only' => true,
    'featured_only'  => true, // Homepage featured only
]);

foreach ($stories as $story) {
    echo $story['company_name'];
    echo $story['industry'];
}
```

### Detail Pages

#### Service Detail (`/pages/services/{slug}`)

```php
$service = get_service($pdo, 'voice-ai');
if ($service && $service['status'] === 'published') {
    echo $service['title'];
    echo $service['overview_html'];
    echo $service['use_cases_html'];
}
```

#### Webinar Detail (`/pages/webinar/{slug}`)

Already routed in `/pages/webinar/single.php`

#### Story Detail (`/pages/success-stories/{slug}`)

Already routed in `/pages/success-stories/single.php`

## Troubleshooting

### Migrations not executing

1. Check `/admin/setup.php` for status
2. Look at PHP error log for connection issues
3. Verify database credentials in `config/config.php`

### Foreign key constraints

Migrations run in order: services → webinars → success_stories. All tables depend on `users` table existing first.

### Helper functions not found

Ensure `/core/content-helpers.php` is required:
```php
require_once CORE_DIR . '/content-helpers.php';
```

### API returning 404

Check that:
1. Endpoint name is correct (webinars, success-stories, services)
2. Action is 'list' or 'detail'
3. Content is published (status='published')

## Best Practices

1. **Always set author_id** — All content should be attributed to a user
2. **Use slugs** — Generate URL-friendly slugs automatically when possible
3. **Publish timestamps** — Set publish date when changing status to 'published'
4. **SEO metadata** — Fill in meta titles/descriptions for search optimization
5. **Database backups** — Backup before running migrations
6. **Test in staging** — Always test migrations in a staging environment first

## Performance Tips

- All critical columns are indexed (status, published_at, etc.)
- Use `featured_only`, `published_only` filters for faster queries
- Pagination recommended for listing 50+ items
- Cache API responses on frontend for 1-hour intervals

## Next Steps

1. ✅ Run base schema
2. ✅ Check migration status at `/admin/setup.php`
3. 🔄 Add first service at `/admin/services.php`
4. 🔄 Schedule first webinar at `/admin/webinars.php`
5. 🔄 Publish first case study at `/admin/success-stories.php`
6. 🔄 Update frontend pages with helper functions
7. 🔄 Test API endpoints

## Support Files

- `db/MIGRATIONS.md` — Detailed migration documentation
- `SETUP.md` — Complete system setup guide
- `core/content-helpers.php` — Function signatures and documentation
- `/admin/setup.php` — Visual migration status dashboard
