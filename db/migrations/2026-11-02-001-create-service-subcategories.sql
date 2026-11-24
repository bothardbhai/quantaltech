-- Migration: Create service_subcategories table
-- Created: 2026-11-02
-- Purpose: Admin-managed grouping master for Services (Build AI, AI Consulting,
--          AI Applications). Used ONLY to group services in admin and in the
--          frontend navigation/mega menu. A subcategory's slug is an internal
--          identifier and is never part of any service URL, slug, canonical or
--          route — service URLs stay /services/{services.slug}.
--          Referenced by `services.subcategory_id` (see
--          2026-11-02-002-add-subcategory-to-services.sql). Managed in
--          admin/service-subcategories.php.

CREATE TABLE IF NOT EXISTS `service_subcategories` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`          VARCHAR(100) NOT NULL,
    `slug`          VARCHAR(120) NOT NULL,
    `description`   TEXT NULL,
    `sort_order`    INT UNSIGNED NOT NULL DEFAULT 1,
    `status`        ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_service_subcategories_slug` (`slug`),
    KEY `idx_service_subcategories_status_sort` (`status`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Initial subcategories (INSERT IGNORE on the unique slug = idempotent)
INSERT IGNORE INTO `service_subcategories` (`name`, `slug`, `description`, `sort_order`, `status`)
VALUES
    ('Build AI',        'build-ai',        NULL, 1, 'active'),
    ('AI Consulting',   'ai-consulting',   NULL, 2, 'active'),
    ('AI Applications', 'ai-applications', NULL, 3, 'active');
