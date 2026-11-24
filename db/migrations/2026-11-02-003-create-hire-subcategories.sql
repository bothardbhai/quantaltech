-- Migration: Create hire_subcategories table
-- Created: 2026-11-02
-- Purpose: Admin-managed grouping master for Hire Master pages (hire_pages),
--          the Hire counterpart of service_subcategories
--          (2026-11-02-001-create-service-subcategories.sql). Used ONLY to
--          group hire pages in the admin and in the "Hire with Us" mega menu.
--          A subcategory's slug is an internal identifier and is never part
--          of any hire URL, slug, canonical or route — hire pages stay at
--          /hire/{hire_pages.slug}.
--          Referenced by `hire_pages.subcategory_id` (see
--          2026-11-02-004-add-subcategory-to-hire-pages.sql). Managed in
--          admin/hire-subcategories.php.

CREATE TABLE IF NOT EXISTS `hire_subcategories` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`          VARCHAR(100) NOT NULL,
    `slug`          VARCHAR(120) NOT NULL,
    `description`   TEXT NULL,
    `sort_order`    INT UNSIGNED NOT NULL DEFAULT 1,
    `status`        ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_hire_subcategories_slug` (`slug`),
    KEY `idx_hire_subcategories_status_sort` (`status`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Initial subcategories (INSERT IGNORE on the unique slug = idempotent)
INSERT IGNORE INTO `hire_subcategories` (`name`, `slug`, `description`, `sort_order`, `status`)
VALUES
    ('AI & ML Engineers',        'ai-ml-engineers',          NULL, 1, 'active'),
    ('Specialized AI Engineers', 'specialized-ai-engineers', NULL, 2, 'active');
