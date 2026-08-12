-- Migration: Add why_final_html to success_stories
-- Created: 2026-08-12
-- Purpose: "Why Choose Our Solution" needs one optional closing paragraph
--          that renders after all existing why-cards, as plain content (no
--          box/card) — see admin/success-stories.php's Why Choose tab and
--          pages/success-stories/single.php section 11.

ALTER TABLE `success_stories`
    ADD COLUMN `why_final_html` MEDIUMTEXT NULL AFTER `why_cards_json`;
