-- Migration: Create post_faqs table
-- Created: 2026-07-22
-- Purpose: Store an ordered list of FAQ question/answer pairs per blog post.

CREATE TABLE IF NOT EXISTS `post_faqs` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `post_id`    INT UNSIGNED NOT NULL,
    `question`   VARCHAR(500) NOT NULL,
    `answer`     MEDIUMTEXT   NOT NULL,
    `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_post_faqs_post_sort` (`post_id`, `sort_order`),
    CONSTRAINT `fk_post_faqs_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
