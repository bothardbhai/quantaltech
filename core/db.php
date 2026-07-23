<?php
/**
 * Database connection — PDO singleton.
 *
 * Returns null if DB credentials are not configured, so the site continues
 * to render static pages even before DB setup. Stage 2 admin requires the DB.
 */

declare(strict_types=1);

/**
 * Returns a configured PDO instance, or null if the DB is not configured / unreachable.
 */
function db(): ?PDO
{
    static $pdo = null;
    static $tried = false;

    if ($pdo !== null) {
        return $pdo;
    }
    if ($tried) {
        // Don't keep retrying within a single request after the first failure.
        return null;
    }
    $tried = true;

    if (!defined('DB_HOST') || DB_HOST === '' || !defined('DB_NAME') || DB_NAME === '') {
        return null;
    }

    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_NAME);
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        // In production, log and continue rendering. Don't expose DB errors to users.
        error_log('DB connection failed: ' . $e->getMessage());
        $pdo = null;
    }

    return $pdo;
}
