<?php
/**
 * Database migration runner
 * 
 * Scans db/migrations/*.sql files and executes pending migrations.
 * Tracks executed migrations in a migrations_log table.
 */

declare(strict_types=1);

/**
 * Get the database connection
 */
function get_migration_db(): ?\PDO
{
    if (!defined('DB_HOST') || !defined('DB_NAME')) {
        error_log('Migration: DB configuration not defined');
        return null;
    }

    try {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        error_log('Migration DB Connection Error: ' . $e->getMessage());
        return null;
    }
}

/**
 * Create migrations_log table if it doesn't exist
 */
function ensure_migrations_log_table(\PDO $pdo): bool
{
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `migrations_log` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `migration_file` VARCHAR(255) NOT NULL UNIQUE,
                `executed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uniq_migration_file` (`migration_file`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        return true;
    } catch (PDOException $e) {
        error_log('Failed to create migrations_log table: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get list of pending migrations
 */
function get_pending_migrations(\PDO $pdo, string $migrations_dir): array
{
    $pending = [];

    if (!is_dir($migrations_dir)) {
        return $pending;
    }

    // Get executed migrations
    try {
        $stmt = $pdo->query("SELECT migration_file FROM migrations_log");
        $executed = [];
        while ($row = $stmt->fetch()) {
            $executed[$row['migration_file']] = true;
        }
    } catch (PDOException $e) {
        error_log('Error fetching executed migrations: ' . $e->getMessage());
        $executed = [];
    }

    // Scan migrations directory
    $files = glob($migrations_dir . '/*.sql');
    if (!is_array($files)) {
        return $pending;
    }

    sort($files); // Execute in alphabetical order

    foreach ($files as $file) {
        $filename = basename($file);
        if (!isset($executed[$filename])) {
            $pending[] = [
                'file' => $file,
                'name' => $filename,
            ];
        }
    }

    return $pending;
}

/**
 * Execute a single migration file
 */
function execute_migration(\PDO $pdo, string $file, string $filename): bool
{
    try {
        $sql = file_get_contents($file);
        if ($sql === false) {
            error_log("Migration: Could not read file $file");
            return false;
        }

        // Strip full-line SQL comments first. Filtering whole chunks that
        // merely *start* with "--" (the previous approach) discards an
        // entire statement whenever a comment header precedes it with no
        // semicolon in between (e.g. a header + a single ALTER TABLE) —
        // the migration then silently logs as "executed" having run nothing.
        $sql = preg_replace('/^\s*--.*$/m', '', $sql) ?? $sql;

        // Split by semicolon and filter empty statements
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            fn($s) => $s !== ''
        );

        foreach ($statements as $statement) {
            if (!empty($statement)) {
                $pdo->exec($statement);
            }
        }

        // Log the migration
        $stmt = $pdo->prepare("INSERT INTO migrations_log (migration_file) VALUES (?)");
        $stmt->execute([$filename]);

        error_log("Migration executed: $filename");
        return true;
    } catch (PDOException $e) {
        error_log("Migration failed ($filename): " . $e->getMessage());
        return false;
    } catch (Exception $e) {
        error_log("Migration error ($filename): " . $e->getMessage());
        return false;
    }
}

/**
 * Run all pending migrations
 */
function run_migrations(string $migrations_dir): array
{
    $pdo = get_migration_db();
    if (!$pdo) {
        return ['success' => false, 'message' => 'Database connection failed'];
    }

    if (!ensure_migrations_log_table($pdo)) {
        return ['success' => false, 'message' => 'Failed to create migrations_log table'];
    }

    $pending = get_pending_migrations($pdo, $migrations_dir);

    if (empty($pending)) {
        return ['success' => true, 'message' => 'No pending migrations', 'count' => 0];
    }

    $executed = 0;
    $failed = 0;

    foreach ($pending as $migration) {
        if (execute_migration($pdo, $migration['file'], $migration['name'])) {
            $executed++;
        } else {
            $failed++;
        }
    }

    return [
        'success'   => $failed === 0,
        'executed'  => $executed,
        'failed'    => $failed,
        'message'   => "Executed $executed migrations" . ($failed > 0 ? ", $failed failed" : ''),
    ];
}

/**
 * Get migration status
 */
function get_migration_status(string $migrations_dir): array
{
    $pdo = get_migration_db();
    if (!$pdo) {
        return ['status' => 'error', 'message' => 'Database connection failed'];
    }

    if (!ensure_migrations_log_table($pdo)) {
        return ['status' => 'error', 'message' => 'Failed to check migrations_log table'];
    }

    $pending = get_pending_migrations($pdo, $migrations_dir);
    $total_migrations = count(glob($migrations_dir . '/*.sql')) ?: 0;
    $executed = $total_migrations - count($pending);

    return [
        'status'                => 'ok',
        'total_migrations'      => $total_migrations,
        'executed_migrations'   => $executed,
        'pending_migrations'    => count($pending),
        'pending_migration_names' => array_column($pending, 'name'),
    ];
}
