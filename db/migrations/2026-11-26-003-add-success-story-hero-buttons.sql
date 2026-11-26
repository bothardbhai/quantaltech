-- Migration: Editable hero buttons on a Success Story page
-- Created: 2026-11-26
-- Purpose: The two hero buttons ("Book a Free Scoping Call" -> /contact and
--          "View Case Studies" -> /success-stories) were hard-coded. Stores
--          per-story overrides as {"btn1": {text,url,new_tab,hide}, "btn2": {...}}.
--          NULL/blank fields fall back to those defaults, so every existing
--          story is unchanged.

ALTER TABLE `success_stories`
    ADD COLUMN `hero_buttons_json` JSON NULL AFTER `hero_eyebrow`;
