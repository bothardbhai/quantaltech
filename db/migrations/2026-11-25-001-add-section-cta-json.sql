-- Migration: Per-section CTA button + before/after text (services, hire, success stories)
-- Created: 2026-11-25
-- Purpose: Lets an admin add, to any content section, an optional block of
--          text above a button, the button itself (label + URL + new-tab),
--          and text below it. Stored as ONE JSON object per page keyed by
--          section (e.g. {"overview": {"before": "<p>…</p>", "btn_text":
--          "Book a call", "btn_url": "/contact", "new_tab": 0, "after": ""}})
--          rather than ~5 columns x ~15 sections per table. NULL/missing key
--          = nothing rendered, so every existing page is unchanged.

ALTER TABLE `services`
    ADD COLUMN `section_cta_json` JSON NULL;

ALTER TABLE `hire_pages`
    ADD COLUMN `section_cta_json` JSON NULL;

ALTER TABLE `success_stories`
    ADD COLUMN `section_cta_json` JSON NULL;
