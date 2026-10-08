-- Migration: Add Hire Process section to hire_pages
-- Created: 2026-10-05
-- Purpose: New Hire Master section between Industries and Mid CTA — heading +
--          description + section image + accordion of process steps.
--          Shape mirrors services.process_steps_json but adds an image column
--          (hireprocess_image) since this section renders image-left /
--          accordion-right, unlike the full-width Services Process Accordion.

ALTER TABLE `hire_pages`
    ADD COLUMN `hireprocess_sub` VARCHAR(150) NOT NULL DEFAULT '' AFTER `industries_json`,
    ADD COLUMN `hireprocess_title_html` MEDIUMTEXT NULL AFTER `hireprocess_sub`,
    ADD COLUMN `hireprocess_text` TEXT NULL AFTER `hireprocess_title_html`,
    ADD COLUMN `hireprocess_image` VARCHAR(500) NOT NULL DEFAULT '' AFTER `hireprocess_text`,
    ADD COLUMN `hireprocess_steps_json` JSON NULL AFTER `hireprocess_image`; -- [{"title","desc"}]
