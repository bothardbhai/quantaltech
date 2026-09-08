-- Migration: Add Foundation Model Expertise + Our Tech sections to hire_pages
-- Created: 2026-09-08
-- Purpose: Two new Hire Master sections, inserted (on the frontend) between
--          Expertise and What They Build:
--            - Foundation Model Expertise: same shape as `industries_json`
--              (icon-card grid) but with a logo/image path instead of an
--              icon class — see foundation_cards_json.
--            - Our Tech: same shape as services.tech_categories_json
--              (category title + newline-list of items), duplicated onto
--              hire_pages the same way industries_json already is
--              duplicated between `services` and `hire_pages`.

ALTER TABLE `hire_pages`
    ADD COLUMN `foundation_sub` VARCHAR(150) NOT NULL DEFAULT '' AFTER `expertise_cards_json`,
    ADD COLUMN `foundation_title_html` MEDIUMTEXT NULL AFTER `foundation_sub`,
    ADD COLUMN `foundation_text` TEXT NULL AFTER `foundation_title_html`,
    ADD COLUMN `foundation_cards_json` JSON NULL AFTER `foundation_text`, -- [{"image","title","desc"}]
    ADD COLUMN `tech_sub` VARCHAR(150) NOT NULL DEFAULT '' AFTER `foundation_cards_json`,
    ADD COLUMN `tech_title_html` MEDIUMTEXT NULL AFTER `tech_sub`,
    ADD COLUMN `tech_text` TEXT NULL AFTER `tech_title_html`,
    ADD COLUMN `tech_categories_json` JSON NULL AFTER `tech_text`; -- [{"title","items":[...]}]
