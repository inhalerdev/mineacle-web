<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bans-lib.php';

mineacle_security_headers(true);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

try {
    $config = mineacle_config();
    $pdo = mineacle_db();

    $prefix = (string) ($config['litebans']['bans_table'] ?? 'litebans_bans');
    $stmt = $pdo->query('SELECT 1 AS ok');
    $basic = $stmt->fetch();

    $tableCheck = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($prefix));
    $tableExists = (bool) $tableCheck->fetchColumn();

    $count = null;
    if ($tableExists) {
        $countStmt = $pdo->query("SELECT COUNT(*) FROM `$prefix`");
        $count = (int) $countStmt->fetchColumn();
    }

    echo json_encode([
        'success' => true,
        'database_connected' => true,
        'litebans_table' => $prefix,
        'litebans_table_exists' => $tableExists,
        'litebans_total_rows' => $count,
    ], JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'database_connected' => false,
        'error' => 'LiteBans database health check failed',
        'debug' => [
            'message' => $e->getMessage(),
            'type' => get_class($e),
            'file' => basename($e->getFile()),
            'line' => $e->getLine(),
        ],
    ], JSON_UNESCAPED_SLASHES);
}
