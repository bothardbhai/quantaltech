-- Migration: Let a Success Story hide the Short Description inside "Our Client"
-- Created: 2026-11-26
-- Purpose: The story's Short Description / Excerpt is printed in the hero, on
--          the listing card, AND as a paragraph in the "Our Client" section.
--          This lets an admin turn off that last use per story. Default 1 =
--          keep showing it, so every existing story is unchanged.

ALTER TABLE `success_stories`
    ADD COLUMN `ourclient_show_excerpt` TINYINT(1) NOT NULL DEFAULT 1 AFTER `ourclient_intro_html`;
