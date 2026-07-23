-- Migration: Create services table
-- Created: 2025-05-11
-- Purpose: Manage AI service offerings (Voice AI, Text AI, Image/Document AI, Process Automation)

CREATE TABLE IF NOT EXISTS `services` (
    `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `slug`              VARCHAR(120) NOT NULL,
    `name`              VARCHAR(120) NOT NULL,
    `title`             VARCHAR(255) NOT NULL,
    `excerpt`           TEXT         NOT NULL,
    `description`       MEDIUMTEXT   NOT NULL,
    
    -- Service metadata
    `icon_class`        VARCHAR(100) NOT NULL DEFAULT 'flaticon-tech-interaction-1',
    `service_number`    INT UNSIGNED NOT NULL DEFAULT 1,  -- display order
    `featured_image`    VARCHAR(500) NOT NULL DEFAULT '',
    `featured_alt`      VARCHAR(255) NOT NULL DEFAULT '',
    
    -- Detailed sections
    `overview_html`     MEDIUMTEXT   NOT NULL DEFAULT '',
    `features_html`     MEDIUMTEXT   NOT NULL DEFAULT '',
    `use_cases_html`    MEDIUMTEXT   NOT NULL DEFAULT '',
    `benefits_html`     MEDIUMTEXT   NOT NULL DEFAULT '',
    
    -- Features list (JSON for flexibility)
    `features_json`     JSON         NULL,  -- [{"title": "Feature", "description": "..."}, ...]
    
    -- Content & SEO
    `meta_title`        VARCHAR(255) NOT NULL DEFAULT '',
    `meta_description`  VARCHAR(320) NOT NULL DEFAULT '',
    `meta_keywords`     VARCHAR(255) NOT NULL DEFAULT '',
    `og_image`          VARCHAR(500) NOT NULL DEFAULT '',
    `schema_json`       MEDIUMTEXT   NOT NULL,
    
    -- Status & Publishing
    `status`            ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
    `published_at`      DATETIME     NULL,
    `display_on_home`   TINYINT(1)   NOT NULL DEFAULT 1,
    `author_id`         INT UNSIGNED NULL,
    
    -- Metadata
    `created_at`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_services_slug` (`slug`),
    KEY `idx_services_status_published` (`status`, `published_at`),
    KEY `idx_services_display_home` (`display_on_home`, `service_number`),
    KEY `idx_services_author` (`author_id`),
    CONSTRAINT `fk_services_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default services
INSERT IGNORE INTO `services` (`slug`, `name`, `title`, `excerpt`, `description`, `service_number`, `status`, `published_at`, `icon_class`, `display_on_home`) 
VALUES 
    ('voice-ai', 'Voice AI', 'Voice AI', 'Conversational, multilingual, sentiment-aware voice agents that transform customer interactions and lead nurturing.', '', 1, 'published', NOW(), 'flaticon-tech-interaction-1', 1),
    ('text-ai', 'Text AI', 'Text AI', 'Intelligent chatbots, NLP-powered insights, AI email marketing, and personalized outreach.', '', 2, 'published', NOW(), 'flaticon-tech-interaction-1', 1),
    ('image-document-ai', 'Image / Document AI', 'Image / Document AI', 'KYC verification, fraud detection, signature authentication, and invoice processing with computer vision.', '', 3, 'published', NOW(), 'flaticon-tech-interaction-1', 1),
    ('process-automation', 'Process Automation', 'Process Automation', 'Streamline workflows with AI-driven automation for increased efficiency and reduced manual overhead.', '', 4, 'published', NOW(), 'flaticon-tech-interaction-1', 1);
