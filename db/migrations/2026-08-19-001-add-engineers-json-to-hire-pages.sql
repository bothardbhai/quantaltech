-- Migration: Add engineers_json to hire_pages
-- Created: 2026-08-19
-- Purpose: "Meet Our Engineers" section — a first-class section of the Hire
-- Master, not a Related/picker feature. Engineers belong to one specific
-- hire page, the same way impact_stats_json/expertise_cards_json do: a JSON
-- array of row objects edited via the object-repeater engine
-- (admin/helpers.php's svc_build_repeater()/svc_repeater_field()).
--
-- Row shape: [{image, name, role, years_of_experience, linkedin_url,
--   education: [string, ...], skills: [string, ...]}, ...]
-- (education/skills are flat string-array sub-fields within each row,
-- same "list" field type already used by services.grid_services_json's
-- "tags" sub-field — see admin/services.php.)

ALTER TABLE `hire_pages`
    ADD COLUMN `engineers_json` JSON NULL AFTER `impact_stats_json`;
