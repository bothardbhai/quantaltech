-- Migration: Add per-story expertise subset override for "Meet the Expert"
-- Created: 2026-09-24
-- Purpose: A team_members profile carries their FULL expertise list (e.g.
--          9 tags), but only some of those skills may be relevant to any
--          one project. Lets an admin pick which of the picked expert's
--          tags to actually show on THIS story. NULL/empty = show every
--          tag from the member's profile (today's behavior, unchanged for
--          every existing story).

ALTER TABLE `success_stories`
    ADD COLUMN `expert_expertise_json` JSON NULL AFTER `expert_member_id`;
