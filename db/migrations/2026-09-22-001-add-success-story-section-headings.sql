-- Migration: Add editable heading/intro fields to 8 Success Story sections
-- Created: 2026-09-22
-- Purpose: "Our Client", Objectives, Proposed Architecture, Our Workflow,
--          Results & Impact, What We Delivered, Our Technology Stack, and
--          Why Choose Our Solution currently render a hardcoded eyebrow +
--          H2 with no admin override, while their repeater items are
--          already editable. This brings them up to the same
--          *_sub/*_title/*_intro_html shape already used for Client
--          Responsibilities / Future Enhancements (see
--          2026-08-12-002-extend-success-stories-table.sql), except the new
--          intro field is CKEditor-enabled trusted HTML (MEDIUMTEXT) rather
--          than a plain single-line VARCHAR, per the site's admin-content
--          fields (see 2026-07-28-001-extend-services-table.sql for the
--          equivalent *_title_html precedent).
--
--          *_title stays a plain VARCHAR (not HTML) — these are short
--          section headings, matching the existing responsibilities_title/
--          future_title convention. When blank, the template falls back to
--          the section's current hardcoded default text, so no existing
--          published story changes appearance until an editor opts in.

ALTER TABLE `success_stories`
    -- Our Client (new — previously had no heading override at all;
    -- existing intro/body content stays on the `excerpt`/`body_html`
    -- columns, untouched). Prefixed "ourclient_", NOT "client_" — that
    -- prefix is already taken by the unrelated client_name/client_title
    -- (testimonial job title)/client_image columns.
    ADD COLUMN `ourclient_sub` VARCHAR(150) NOT NULL DEFAULT '',
    ADD COLUMN `ourclient_title` VARCHAR(255) NOT NULL DEFAULT '',
    ADD COLUMN `ourclient_intro_html` MEDIUMTEXT NULL,

    -- Objectives
    ADD COLUMN `objectives_sub` VARCHAR(150) NOT NULL DEFAULT '',
    ADD COLUMN `objectives_title` VARCHAR(255) NOT NULL DEFAULT '',
    ADD COLUMN `objectives_intro_html` MEDIUMTEXT NULL,

    -- Proposed Architecture
    ADD COLUMN `architecture_sub` VARCHAR(150) NOT NULL DEFAULT '',
    ADD COLUMN `architecture_title` VARCHAR(255) NOT NULL DEFAULT '',
    ADD COLUMN `architecture_intro_html` MEDIUMTEXT NULL,

    -- Our Workflow
    ADD COLUMN `workflow_sub` VARCHAR(150) NOT NULL DEFAULT '',
    ADD COLUMN `workflow_title` VARCHAR(255) NOT NULL DEFAULT '',
    ADD COLUMN `workflow_intro_html` MEDIUMTEXT NULL,

    -- Results & Impact
    ADD COLUMN `results_sub` VARCHAR(150) NOT NULL DEFAULT '',
    ADD COLUMN `results_title` VARCHAR(255) NOT NULL DEFAULT '',
    ADD COLUMN `results_intro_html` MEDIUMTEXT NULL,

    -- What We Delivered
    ADD COLUMN `deliverables_sub` VARCHAR(150) NOT NULL DEFAULT '',
    ADD COLUMN `deliverables_title` VARCHAR(255) NOT NULL DEFAULT '',
    ADD COLUMN `deliverables_intro_html` MEDIUMTEXT NULL,

    -- Our Technology Stack (prefixed "techstack_" — distinct from the
    -- existing unrelated `tech_stack_summary`/`tech_stack_items_json`)
    ADD COLUMN `techstack_sub` VARCHAR(150) NOT NULL DEFAULT '',
    ADD COLUMN `techstack_title` VARCHAR(255) NOT NULL DEFAULT '',
    ADD COLUMN `techstack_intro_html` MEDIUMTEXT NULL,

    -- Why Choose Our Solution (this section already has `why_cards_json`
    -- and a closing `why_final_html` paragraph that renders AFTER the
    -- cards — `why_intro_html` here is the new opening line, BEFORE them)
    ADD COLUMN `why_sub` VARCHAR(150) NOT NULL DEFAULT '',
    ADD COLUMN `why_title` VARCHAR(255) NOT NULL DEFAULT '',
    ADD COLUMN `why_intro_html` MEDIUMTEXT NULL;
