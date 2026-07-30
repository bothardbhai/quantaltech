-- =============================================================================
-- Quantal AI — database schema
-- =============================================================================
-- Stage 1: schema designed, ready to import. Stage 2 admin reads/writes to it.
-- Import via phpMyAdmin (Import tab) or:
--     mysql -u <user> -p <dbname> < schema.sql
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- users — single admin in v1, schema-ready for roles later
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `username`       VARCHAR(60)  NOT NULL,
    `email`          VARCHAR(190) NOT NULL,
    `password_hash`  VARCHAR(255) NOT NULL,         -- bcrypt via password_hash()
    `display_name`   VARCHAR(120) NOT NULL DEFAULT '',
    `role`           VARCHAR(20)  NOT NULL DEFAULT 'admin',  -- room for editor/author later
    `is_active`      TINYINT(1)   NOT NULL DEFAULT 1,
    `last_login_at`  DATETIME     NULL,
    `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_users_username` (`username`),
    UNIQUE KEY `uniq_users_email`    (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- pages — SEO metadata for file-based pages
--         The admin lists every .php file under /pages and lets you attach
--         metadata to it. The `path` column is the canonical URL ('/about',
--         '/services/ai-consulting', etc., '/' for the home page).
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pages` (
    `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `path`             VARCHAR(255) NOT NULL,        -- canonical URL path, e.g. '/about'
    `title`            VARCHAR(255) NOT NULL DEFAULT '',
    `meta_description` VARCHAR(320) NOT NULL DEFAULT '',
    `meta_keywords`    VARCHAR(255) NOT NULL DEFAULT '',
    `og_image`         VARCHAR(500) NOT NULL DEFAULT '',  -- relative path or absolute URL
    `canonical`        VARCHAR(500) NOT NULL DEFAULT '',  -- override; empty = auto from SITE_URL + path
    `schema_json`      MEDIUMTEXT   NOT NULL,             -- raw JSON-LD; admin validates
    `is_published`     TINYINT(1)   NOT NULL DEFAULT 1,
    `template_path`    VARCHAR(500) NOT NULL DEFAULT '',  -- the .php file on disk (informational)
    `notes`            TEXT         NOT NULL,             -- admin-only notes
    `created_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_pages_path` (`path`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- services — full section-based Service Master. Scalar columns hold each
--            section's subtitle/heading/text; `*_json` columns hold that
--            section's repeater rows. See pages/services/_subservice.php's
--            doc block for the exact per-section field contract this mirrors.
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `services` (
    `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `slug`              VARCHAR(120) NOT NULL,
    `name`              VARCHAR(120) NOT NULL,
    `title`             VARCHAR(255) NOT NULL,
    `excerpt`           TEXT         NOT NULL,
    `description`       MEDIUMTEXT   NOT NULL,

    -- Service metadata
    `icon_class`        VARCHAR(100) NOT NULL DEFAULT 'flaticon-tech-interaction-1',
    `service_number`    INT UNSIGNED NOT NULL DEFAULT 1,  -- display order
    `featured_image`    VARCHAR(500) NOT NULL DEFAULT '',
    `featured_alt`      VARCHAR(255) NOT NULL DEFAULT '',

    -- Legacy detail sections (kept for backward compatibility; unused by the
    -- new card-based sections below, which replaced them)
    `overview_html`     MEDIUMTEXT   NOT NULL DEFAULT '',
    `features_html`     MEDIUMTEXT   NOT NULL DEFAULT '',
    `use_cases_html`    MEDIUMTEXT   NOT NULL DEFAULT '',
    `benefits_html`     MEDIUMTEXT   NOT NULL DEFAULT '',

    -- Service Overview feature cards: [{"icon","title","description"}].
    -- Legacy rows may hold a flat string array; normalized on read in PHP.
    `features_json`     JSON         NULL,

    -- Breadcrumb / page-banner
    `page_label`        VARCHAR(255) NOT NULL DEFAULT '',
    `crumb`              VARCHAR(150) NOT NULL DEFAULT '',

    -- Hero
    `hero_tag`          VARCHAR(150) NOT NULL DEFAULT '',
    `hero_title_html`   MEDIUMTEXT   NULL,
    `hero_desc`         TEXT         NULL,

    -- Powered-by platform strip
    `platform_title`    VARCHAR(255) NOT NULL DEFAULT '',
    `platforms_json`    JSON         NULL,

    -- Impact stats
    `impact_stats_json` JSON         NULL,

    -- Service overview
    `overview_sub`             VARCHAR(150) NOT NULL DEFAULT '',
    `overview_title_html`      MEDIUMTEXT   NULL,
    `overview_paragraphs_json` JSON         NULL,
    `overview_btn_text`        VARCHAR(100) NOT NULL DEFAULT '',

    -- Benefit cards
    `benefits_sub`        VARCHAR(150) NOT NULL DEFAULT '',
    `benefits_title_html` MEDIUMTEXT   NULL,
    `benefits_text`       TEXT         NULL,
    `benefit_cards_json`  JSON         NULL,

    -- Services grid
    `grid_sub`           VARCHAR(150) NOT NULL DEFAULT '',
    `grid_title_html`    MEDIUMTEXT   NULL,
    `grid_text`          TEXT         NULL,
    `grid_services_json` JSON         NULL,

    -- What you get
    `whatyouget_sub`         VARCHAR(150) NOT NULL DEFAULT '',
    `whatyouget_title_html`  MEDIUMTEXT   NULL,
    `whatyouget_text`        TEXT         NULL,
    `whatyouget_cards_json`  JSON         NULL,

    -- Industries
    `industries_sub`        VARCHAR(150) NOT NULL DEFAULT '',
    `industries_title_html` MEDIUMTEXT   NULL,
    `industries_text`       TEXT         NULL,
    `industries_json`       JSON         NULL,

    -- Framework
    `framework_sub`        VARCHAR(150) NOT NULL DEFAULT '',
    `framework_title_html` MEDIUMTEXT   NULL,
    `framework_text`       TEXT         NULL,
    `framework_steps_json` JSON         NULL,

    -- Why Choose Us
    `why_sub`        VARCHAR(150) NOT NULL DEFAULT '',
    `why_title_html` MEDIUMTEXT   NULL,
    `why_text`       TEXT         NULL,
    `why_cards_json` JSON         NULL,

    -- Engagement models
    `engagement_sub`         VARCHAR(150) NOT NULL DEFAULT '',
    `engagement_title_html`  MEDIUMTEXT   NULL,
    `engagement_text`        TEXT         NULL,
    `engagement_models_json` JSON         NULL,

    -- Process timeline
    `process_sub`        VARCHAR(150) NOT NULL DEFAULT '',
    `process_title_html` MEDIUMTEXT   NULL,
    `process_text`       TEXT         NULL,
    `process_steps_json` JSON         NULL,

    -- Mid CTA
    `cta_tag`        VARCHAR(150) NOT NULL DEFAULT '',
    `cta_title_html` MEDIUMTEXT   NULL,
    `cta_text`       TEXT         NULL,

    -- Case studies
    `cs_sub`             VARCHAR(150) NOT NULL DEFAULT '',
    `cs_title_html`      MEDIUMTEXT   NULL,
    `cs_text`            TEXT         NULL,
    `case_studies_json`  JSON         NULL,

    -- Tech stack
    `tech_sub`             VARCHAR(150) NOT NULL DEFAULT '',
    `tech_title_html`      MEDIUMTEXT   NULL,
    `tech_text`            TEXT         NULL,
    `tech_categories_json` JSON         NULL,

    -- Security & compliance
    `security_sub`          VARCHAR(150) NOT NULL DEFAULT '',
    `security_title_html`   MEDIUMTEXT   NULL,
    `security_text`         TEXT         NULL,
    `security_cards_json`   JSON         NULL,

    -- Related services (stores service IDs; URL resolved at render time)
    `related_sub`               VARCHAR(150) NOT NULL DEFAULT '',
    `related_title_html`        MEDIUMTEXT   NULL,
    `related_text`               TEXT        NULL,
    `related_group_title`       VARCHAR(150) NOT NULL DEFAULT '',
    `related_service_ids_json`  JSON         NULL,

    -- Knowledge hub (stores post IDs; content resolved at render time)
    `blog_sub`           VARCHAR(150) NOT NULL DEFAULT '',
    `blog_title_html`    MEDIUMTEXT   NULL,
    `blog_text`          TEXT         NULL,
    `blog_post_ids_json` JSON         NULL,

    -- FAQ
    `faq_intro` TEXT NULL,
    `faqs_json` JSON NULL,

    -- Content & SEO
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
    `display_on_home`   TINYINT(1)   NOT NULL DEFAULT 1,
    `author_id`         INT UNSIGNED NULL,

    -- Metadata
    `created_at`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_services_slug` (`slug`),
    KEY `idx_services_status_published` (`status`, `published_at`),
    KEY `idx_services_display_home` (`display_on_home`, `service_number`),
    KEY `idx_services_author` (`author_id`),
    CONSTRAINT `fk_services_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- categories — blog post categories
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `slug`         VARCHAR(120) NOT NULL,
    `name`         VARCHAR(120) NOT NULL,
    `description`  VARCHAR(500) NOT NULL DEFAULT '',
    `created_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_categories_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- tags — blog post tags
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tags` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `slug`       VARCHAR(120) NOT NULL,
    `name`       VARCHAR(120) NOT NULL,
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_tags_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- posts — blog entries
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `posts` (
    `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `slug`             VARCHAR(200) NOT NULL,
    `title`            VARCHAR(255) NOT NULL,
    `excerpt`          TEXT         NOT NULL,
    `body_html`        MEDIUMTEXT   NOT NULL,        -- CKEditor output; sanitized server-side on save
    `featured_image`   VARCHAR(500) NOT NULL DEFAULT '',
    `featured_alt`     VARCHAR(255) NOT NULL DEFAULT '',
    `author_id`        INT UNSIGNED NULL,
    `status`           ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
    `published_at`     DATETIME     NULL,
    -- Per-post SEO (overrides defaults)
    `meta_title`       VARCHAR(255) NOT NULL DEFAULT '',  -- empty = use post title
    `meta_description` VARCHAR(320) NOT NULL DEFAULT '',  -- empty = use excerpt
    `meta_keywords`    VARCHAR(255) NOT NULL DEFAULT '',
    `og_image`         VARCHAR(500) NOT NULL DEFAULT '',  -- empty = use featured_image
    `schema_json`      MEDIUMTEXT   NOT NULL,             -- per-post JSON-LD (Article schema typical)
    `created_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_posts_slug` (`slug`),
    KEY `idx_posts_status_published` (`status`, `published_at`),
    KEY `idx_posts_author` (`author_id`),
    CONSTRAINT `fk_posts_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- post_categories — many-to-many between posts and categories
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `post_categories` (
    `post_id`     INT UNSIGNED NOT NULL,
    `category_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`post_id`, `category_id`),
    KEY `idx_pc_category` (`category_id`),
    CONSTRAINT `fk_pc_post`     FOREIGN KEY (`post_id`)     REFERENCES `posts`      (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pc_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- post_tags — many-to-many between posts and tags
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `post_tags` (
    `post_id` INT UNSIGNED NOT NULL,
    `tag_id`  INT UNSIGNED NOT NULL,
    PRIMARY KEY (`post_id`, `tag_id`),
    KEY `idx_pt_tag` (`tag_id`),
    CONSTRAINT `fk_pt_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pt_tag`  FOREIGN KEY (`tag_id`)  REFERENCES `tags`  (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- media — uploaded files (images, mostly), tracked centrally so the admin can
--         show a media library and reuse images across posts/pages
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `media` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `path`          VARCHAR(500) NOT NULL,            -- relative to /public, e.g. '/uploads/blog/2025/img.jpg'
    `original_name` VARCHAR(255) NOT NULL,
    `category`      ENUM('blog','page','service','media') NOT NULL DEFAULT 'media',
    `mime_type`     VARCHAR(80)  NOT NULL,
    `size_bytes`    INT UNSIGNED NOT NULL,
    `width`         INT UNSIGNED NULL,
    `height`        INT UNSIGNED NULL,
    `alt_text`      VARCHAR(255) NOT NULL DEFAULT '',
    `uploaded_by`   INT UNSIGNED NULL,
    `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_media_uploader` (`uploaded_by`),
    CONSTRAINT `fk_media_uploader` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- settings — key/value site config editable via admin
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
    `key`        VARCHAR(80)  NOT NULL,
    `value`      MEDIUMTEXT   NOT NULL,
    `updated_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Seed data — site defaults; replace these in the admin once it's live
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO `settings` (`key`, `value`) VALUES
    ('site_name',    'Quantal AI'),
    ('description',  'Quantal AI builds production-grade AI solutions: intelligent document processing, AI agents, conversational AI, fraud detection, and process automation.'),
    ('og_image',     '/assets/images/quantal/brand/logo.png'),
    ('organization', '');

SET FOREIGN_KEY_CHECKS = 1;
