-- Migration: Extend services table for the full section-based Service Master
-- Created: 2026-07-28
-- Purpose: The service-detail page (_subservice.php) grew into a ~20-section
--          design (hero, impact stats, benefits, engagement models, case
--          studies, etc.) that only one hand-coded page (ai-engineering.php)
--          currently drives. This adds a scalar column per section
--          subtitle/heading/text field, and a JSON column per repeater
--          section, so any service can be fully authored from the admin.
--          `features_json` is repurposed (comment updated) to hold the new
--          Service Overview feature cards ({icon,title,description}) instead
--          of its old flat string-array shape; existing rows with the old
--          shape are normalized in PHP on read, not migrated here.

ALTER TABLE `services`
    -- Breadcrumb / page-banner contract fields (see _subservice.php doc block)
    ADD COLUMN `page_label`            VARCHAR(255)  NOT NULL DEFAULT '' AFTER `title`,
    ADD COLUMN `crumb`                 VARCHAR(150)  NOT NULL DEFAULT '' AFTER `page_label`,

    -- Hero
    ADD COLUMN `hero_tag`              VARCHAR(150)  NOT NULL DEFAULT '' AFTER `crumb`,
    ADD COLUMN `hero_title_html`       MEDIUMTEXT    NULL AFTER `hero_tag`,
    ADD COLUMN `hero_desc`             TEXT          NULL AFTER `hero_title_html`,

    -- Powered-by platform strip
    ADD COLUMN `platform_title`        VARCHAR(255)  NOT NULL DEFAULT '' AFTER `hero_desc`,
    ADD COLUMN `platforms_json`        JSON          NULL AFTER `platform_title`,

    -- Impact stats
    ADD COLUMN `impact_stats_json`     JSON          NULL AFTER `platforms_json`,

    -- Service overview (overview_features reuses `features_json`, see below)
    ADD COLUMN `overview_sub`          VARCHAR(150)  NOT NULL DEFAULT '' AFTER `impact_stats_json`,
    ADD COLUMN `overview_title_html`   MEDIUMTEXT    NULL AFTER `overview_sub`,
    ADD COLUMN `overview_paragraphs_json` JSON       NULL AFTER `overview_title_html`,
    ADD COLUMN `overview_btn_text`     VARCHAR(100)  NOT NULL DEFAULT '' AFTER `overview_paragraphs_json`,

    -- Benefit cards ("Why AI" / real-example boxes)
    ADD COLUMN `benefits_sub`         VARCHAR(150)  NOT NULL DEFAULT '' AFTER `overview_btn_text`,
    ADD COLUMN `benefits_title_html`  MEDIUMTEXT    NULL AFTER `benefits_sub`,
    ADD COLUMN `benefits_text`        TEXT          NULL AFTER `benefits_title_html`,
    ADD COLUMN `benefit_cards_json`   JSON          NULL AFTER `benefits_text`,

    -- Services grid ("What we build")
    ADD COLUMN `grid_sub`             VARCHAR(150)  NOT NULL DEFAULT '' AFTER `benefit_cards_json`,
    ADD COLUMN `grid_title_html`      MEDIUMTEXT    NULL AFTER `grid_sub`,
    ADD COLUMN `grid_text`            TEXT          NULL AFTER `grid_title_html`,
    ADD COLUMN `grid_services_json`   JSON          NULL AFTER `grid_text`,

    -- What you get
    ADD COLUMN `whatyouget_sub`         VARCHAR(150) NOT NULL DEFAULT '' AFTER `grid_services_json`,
    ADD COLUMN `whatyouget_title_html`  MEDIUMTEXT   NULL AFTER `whatyouget_sub`,
    ADD COLUMN `whatyouget_text`        TEXT         NULL AFTER `whatyouget_title_html`,
    ADD COLUMN `whatyouget_cards_json`  JSON         NULL AFTER `whatyouget_text`,

    -- Industries / use cases
    ADD COLUMN `industries_sub`         VARCHAR(150) NOT NULL DEFAULT '' AFTER `whatyouget_cards_json`,
    ADD COLUMN `industries_title_html`  MEDIUMTEXT   NULL AFTER `industries_sub`,
    ADD COLUMN `industries_text`        TEXT         NULL AFTER `industries_title_html`,
    ADD COLUMN `industries_json`        JSON         NULL AFTER `industries_text`,

    -- Framework / methodology
    ADD COLUMN `framework_sub`          VARCHAR(150) NOT NULL DEFAULT '' AFTER `industries_json`,
    ADD COLUMN `framework_title_html`   MEDIUMTEXT   NULL AFTER `framework_sub`,
    ADD COLUMN `framework_text`         TEXT         NULL AFTER `framework_title_html`,
    ADD COLUMN `framework_steps_json`   JSON         NULL AFTER `framework_text`,

    -- Why Choose Us
    ADD COLUMN `why_sub`                VARCHAR(150) NOT NULL DEFAULT '' AFTER `framework_steps_json`,
    ADD COLUMN `why_title_html`         MEDIUMTEXT   NULL AFTER `why_sub`,
    ADD COLUMN `why_text`               TEXT         NULL AFTER `why_title_html`,
    ADD COLUMN `why_cards_json`         JSON         NULL AFTER `why_text`,

    -- Engagement models
    ADD COLUMN `engagement_sub`           VARCHAR(150) NOT NULL DEFAULT '' AFTER `why_cards_json`,
    ADD COLUMN `engagement_title_html`    MEDIUMTEXT   NULL AFTER `engagement_sub`,
    ADD COLUMN `engagement_text`          TEXT         NULL AFTER `engagement_title_html`,
    ADD COLUMN `engagement_models_json`   JSON         NULL AFTER `engagement_text`,

    -- Process timeline
    ADD COLUMN `process_sub`            VARCHAR(150) NOT NULL DEFAULT '' AFTER `engagement_models_json`,
    ADD COLUMN `process_title_html`     MEDIUMTEXT   NULL AFTER `process_sub`,
    ADD COLUMN `process_text`           TEXT         NULL AFTER `process_title_html`,
    ADD COLUMN `process_steps_json`     JSON         NULL AFTER `process_text`,

    -- Mid CTA band
    ADD COLUMN `cta_tag`                VARCHAR(150) NOT NULL DEFAULT '' AFTER `process_steps_json`,
    ADD COLUMN `cta_title_html`         MEDIUMTEXT   NULL AFTER `cta_tag`,
    ADD COLUMN `cta_text`               TEXT         NULL AFTER `cta_title_html`,

    -- Case studies
    ADD COLUMN `cs_sub`                 VARCHAR(150) NOT NULL DEFAULT '' AFTER `cta_text`,
    ADD COLUMN `cs_title_html`          MEDIUMTEXT   NULL AFTER `cs_sub`,
    ADD COLUMN `cs_text`                TEXT         NULL AFTER `cs_title_html`,
    ADD COLUMN `case_studies_json`      JSON         NULL AFTER `cs_text`,

    -- Tech stack
    ADD COLUMN `tech_sub`               VARCHAR(150) NOT NULL DEFAULT '' AFTER `case_studies_json`,
    ADD COLUMN `tech_title_html`        MEDIUMTEXT   NULL AFTER `tech_sub`,
    ADD COLUMN `tech_text`              TEXT         NULL AFTER `tech_title_html`,
    ADD COLUMN `tech_categories_json`   JSON         NULL AFTER `tech_text`,

    -- Security & compliance
    ADD COLUMN `security_sub`           VARCHAR(150) NOT NULL DEFAULT '' AFTER `tech_categories_json`,
    ADD COLUMN `security_title_html`    MEDIUMTEXT   NULL AFTER `security_sub`,
    ADD COLUMN `security_text`          TEXT         NULL AFTER `security_title_html`,
    ADD COLUMN `security_cards_json`    JSON         NULL AFTER `security_text`,

    -- Related services (stores service IDs; URL generated at render time)
    ADD COLUMN `related_sub`            VARCHAR(150) NOT NULL DEFAULT '' AFTER `security_cards_json`,
    ADD COLUMN `related_title_html`     MEDIUMTEXT   NULL AFTER `related_sub`,
    ADD COLUMN `related_text`           TEXT         NULL AFTER `related_title_html`,
    ADD COLUMN `related_group_title`    VARCHAR(150) NOT NULL DEFAULT '' AFTER `related_text`,
    ADD COLUMN `related_service_ids_json` JSON       NULL AFTER `related_group_title`,

    -- Knowledge hub (stores post IDs; image/title/category/link resolved at render time)
    ADD COLUMN `blog_sub`               VARCHAR(150) NOT NULL DEFAULT '' AFTER `related_service_ids_json`,
    ADD COLUMN `blog_title_html`        MEDIUMTEXT   NULL AFTER `blog_sub`,
    ADD COLUMN `blog_text`              TEXT         NULL AFTER `blog_title_html`,
    ADD COLUMN `blog_post_ids_json`     JSON         NULL AFTER `blog_text`,

    -- FAQ
    ADD COLUMN `faq_intro`              TEXT         NULL AFTER `blog_post_ids_json`,
    ADD COLUMN `faqs_json`              JSON         NULL AFTER `faq_intro`,

    -- SEO parity with pages.php/blog.php (canonical + robots were missing)
    ADD COLUMN `canonical`              VARCHAR(500) NOT NULL DEFAULT '' AFTER `schema_json`,
    ADD COLUMN `robots`                 VARCHAR(50)  NOT NULL DEFAULT '' AFTER `canonical`,

    -- features_json now holds Service Overview feature cards:
    -- [{"icon":"fas fa-brain","title":"...","description":"..."}]
    -- (old rows may still hold a flat string array; normalized on read in PHP)
    MODIFY COLUMN `features_json` JSON NULL COMMENT 'Service Overview feature cards: [{"icon","title","description"}]. Legacy rows may hold a flat string array.';
