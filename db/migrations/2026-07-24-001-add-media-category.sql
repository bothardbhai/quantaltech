-- Migration: Add category to media table
-- Created: 2026-07-24
-- Purpose: Media Master now organizes uploads by category (blog/page/service/media),
--          each stored under its own folder. This column drives the Media Master
--          category filter and lets each upload be routed to the right folder.

ALTER TABLE `media`
    ADD COLUMN `category` ENUM('blog','page','service','media') NOT NULL DEFAULT 'media' AFTER `original_name`;
