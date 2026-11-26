-- Migration: Display-style choice for a Success Story's Workflow section
-- Created: 2026-11-26
-- Purpose: The Workflow steps were always shown as an accordion. Admins can now
--          pick 'flow' to show the same steps as the numbered, arrow-connected
--          cards used by the Proposed Architecture section. 'accordion' keeps
--          today's behavior, so every existing story is unchanged.

ALTER TABLE `success_stories`
    ADD COLUMN `workflow_display_mode` ENUM('accordion','flow') NOT NULL DEFAULT 'accordion' AFTER `workflow_json`;
