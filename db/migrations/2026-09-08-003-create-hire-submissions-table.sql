-- Migration: Create hire_submissions table
-- Created: 2026-09-08
-- Purpose: Dedicated lead-capture table for the Hire Form (the "Schedule
--          Free Consultation" widget on /hire-ai-engineers and every
--          /hire/{slug} page). Kept separate from `contact_submissions`
--          since its field set (country/hiring_model/project_details) does
--          not match the shared Contact/Podcast shape.

CREATE TABLE IF NOT EXISTS `hire_submissions` (
    `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`             VARCHAR(150) NOT NULL DEFAULT '',
    `email`            VARCHAR(191) NOT NULL,
    `phone`            VARCHAR(50)  NOT NULL DEFAULT '',
    `company`          VARCHAR(150) NOT NULL DEFAULT '',
    `country`          VARCHAR(100) NOT NULL DEFAULT '',
    `hiring_model`     VARCHAR(100) NOT NULL DEFAULT '',
    `project_details`  TEXT         NOT NULL,
    `page_url`         VARCHAR(500) NOT NULL DEFAULT '',
    `ip_address`       VARCHAR(45)  NOT NULL DEFAULT '',
    `status`           ENUM('new','read','contacted','closed') NOT NULL DEFAULT 'new',
    `created_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_hire_submissions_status` (`status`),
    KEY `idx_hire_submissions_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
