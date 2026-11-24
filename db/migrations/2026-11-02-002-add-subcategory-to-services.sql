-- Migration: Add services.subcategory_id + assign existing services
-- Created: 2026-11-02
-- Purpose: Each service belongs to at most one service subcategory
--          (see 2026-11-02-001-create-service-subcategories.sql).
--          Additive + nullable: no existing service row is deleted, recreated
--          or otherwise modified apart from `subcategory_id`. Services with a
--          NULL subcategory keep working everywhere.
--          ON DELETE RESTRICT: a subcategory still assigned to services cannot
--          be deleted — services must be reassigned first (the admin page
--          checks this up-front and shows a message; the FK is the backstop).
--          Slugs/URLs/routing are NOT touched.

ALTER TABLE `services`
    ADD COLUMN `subcategory_id` INT UNSIGNED NULL AFTER `service_number`,
    ADD KEY `idx_services_subcategory` (`subcategory_id`, `service_number`),
    ADD CONSTRAINT `fk_services_subcategory` FOREIGN KEY (`subcategory_id`)
        REFERENCES `service_subcategories` (`id`) ON DELETE RESTRICT;

-- Initial assignment of the 14 existing services, matched by slug (never by
-- id, since ids differ between environments). Slugs that don't exist in a
-- given database are simply skipped; already-assigned services are left alone.

UPDATE `services` s
    JOIN `service_subcategories` c ON c.`slug` = 'build-ai'
    SET s.`subcategory_id` = c.`id`
    WHERE s.`subcategory_id` IS NULL
      AND s.`slug` IN (
          'ai-engineering-services',
          'ai-ml-services',
          'large-language-model-development-company',
          'generative-ai-services',
          'chatgpt-developers'
      );

UPDATE `services` s
    JOIN `service_subcategories` c ON c.`slug` = 'ai-consulting'
    SET s.`subcategory_id` = c.`id`
    WHERE s.`subcategory_id` IS NULL
      AND s.`slug` IN (
          'ai-strategy-consulting',
          'ai-consulting-services-company',
          'generative-ai-consulting',
          'mlops-consulting-services'
      );

UPDATE `services` s
    JOIN `service_subcategories` c ON c.`slug` = 'ai-applications'
    SET s.`subcategory_id` = c.`id`
    WHERE s.`subcategory_id` IS NULL
      AND s.`slug` IN (
          'voice-ai',
          'text-ai',
          'image-document-ai',
          'process-automation',
          'ai-chatbot-development-company'
      );
