-- Newsletter subscribers table
-- Run: php db/migrate.php  (or execute manually in phpMyAdmin / MySQL CLI)

CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    email         VARCHAR(191) NOT NULL,
    status        ENUM('active', 'unsubscribed') NOT NULL DEFAULT 'active',
    source        VARCHAR(50)  NOT NULL DEFAULT 'footer',
    subscribed_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    unsubscribed_at DATETIME   NULL,
    ip_address    VARCHAR(45)  NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_email (email),
    KEY idx_status (status),
    KEY idx_subscribed_at (subscribed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
