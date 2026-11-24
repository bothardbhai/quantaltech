-- Migration: Let services / hire pages pick (or hide) their Success Stories teaser
-- Created: 2026-11-24
-- Purpose: The "Case Studies & Success Stories" section on service and hire
--          pages is always the 4 most recently published Success Stories.
--          Adds two optional controls per page, both defaulting to today's
--          behavior (so every existing page is unchanged):
--            cs_story_ids_json - hand-picked story IDs (max 4); NULL/empty =
--                                automatically show the latest 4.
--            cs_hidden         - 1 = don't show the section at all.

ALTER TABLE `services`
    ADD COLUMN `cs_story_ids_json` JSON NULL AFTER `cs_text`,
    ADD COLUMN `cs_hidden` TINYINT(1) NOT NULL DEFAULT 0 AFTER `cs_story_ids_json`;

ALTER TABLE `hire_pages`
    ADD COLUMN `cs_story_ids_json` JSON NULL AFTER `cs_text`,
    ADD COLUMN `cs_hidden` TINYINT(1) NOT NULL DEFAULT 0 AFTER `cs_story_ids_json`;
