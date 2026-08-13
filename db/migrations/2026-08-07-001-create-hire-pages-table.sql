-- Migration: Create hire_pages table
-- Created: 2026-08-07
-- Purpose: Hire Master — the dynamic counterpart of the Service Master
--          (see db/migrations/2025-05-11-003-create-services-table.sql and
--          2026-07-28-001-extend-services-table.sql). Each row is one
--          hire-able role page (e.g. python-developer, llm-engineer),
--          reachable at /hire/{slug}. Follows the exact same
--          section-based shape as `services`: a scalar *_sub/*_title_html/
--          *_text triplet per section heading, plus a *_json repeater
--          column per section's card list, so admin/hire.php can reuse the
--          same tabbed-form + repeater pattern as admin/services.php.

CREATE TABLE IF NOT EXISTS `hire_pages` (
    `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `slug`              VARCHAR(120) NOT NULL,
    `name`              VARCHAR(120) NOT NULL,
    `title`             VARCHAR(255) NOT NULL,
    `excerpt`           TEXT         NOT NULL,
    `role_label`        VARCHAR(150) NOT NULL DEFAULT '',

    -- Card / display metadata (used on the /hire-ai-engineers hub grid)
    `icon_class`        VARCHAR(100) NOT NULL DEFAULT 'fas fa-brain',
    `sort_order`        INT UNSIGNED NOT NULL DEFAULT 1,
    `featured_image`    VARCHAR(500) NOT NULL DEFAULT '',
    `featured_alt`      VARCHAR(255) NOT NULL DEFAULT '',

    -- Breadcrumb / page-banner (mirrors services.page_label / services.crumb)
    `page_label`        VARCHAR(255) NOT NULL DEFAULT '',
    `crumb`              VARCHAR(150) NOT NULL DEFAULT '',

    -- Hero
    `hero_tag`          VARCHAR(150) NOT NULL DEFAULT '',
    `hero_title_html`   MEDIUMTEXT   NULL,
    `hero_desc`         TEXT         NULL,
    `hero_features_json` JSON        NULL,  -- [{"icon","text"}] (the checkmark bullet list)

    -- Impact stats (the 4-card strip under the hero)
    `impact_stats_json` JSON         NULL,  -- [{"number","title"}]

    -- Expertise of Our Engineers
    `expertise_sub`         VARCHAR(150) NOT NULL DEFAULT '',
    `expertise_title_html`  MEDIUMTEXT   NULL,
    `expertise_text`        TEXT         NULL,
    `expertise_cards_json`  JSON         NULL,  -- [{"icon","title","desc"}]

    -- What Our Engineers Build
    `build_sub`         VARCHAR(150) NOT NULL DEFAULT '',
    `build_title_html`  MEDIUMTEXT   NULL,
    `build_text`        TEXT         NULL,
    `build_cards_json`  JSON         NULL,  -- [{"number","title","desc"}]

    -- Engagement models
    `engagement_sub`           VARCHAR(150) NOT NULL DEFAULT '',
    `engagement_title_html`    MEDIUMTEXT   NULL,
    `engagement_text`          TEXT         NULL,
    `engagement_models_json`   JSON         NULL,  -- [{"icon","title","desc","featured":bool}]

    -- Why Hire
    `why_sub`           VARCHAR(150) NOT NULL DEFAULT '',
    `why_title_html`    MEDIUMTEXT   NULL,
    `why_text`          TEXT         NULL,
    `why_cards_json`    JSON         NULL,  -- [{"title","desc"}]

    -- Industries
    `industries_sub`         VARCHAR(150) NOT NULL DEFAULT '',
    `industries_title_html`  MEDIUMTEXT   NULL,
    `industries_text`        TEXT         NULL,
    `industries_json`        JSON         NULL,  -- [{"icon","title","desc"}]

    -- Mid CTA band
    `cta_tag`           VARCHAR(150) NOT NULL DEFAULT '',
    `cta_title_html`    MEDIUMTEXT   NULL,
    `cta_text`          TEXT         NULL,

    -- Case studies
    `cs_sub`             VARCHAR(150) NOT NULL DEFAULT '',
    `cs_title_html`      MEDIUMTEXT   NULL,
    `cs_text`            TEXT         NULL,
    `case_studies_json`  JSON         NULL,  -- [{"title","desc","image"}]

    -- Related hire pages + related services (store IDs; resolved at render time)
    `related_sub`               VARCHAR(150) NOT NULL DEFAULT '',
    `related_title_html`        MEDIUMTEXT   NULL,
    `related_text`               TEXT        NULL,
    `related_hire_ids_json`     JSON         NULL,  -- int[] -> hire_pages.id
    `related_service_ids_json`  JSON         NULL,  -- int[] -> services.id

    -- Knowledge hub (stores post IDs; image/title/link resolved at render time)
    `blog_sub`           VARCHAR(150) NOT NULL DEFAULT '',
    `blog_title_html`    MEDIUMTEXT   NULL,
    `blog_text`          TEXT         NULL,
    `blog_post_ids_json` JSON         NULL,  -- int[] -> posts.id

    -- FAQ
    `faq_intro`  TEXT NULL,
    `faqs_json`  JSON NULL,  -- [{"question","answer"}]

    -- Final CTA band
    `final_cta_title_html` MEDIUMTEXT   NULL,
    `final_cta_desc`       TEXT         NULL,
    `final_cta_btn_text`   VARCHAR(150) NOT NULL DEFAULT '',
    `final_cta_btn_url`    VARCHAR(500) NOT NULL DEFAULT '',

    -- SEO (parity with services)
    `meta_title`        VARCHAR(255) NOT NULL DEFAULT '',
    `meta_description`  VARCHAR(320) NOT NULL DEFAULT '',
    `meta_keywords`     VARCHAR(255) NOT NULL DEFAULT '',
    `og_image`          VARCHAR(500) NOT NULL DEFAULT '',
    `schema_json`       MEDIUMTEXT   NOT NULL,
    `canonical`         VARCHAR(500) NOT NULL DEFAULT '',
    `robots`            VARCHAR(50)  NOT NULL DEFAULT '',

    -- Status & Publishing
    `status`            ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
    `published_at`      DATETIME     NULL,
    `display_on_hub`    TINYINT(1)   NOT NULL DEFAULT 1,
    `author_id`         INT UNSIGNED NULL,

    -- Metadata
    `created_at`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_hire_pages_slug` (`slug`),
    KEY `idx_hire_pages_status_published` (`status`, `published_at`),
    KEY `idx_hire_pages_display_hub` (`display_on_hub`, `sort_order`),
    KEY `idx_hire_pages_author` (`author_id`),
    CONSTRAINT `fk_hire_pages_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
