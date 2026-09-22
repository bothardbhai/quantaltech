-- Migration: Add editable FAQ heading, hero contact-card heading, and
--            "Meet Our Engineers" heading to hire_pages
-- Created: 2026-09-22
-- Purpose: Three hardcoded H2/H3 literals with no admin override:
--            - FAQ heading (faq_intro + faqs_json are already editable)
--            - The inline hero contact-card heading ("Let's Build Your AI Team")
--            - "Meet Our Engineers" (the $engineers repeater itself is
--              already editable via the existing 'engineers' admin tab)
--          All fall back to their current hardcoded text when blank, so no
--          existing published hire page changes appearance.

ALTER TABLE `hire_pages`
    ADD COLUMN `faq_title` VARCHAR(255) NOT NULL DEFAULT '' AFTER `faq_intro`,
    ADD COLUMN `hero_card_title` VARCHAR(255) NOT NULL DEFAULT '',
    ADD COLUMN `engineers_title` VARCHAR(255) NOT NULL DEFAULT '';
