-- Migration: Add 'success-story' to media category
-- Created: 2026-08-12
-- Purpose: Success Stories Master uploads (hero/featured images, challenge &
--          solution images, technology logos, etc.) get their own Media
--          Master category, alongside the existing blog/page/service/media/
--          hire categories (see 2026-08-07-002-add-hire-media-category.sql).

ALTER TABLE `media`
    MODIFY COLUMN `category` ENUM('blog','page','service','media','hire','success-story') NOT NULL DEFAULT 'media';
