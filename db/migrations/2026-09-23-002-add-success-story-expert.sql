-- Migration: Add "Meet the Expert" section to success_stories
-- Created: 2026-09-23
-- Purpose: Attach one profile from the new team_members table (see
--          2026-09-23-001-create-team-members-table.sql) to a Success
--          Story. expert_member_id is nullable — the section is entirely
--          hidden on the frontend when no member is picked. sub/title
--          follow the same fallback pattern as the other section headings
--          added in 2026-09-22-001 (blank = use the hardcoded default).

ALTER TABLE `success_stories`
    ADD COLUMN `expert_tag` VARCHAR(150) NOT NULL DEFAULT '',
    ADD COLUMN `expert_title` VARCHAR(255) NOT NULL DEFAULT '',
    ADD COLUMN `expert_member_id` INT UNSIGNED NULL,
    ADD KEY `idx_success_stories_expert_member` (`expert_member_id`),
    ADD CONSTRAINT `fk_success_stories_expert_member` FOREIGN KEY (`expert_member_id`)
        REFERENCES `team_members` (`id`) ON DELETE SET NULL;
