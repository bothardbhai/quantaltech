-- Migration: Extend success_stories table for the redesigned detail/listing pages
-- Created: 2026-08-12
-- Purpose: Success Stories predates the current Service Master / Hire Master
--          shape (see 2026-07-28-001-extend-services-table.sql and
--          2026-08-07-001-create-hire-pages-table.sql) — one flat table with
--          plain challenge/solution/results textareas and no repeaters,
--          categories, breadcrumb, or section-level SEO parity. This
--          migration brings it up to the same section-based shape: a scalar
--          *_sub/*_title triplet per section heading (where the section has
--          one) plus a *_json repeater column per section's card/list list,
--          so admin/success-stories.php can reuse the same tabbed-form +
--          repeater engine (admin/helpers.php) as Service/Hire.

ALTER TABLE `success_stories`
    -- Category (see 2026-08-12-001-create-success-story-categories-table.sql)
    ADD COLUMN `category_id` INT UNSIGNED NULL AFTER `industry`,

    -- Listing/hub order (mirrors hire_pages.sort_order)
    ADD COLUMN `sort_order` INT UNSIGNED NOT NULL DEFAULT 1 AFTER `featured`,

    -- Breadcrumb / page-banner (mirrors services.page_label / services.crumb)
    ADD COLUMN `page_label` VARCHAR(255) NOT NULL DEFAULT '' AFTER `slug`,
    ADD COLUMN `crumb` VARCHAR(150) NOT NULL DEFAULT '' AFTER `page_label`,

    -- Hero
    ADD COLUMN `hero_eyebrow` VARCHAR(150) NOT NULL DEFAULT '' AFTER `title`,

    -- Content + Information panel (Industry reuses the existing `industry`
    -- column; these four are new plain-text fields for the right-hand panel)
    ADD COLUMN `services_provided` VARCHAR(255) NOT NULL DEFAULT '' AFTER `company_size`,
    ADD COLUMN `tech_stack_summary` VARCHAR(255) NOT NULL DEFAULT '' AFTER `services_provided`,
    ADD COLUMN `third_party_services` VARCHAR(255) NOT NULL DEFAULT '' AFTER `tech_stack_summary`,
    ADD COLUMN `outcome_summary` VARCHAR(255) NOT NULL DEFAULT '' AFTER `third_party_services`,

    -- Objectives
    ADD COLUMN `objectives_json` JSON NULL,  -- [{"title","desc","active":bool}]

    -- Proposed Architecture (flow diagram steps)
    ADD COLUMN `architecture_json` JSON NULL,  -- [{"icon","title","subtitle","items":[],"active":bool}]

    -- The Challenge (reuses existing challenge_html for the body copy)
    ADD COLUMN `challenge_sub` VARCHAR(150) NOT NULL DEFAULT '' AFTER `challenge_html`,
    ADD COLUMN `challenge_title` VARCHAR(255) NOT NULL DEFAULT '' AFTER `challenge_sub`,
    ADD COLUMN `challenge_image` VARCHAR(500) NOT NULL DEFAULT '' AFTER `challenge_title`,

    -- The Solution (reuses existing solution_html for the body copy)
    ADD COLUMN `solution_sub` VARCHAR(150) NOT NULL DEFAULT '' AFTER `solution_html`,
    ADD COLUMN `solution_title` VARCHAR(255) NOT NULL DEFAULT '' AFTER `solution_sub`,
    ADD COLUMN `solution_image` VARCHAR(500) NOT NULL DEFAULT '' AFTER `solution_title`,

    -- Our Workflow (accordion steps)
    ADD COLUMN `workflow_json` JSON NULL,  -- [{"title","desc","active":bool}]

    -- Results & Impact (structured; existing results_html left as-is/unused by the new template)
    ADD COLUMN `results_json` JSON NULL,  -- [{"title","desc","active":bool}]

    -- What We Delivered
    ADD COLUMN `deliverables_json` JSON NULL,  -- [{"icon","title","desc","active":bool}]

    -- Our Technology Stack
    ADD COLUMN `tech_stack_items_json` JSON NULL,  -- [{"icon","name","purpose","active":bool}]

    -- Why Choose Our Solution (present in the redesigned template though not
    -- explicitly listed among the 14 named sections — kept manageable +
    -- conditional like every other section)
    ADD COLUMN `why_cards_json` JSON NULL,  -- [{"title","desc","active":bool}]

    -- Client Responsibilities
    ADD COLUMN `responsibilities_sub` VARCHAR(150) NOT NULL DEFAULT '',
    ADD COLUMN `responsibilities_title` VARCHAR(255) NOT NULL DEFAULT '',
    ADD COLUMN `responsibilities_text` VARCHAR(255) NOT NULL DEFAULT '',
    ADD COLUMN `responsibilities_json` JSON NULL,  -- [{"text","active":bool}]

    -- Future Enhancements (kept fully separate from Client Responsibilities)
    ADD COLUMN `future_sub` VARCHAR(150) NOT NULL DEFAULT '',
    ADD COLUMN `future_title` VARCHAR(255) NOT NULL DEFAULT '',
    ADD COLUMN `future_text` VARCHAR(255) NOT NULL DEFAULT '',
    ADD COLUMN `future_json` JSON NULL,  -- [{"text","active":bool}]

    -- More Case Studies (manual picks + how many to auto-fill with)
    ADD COLUMN `related_story_ids_json` JSON NULL,  -- int[] -> success_stories.id
    ADD COLUMN `related_count` TINYINT UNSIGNED NOT NULL DEFAULT 3,

    -- Final CTA band copy (this section pairs with the existing Contact Form,
    -- which has no button element of its own)
    ADD COLUMN `final_cta_sub` VARCHAR(150) NOT NULL DEFAULT '',
    ADD COLUMN `final_cta_title` VARCHAR(255) NOT NULL DEFAULT '',
    ADD COLUMN `final_cta_desc` TEXT NULL,

    -- SEO parity with services/hire_pages (meta_title/meta_description/
    -- meta_keywords/og_image/schema_json already exist on this table)
    ADD COLUMN `canonical` VARCHAR(500) NOT NULL DEFAULT '' AFTER `schema_json`,
    ADD COLUMN `robots` VARCHAR(50) NOT NULL DEFAULT '' AFTER `canonical`,

    ADD KEY `idx_success_stories_category` (`category_id`),
    ADD CONSTRAINT `fk_success_stories_category` FOREIGN KEY (`category_id`)
        REFERENCES `success_story_categories` (`id`) ON DELETE SET NULL;
