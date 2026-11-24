-- Migration: Add optional per-post Author fields to posts
-- Created: 2026-10-03
-- Purpose: Let each blog post show an "Author" box (image, name,
--          designation, bio, LinkedIn link, expertise tags) on the
--          frontend detail page, entered independently per post —
--          mirrors team_members' shape (image/designation/expertise_json)
--          but intentionally NOT linked to that table, per product
--          decision to keep per-post authors self-contained. All columns
--          nullable/empty-default so every existing post keeps working
--          unchanged and the section is simply hidden until filled in.

ALTER TABLE `posts`
    ADD COLUMN `author_image`          VARCHAR(500) NOT NULL DEFAULT '' AFTER `author_id`,
    ADD COLUMN `author_name`           VARCHAR(150) NOT NULL DEFAULT '' AFTER `author_image`,
    ADD COLUMN `author_designation`    VARCHAR(200) NOT NULL DEFAULT '' AFTER `author_name`,
    ADD COLUMN `author_description`    TEXT NULL AFTER `author_designation`,
    ADD COLUMN `author_linkedin_url`   VARCHAR(500) NOT NULL DEFAULT '' AFTER `author_description`,
    ADD COLUMN `author_expertise_json` JSON NULL AFTER `author_linkedin_url`; -- string[] of pill tags
