-- Migration: Add Final CTA fields to services table
-- Created: 2026-07-30
-- Purpose: The service detail page grew a new "Final CTA" band at the very
--          bottom (distinct from the existing mid-page CTA — cta_tag/
--          cta_title_html/cta_text). It was hardcoded; this makes it
--          fully admin-editable, matching the pattern used everywhere else
--          (an *_html field for the heading, plain fields for the rest).

ALTER TABLE `services`
    ADD COLUMN `final_cta_title_html` MEDIUMTEXT    NULL AFTER `faqs_json`,
    ADD COLUMN `final_cta_desc`       TEXT          NULL AFTER `final_cta_title_html`,
    ADD COLUMN `final_cta_btn_text`   VARCHAR(150)  NOT NULL DEFAULT '' AFTER `final_cta_desc`,
    ADD COLUMN `final_cta_btn_url`    VARCHAR(500)  NOT NULL DEFAULT '' AFTER `final_cta_btn_text`;
