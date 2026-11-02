-- Migration: Add hire_pages.subcategory_id + assign existing hire pages
-- Created: 2026-11-02
-- Purpose: Each hire page belongs to at most one hire subcategory
--          (see 2026-11-02-003-create-hire-subcategories.sql).
--          Additive + nullable: no existing hire page is deleted, recreated
--          or otherwise modified apart from `subcategory_id`. Hire pages with
--          a NULL subcategory keep working everywhere.
--          ON DELETE RESTRICT: a subcategory still assigned to hire pages
--          cannot be deleted — reassign them first (admin checks up-front;
--          the FK is the backstop). Slugs/URLs/routing are NOT touched.

ALTER TABLE `hire_pages`
    ADD COLUMN `subcategory_id` INT UNSIGNED NULL AFTER `sort_order`,
    ADD KEY `idx_hire_pages_subcategory` (`subcategory_id`, `sort_order`),
    ADD CONSTRAINT `fk_hire_pages_subcategory` FOREIGN KEY (`subcategory_id`)
        REFERENCES `hire_subcategories` (`id`) ON DELETE RESTRICT;

-- Initial assignment of the 7 existing hire pages, matched by slug (never by
-- id). Unknown slugs are skipped; already-assigned rows are left alone.

UPDATE `hire_pages` h
    JOIN `hire_subcategories` c ON c.`slug` = 'ai-ml-engineers'
    SET h.`subcategory_id` = c.`id`
    WHERE h.`subcategory_id` IS NULL
      AND h.`slug` IN (
          'hire-ai-engineers',
          'hire-ai-ml-developers',
          'hire-machine-learning-engineers',
          'hire-ai-developers'
      );

UPDATE `hire_pages` h
    JOIN `hire_subcategories` c ON c.`slug` = 'specialized-ai-engineers'
    SET h.`subcategory_id` = c.`id`
    WHERE h.`subcategory_id` IS NULL
      AND h.`slug` IN (
          'hire-generative-ai-engineers',
          'hire-ai-agent-developer',
          'hire-nlp-engineers'
      );
