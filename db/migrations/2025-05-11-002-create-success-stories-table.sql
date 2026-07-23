-- Migration: Create success_stories table
-- Created: 2025-05-11
-- Purpose: Store case studies and success stories from clients

CREATE TABLE IF NOT EXISTS `success_stories` (
    `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `slug`              VARCHAR(200) NOT NULL,
    `title`             VARCHAR(255) NOT NULL,
    `excerpt`           TEXT         NOT NULL,
    `body_html`         MEDIUMTEXT   NOT NULL,
    
    -- Client information
    `company_name`      VARCHAR(120) NOT NULL,
    `company_website`   VARCHAR(255) NOT NULL DEFAULT '',
    `company_logo`      VARCHAR(500) NOT NULL DEFAULT '',
    `industry`          VARCHAR(100) NOT NULL,
    `company_size`      VARCHAR(50)  NOT NULL DEFAULT '',
    
    -- Challenge, Solution, Results
    `challenge_html`    MEDIUMTEXT   NOT NULL DEFAULT '',
    `solution_html`     MEDIUMTEXT   NOT NULL DEFAULT '',
    `results_html`      MEDIUMTEXT   NOT NULL DEFAULT '',
    
    -- Metrics/Results quantified
    `metrics_json`      JSON         NULL,  -- {"metric_name": "value", ...}
    
    -- Featured image
    `featured_image`    VARCHAR(500) NOT NULL DEFAULT '',
    `featured_alt`      VARCHAR(255) NOT NULL DEFAULT '',
    
    -- Client contact (optional)
    `client_name`       VARCHAR(120) NOT NULL DEFAULT '',
    `client_title`      VARCHAR(120) NOT NULL DEFAULT '',
    `client_image`      VARCHAR(500) NOT NULL DEFAULT '',
    
    -- Content & SEO
    `meta_title`        VARCHAR(255) NOT NULL DEFAULT '',
    `meta_description`  VARCHAR(320) NOT NULL DEFAULT '',
    `meta_keywords`     VARCHAR(255) NOT NULL DEFAULT '',
    `og_image`          VARCHAR(500) NOT NULL DEFAULT '',
    `schema_json`       MEDIUMTEXT   NOT NULL,
    
    -- Status & Publishing
    `status`            ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
    `published_at`      DATETIME     NULL,
    `featured`          TINYINT(1)   NOT NULL DEFAULT 0,  -- show on homepage
    `author_id`         INT UNSIGNED NULL,
    
    -- Metadata
    `created_at`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_success_stories_slug` (`slug`),
    KEY `idx_success_stories_status_published` (`status`, `published_at`),
    KEY `idx_success_stories_featured` (`featured`, `published_at`),
    KEY `idx_success_stories_author` (`author_id`),
    CONSTRAINT `fk_success_stories_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
