-- Migration: Add editable FAQ section heading to services
-- Created: 2026-09-22
-- Purpose: The "Frequently Asked Questions" H3 above the FAQ intro/items is
--          a hardcoded literal with no admin override — faq_intro and the
--          faqs_json repeater are already editable, only the heading itself
--          was missing. Falls back to "Frequently Asked Questions" when
--          blank, so no existing published service changes appearance.

ALTER TABLE `services`
    ADD COLUMN `faq_title` VARCHAR(255) NOT NULL DEFAULT '' AFTER `faq_intro`;
