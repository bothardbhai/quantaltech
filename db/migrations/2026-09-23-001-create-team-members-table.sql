-- Migration: Create team_members table
-- Created: 2026-09-23
-- Purpose: A global, admin-managed roster of team/engineer profiles —
--          independent of any single hire_pages row — so one profile
--          (name, designation, experience, expertise tags, photo, external
--          profile link) can be entered once and attached to multiple
--          places. First consumer: Success Stories' "Meet the Expert"
--          section (success_stories.expert_member_id, see
--          2026-09-23-002-add-success-story-expert.sql). Deliberately flat
--          (no repeaters) — mirrors success_story_categories' simple CRUD
--          shape.

CREATE TABLE IF NOT EXISTS `team_members` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`            VARCHAR(150) NOT NULL,
    `designation`     VARCHAR(200) NOT NULL DEFAULT '',
    `experience_text` TEXT NULL,
    `expertise_json`  JSON NULL,  -- string[] of pill tags, e.g. ["AI","Python","RAG"]
    `image`           VARCHAR(500) NOT NULL DEFAULT '',
    `profile_url`     VARCHAR(500) NOT NULL DEFAULT '',
    `status`          ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `sort_order`      INT UNSIGNED NOT NULL DEFAULT 1,
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_team_members_status_sort` (`status`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
