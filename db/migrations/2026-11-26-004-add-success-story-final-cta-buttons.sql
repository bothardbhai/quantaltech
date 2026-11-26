-- Migration: Editable buttons in a Success Story's bottom contact section
-- Created: 2026-11-26
-- Purpose: Adds an optional extra button next to the phone/email list (e.g. "Book a
--          30-min walkthrough") and lets the form's "Send message" / "Reset" button
--          labels be changed (or Reset hidden). One JSON column:
--          {"walk": {text,url,new_tab}, "send_text", "reset_text", "hide_reset"}.
--          NULL = today's layout and labels, unchanged.

ALTER TABLE `success_stories`
    ADD COLUMN `final_cta_buttons_json` JSON NULL AFTER `final_cta_desc`;
