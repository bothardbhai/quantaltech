-- Migration: Create success_story_categories table
-- Created: 2026-08-12
-- Purpose: Admin-managed category/filter taxonomy for Success Stories
--          (e.g. Legal Recruitment, HR & Recruitment, AI / ML Research).
--          Referenced by `success_stories.category_id` (see
--          2026-08-12-002-extend-success-stories-table.sql) and used by the
--          /success-stories listing page's filter bar.

CREATE TABLE IF NOT EXISTS `success_story_categories` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`          VARCHAR(100) NOT NULL,
    `slug`          VARCHAR(120) NOT NULL,
    `status`        ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `sort_order`    INT UNSIGNED NOT NULL DEFAULT 1,
    `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_success_story_categories_slug` (`slug`),
    KEY `idx_success_story_categories_status_sort` (`status`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
