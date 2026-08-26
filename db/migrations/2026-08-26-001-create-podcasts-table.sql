-- Migration: Create podcasts table
-- Created: 2026-08-26
-- Purpose: Store Quantal AI Podcast episodes (YouTube-hosted interviews),
--          independent of the `webinars` table — webinars is scheduling/
--          registration-shaped and has no room for a YouTube URL or
--          repeatable Key Takeaways / Episode Highlights lists.

CREATE TABLE IF NOT EXISTS `podcasts` (
    `id`                    INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `slug`                  VARCHAR(200) NOT NULL,
    `title`                 VARCHAR(255) NOT NULL,

    -- Guest information
    `guest_name`            VARCHAR(120) NOT NULL,
    `guest_designation`     VARCHAR(150) NOT NULL DEFAULT '',
    `guest_company`         VARCHAR(150) NOT NULL DEFAULT '',
    `guest_company_logo`    VARCHAR(500) NOT NULL DEFAULT '',

    -- Video
    `youtube_url`           VARCHAR(500) NOT NULL DEFAULT '',
    `thumbnail_override`    VARCHAR(500) NOT NULL DEFAULT '',

    -- Content (TEXT/MEDIUMTEXT columns can't carry a DEFAULT under this
    -- server's strict SQL mode — see MySQL error 1101 — so these are left
    -- NOT NULL with no default; admin code always writes an explicit '' )
    `short_description`     TEXT         NOT NULL,
    `description_html`      MEDIUMTEXT   NOT NULL,
    `key_takeaways_json`    MEDIUMTEXT   NOT NULL,   -- [{"text": "...", "active": true}, ...]
    `highlights_json`       MEDIUMTEXT   NOT NULL,   -- [{"text": "...", "active": true}, ...]

    -- Publishing
    `featured`              TINYINT(1)   NOT NULL DEFAULT 0,
    `publish_date`          DATE         NULL,
    `status`                ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
    `published_at`          DATETIME     NULL,
    `author_id`             INT UNSIGNED NULL,

    -- SEO
    `meta_title`            VARCHAR(255) NOT NULL DEFAULT '',
    `meta_description`      VARCHAR(320) NOT NULL DEFAULT '',
    `meta_keywords`         VARCHAR(255) NOT NULL DEFAULT '',
    `og_image`              VARCHAR(500) NOT NULL DEFAULT '',
    `canonical`              VARCHAR(500) NOT NULL DEFAULT '',
    `robots`                VARCHAR(60)  NOT NULL DEFAULT '',
    `schema_json`           MEDIUMTEXT   NOT NULL,

    -- Metadata
    `created_at`            DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`            DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_podcasts_slug` (`slug`),
    KEY `idx_podcasts_status_publish_date` (`status`, `publish_date`),
    KEY `idx_podcasts_featured` (`featured`, `publish_date`),
    KEY `idx_podcasts_author` (`author_id`),
    CONSTRAINT `fk_podcasts_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
