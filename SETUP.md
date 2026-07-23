# Backend Setup & Database Configuration

Complete guide to setting up the database migrations and backend for Quantal AI website.

## Prerequisites

- PHP 8.1+
- MySQL/MariaDB 5.7+
- Basic SQL knowledge
- Access to admin panel

## Quick Start

### Step 1: Database Configuration

Ensure your database credentials are configured in `config/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'quantal_db');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### Step 2: Run Initial Schema

First, create the base tables:

```bash
# Using MySQL CLI
mysql -u root -p quantal_db < db/schema.sql
```

Or use phpMyAdmin → Import tab.

### Step 3: Automatic Migration (Recommended)

Migrations run automatically. To verify:

```php
<?php
require_once 'db/migrate.php';
$status = get_migration_status(DB_MIGRATIONS_DIR);
echo json_encode($status);
```

### Step 4: Check Migration Status

Add this to your admin dashboard or create a setup page:

```php
require_once 'db/migrate.php';
$status = get_migration_status(DB_MIGRATIONS_DIR);

if ($status['status'] === 'ok') {
    echo "✓ Migrations: {$status['executed_migrations']}/{$status['total_migrations']}";
    
    if ($status['pending_migrations'] > 0) {
        $result = run_migrations(DB_MIGRATIONS_DIR);
        echo "Running migrations... " . $result['message'];
    }
}
```

## Database Structure

### Table Overview

```
users (existing)
├── id (PK)
├── username, email, password_hash
└── role, is_active, timestamps

services (new)
├── id (PK)
├── slug, name, title, description
├── service_number (sort order)
├── features_json (flexible features)
├── status, display_on_home
└── timestamps

webinars (new)
├── id (PK)
├── slug, title, excerpt, description
├── speaker_name, speaker_title, speaker_bio
├── scheduled_at, duration_minutes, timezone
├── registration_url, max_attendees, registered_count
├── status (draft|published|archived|completed|cancelled)
└── timestamps

success_stories (new)
├── id (PK)
├── slug, title, excerpt, body_html
├── company_name, company_website, company_logo
├── industry, company_size
├── challenge_html, solution_html, results_html
├── client_name, client_title, client_image
├── featured (for homepage)
├── status
└── timestamps

migrations_log (tracking)
├── id (PK)
├── migration_file (UNIQUE)
└── executed_at
```

## Admin Management Pages

After migrations, you have three new admin sections:

### 1. Services Management
**Path:** `/admin/services.php`

Manage the 4 core AI services:
- Voice AI
- Text AI
- Image / Document AI
- Process Automation

Features:
- Edit service descriptions and details
- Manage feature lists (JSON)
- SEO optimization per service
- Display order control
- Publish/draft/archive status

### 2. Webinars Management
**Path:** `/admin/webinars.php`

Create and manage webinar events:
- Event scheduling with timezone support
- Speaker information management
- Registration URL tracking
- Attendee capacity management
- Multiple statuses: draft, published, completed, cancelled
- Full SEO metadata

### 3. Success Stories Management
**Path:** `/admin/success-stories.php`

Publish client case studies:
- Company and client information
- Challenge/Solution/Results sections
- Featured flag for homepage display
- Industry categorization
- Client testimonial support
- Full SEO metadata

## Frontend Usage

### Services Listing

```php
require_once CORE_DIR . '/content-helpers.php';

$services = get_services($pdo, [
    'status'          => 'published',
    'display_on_home' => true,
]);

foreach ($services as $service) {
    echo $service['name']; // Voice AI, Text AI, etc.
    echo $service['title'];
    echo $service['excerpt'];
}
```

### Webinars Listing

```php
$webinars = get_webinars($pdo, [
    'published_only' => true,
    'order'          => 'scheduled_at ASC',
]);

foreach ($webinars as $webinar) {
    echo $webinar['title'];
    echo $webinar['speaker_name'];
    echo date('M d, Y', strtotime($webinar['scheduled_at']));
}
```

### Success Stories

```php
// Featured stories for homepage
$featured = get_success_stories($pdo, [
    'published_only' => true,
    'featured_only'  => true,
]);

// All stories
$all_stories = get_success_stories($pdo, [
    'published_only' => true,
]);

