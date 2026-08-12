-- Migration: Add 'hire' to media category
-- Created: 2026-08-07
-- Purpose: Hire Master uploads (featured images, case-study images, etc.)
--          get their own Media Master category, alongside the existing
--          blog/page/service/media categories (see
--          2026-07-24-001-add-media-category.sql).

ALTER TABLE `media`
    MODIFY COLUMN `category` ENUM('blog','page','service','media','hire') NOT NULL DEFAULT 'media';
