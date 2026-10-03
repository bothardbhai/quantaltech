-- Migration: Add a display-style toggle to Success Stories' Technology Stack
-- Created: 2026-09-24
-- Purpose: "Our Technology Stack" was deliberately built as a numbered
--          editorial list (icon + name + purpose per row) rather than
--          pills/cards. Some stories have enough detail to justify that;
--          others just have a flat list of technology names with no
--          per-item purpose text, for which plain pills (reusing the exact
--          .tech-item pill style already used on the Services "Platforms &
--          Technologies" section) read better. 'list' keeps today's
--          behavior unchanged for every existing story.

ALTER TABLE `success_stories`
    ADD COLUMN `techstack_display_mode` ENUM('list','pills') NOT NULL DEFAULT 'list' AFTER `tech_stack_items_json`;
