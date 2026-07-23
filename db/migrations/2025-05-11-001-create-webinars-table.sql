-- Migration: Create webinars table
-- Created: 2025-05-11
-- Purpose: Store webinar event information with speaker details and registration

CREATE TABLE IF NOT EXISTS `webinars` (
    `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `slug`              VARCHAR(200) NOT NULL,
    `title`             VARCHAR(255) NOT NULL,
    `excerpt`           TEXT         NOT NULL,
    `description`       MEDIUMTEXT   NOT NULL,
    `speaker_name`      VARCHAR(120) NOT NULL,
    `speaker_title`     VARCHAR(120) NOT NULL DEFAULT '',
    `speaker_bio`       TEXT         NOT NULL DEFAULT '',
    `speaker_image`     VARCHAR(500) NOT NULL DEFAULT '',
    `featured_image`    VARCHAR(500) NOT NULL DEFAULT '',
    `featured_alt`      VARCHAR(255) NOT NULL DEFAULT '',
    
    -- Event scheduling
    `scheduled_at`      DATETIME     NOT NULL,
    `duration_minutes`  INT UNSIGNED NOT NULL DEFAULT 60,
    `timezone`          VARCHAR(50)  NOT NULL DEFAULT 'UTC',
    
    -- Registration
    `registration_url`  VARCHAR(500) NOT NULL DEFAULT '',
    `max_attendees`     INT UNSIGNED NULL,
    `registered_count`  INT UNSIGNED NOT NULL DEFAULT 0,
    
    -- Content & SEO
    `content_html`      MEDIUMTEXT   NOT NULL,
    `meta_title`        VARCHAR(255) NOT NULL DEFAULT '',
    `meta_description`  VARCHAR(320) NOT NULL DEFAULT '',
    `meta_keywords`     VARCHAR(255) NOT NULL DEFAULT '',
    `og_image`          VARCHAR(500) NOT NULL DEFAULT '',
    `schema_json`       MEDIUMTEXT   NOT NULL,
    
    -- Status & Publishing
    `status`            ENUM('draft','published','archived','completed','cancelled') NOT NULL DEFAULT 'draft',
    `published_at`      DATETIME     NULL,
    `author_id`         INT UNSIGNED NULL,
    
    -- Metadata
    `created_at`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_webinars_slug` (`slug`),
    KEY `idx_webinars_status_scheduled` (`status`, `scheduled_at`),
    KEY `idx_webinars_author` (`author_id`),
    CONSTRAINT `fk_webinars_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
