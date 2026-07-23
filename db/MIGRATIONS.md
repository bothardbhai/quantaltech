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
| results_html | MEDIUMTEXT | Results and impact |
| featured | TINYINT(1) | Show on homepage |
| status | ENUM | draft, published, archived |

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
get_success_stories($pdo, ['status' => 'published', 'featured_only' => true]);
get_success_story($pdo, 123);
get_success_story($pdo, 'case-study-slug');
save_success_story($pdo, $data);
delete_success_story($pdo, $id);
```

### Services

```php
get_services($pdo, ['display_on_home' => true]);
get_service($pdo, 123);
get_service($pdo, 'voice-ai');
save_service($pdo, $data);
delete_service($pdo, $id);
```

## Admin Pages

After migrations run, you'll have three new admin management pages:

- `/admin/webinars.php` - Manage webinars
- `/admin/success-stories.php` - Manage case studies
- `/admin/services.php` - Manage service offerings

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
