-- Migration: Add 'podcast' to media category
-- Created: 2026-08-26
-- Purpose: Podcast Master uploads (guest company logos, thumbnail overrides,
--          og_image) get their own Media Master category, alongside the
--          existing blog/page/service/media/hire/success-story/engineer
--          categories (see 2026-08-12-003-add-success-story-media-category.sql).
--          NOTE: 'engineer' is included here even though no tracked migration
--          in this repo added it — the live `media` table's ENUM already
--          carries it (and has rows using it), so it must stay listed or a
--          MODIFY COLUMN here would truncate that existing category out.

ALTER TABLE `media`
    MODIFY COLUMN `category` ENUM('blog','page','service','media','hire','success-story','engineer','podcast') NOT NULL DEFAULT 'media';