// By industry
$by_industry = get_success_stories($pdo, [
    'published_only' => true,
    'industry'       => 'FinTech',
]);
```

### Single Item

```php
// By slug
$service = get_service($pdo, 'voice-ai');
$webinar = get_webinar($pdo, 'webinar-slug');
$story = get_success_story($pdo, 'case-study-slug');

// By ID
$service = get_service($pdo, 1);
$webinar = get_webinar($pdo, 1);
$story = get_success_story($pdo, 1);
```

## JSON API Endpoints

Public API endpoints for frontend consumption (no auth required):

### Services

```
GET /api/content?endpoint=services&action=list
GET /api/content?endpoint=services&action=detail&slug=voice-ai
```

Response:
```json
{
  "success": true,
  "count": 4,
  "data": [
    {
      "id": 1,
      "slug": "voice-ai",
      "name": "Voice AI",
      "title": "Voice AI",
      "excerpt": "Conversational, multilingual...",
      "status": "published",
      "display_on_home": 1
    }
  ]
}
```

### Webinars

```
GET /api/content?endpoint=webinars&action=list
GET /api/content?endpoint=webinars&action=list&filter=upcoming
GET /api/content?endpoint=webinars&action=list&filter=past
GET /api/content?endpoint=webinars&action=detail&slug=webinar-slug
```

### Success Stories

```
GET /api/content?endpoint=success-stories&action=list
GET /api/content?endpoint=success-stories&action=list&featured=1
GET /api/content?endpoint=success-stories&action=list&industry=FinTech
GET /api/content?endpoint=success-stories&action=detail&slug=case-study
```

## Data Validation & Sanitization

All admin forms include validation:

```php
// Built-in sanitization functions used in forms
sanitize_text()       // XSS protection
sanitize_textarea()   // Multi-line text
sanitize_html()       // HTML content (whitelisted tags)
sanitize_url()        // URLs
sanitize_slug()       // URL slugs
```

## Error Handling

Migration errors are logged to PHP error log:

```php
error_log('Migration error: ...');
```

API errors return proper HTTP status codes:

```
200 OK       - Success
400 Bad Request - Invalid parameters
404 Not Found   - Resource not found
500 Server Error - Database/system error
```

## Troubleshooting

### Issue: Migrations not running

**Solution:** Add to admin bootstrap:
```php
require_once DB_DIR . '/migrate.php';
$result = run_migrations(DB_MIGRATIONS_DIR);
```

### Issue: Can't connect to database

**Solution:** 
1. Check credentials in `config/config.php`
2. Verify MySQL is running
3. Check database name exists

### Issue: Table already exists error

**Solution:** Safe to ignore. Migrations use `CREATE TABLE IF NOT EXISTS`.

### Issue: Foreign key constraint error

**Solution:** Verify all tables created in order:
1. `users` (base)
2. `services`
3. `webinars` (depends on users)
4. `success_stories` (depends on users)

## Backup & Restore

### Backup database

```bash
mysqldump -u root -p quantal_db > backup-2025-05-11.sql
```

### Restore database

```bash
mysql -u root -p quantal_db < backup-2025-05-11.sql
```

## Performance Optimization

### Database Indexes

All critical columns are indexed:
- `webinars.status, scheduled_at`
- `success_stories.status, published_at`
- `services.display_on_home, service_number`

### Query Optimization

Use helper functions with filters to minimize queries:

```php
// Good - filters at database level
$stories = get_success_stories($pdo, ['featured_only' => true]);

// Avoid - filter in PHP
$all = get_success_stories($pdo);
$featured = array_filter($all, fn($s) => $s['featured']);
```

## Next Steps

1. ✓ Run initial schema
2. ✓ Run migrations
3. Visit `/admin/webinars.php` - Add first webinar
4. Visit `/admin/success-stories.php` - Add first case study
5. Visit `/admin/services.php` - Customize services
6. Update frontend pages to use helper functions
7. Test JSON API endpoints

## Support

For issues or questions, check:
- `db/MIGRATIONS.md` - Detailed migration info
- `core/content-helpers.php` - Function documentation
- Database error logs at `/var/log/mysql/error.log`
- PHP error logs defined in `php.ini`
