-- Migration: Create contact_submissions table
-- Created: 2026-09-08
-- Purpose: Shared lead-capture table for the Contact Form and the Podcast
--          Inquiry Form (both post to pages/contact-submit.php) — one common
--          table distinguished by `form_type`, rather than a separate table
--          per form. The database write here is authoritative: it happens
--          before any email is attempted, and email failure never affects
--          whether a submission is considered successful.

CREATE TABLE IF NOT EXISTS `contact_submissions` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `form_type`   VARCHAR(30)  NOT NULL DEFAULT 'contact',   -- 'contact' | 'podcast'
    `name`        VARCHAR(150) NOT NULL DEFAULT '',
    `first_name`  VARCHAR(100) NOT NULL DEFAULT '',
    `last_name`   VARCHAR(100) NOT NULL DEFAULT '',
    `email`       VARCHAR(191) NOT NULL,
    `phone`       VARCHAR(50)  NOT NULL DEFAULT '',
    `company`     VARCHAR(150) NOT NULL DEFAULT '',
    `subject`     VARCHAR(255) NOT NULL DEFAULT '',
    `message`     TEXT         NOT NULL,
    `page_url`    VARCHAR(500) NOT NULL DEFAULT '',
    `ip_address`  VARCHAR(45)  NOT NULL DEFAULT '',
    `status`      ENUM('new','read','contacted','closed') NOT NULL DEFAULT 'new',
    `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_contact_submissions_form_type_status` (`form_type`, `status`),
    KEY `idx_contact_submissions_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
