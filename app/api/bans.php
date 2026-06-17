<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bans-lib.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $search = trim((string) ($_GET['search'] ?? $_GET['q'] ?? ''));

    if (strlen($search) > 32) {
        $search = substr($search, 0, 32);
    }

    $page = max(1, (int) ($_GET['page'] ?? 1));
    $payload = fetch_litebans_bans_page($search, $page);

    echo json_encode([
        'success' => true,
        'bans' => $payload['bans'],
        'pagination' => $payload['pagination'],
    ], JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    error_log('[MineacleBans] ' . $e->getMessage());

    echo json_encode([
        'success' => false,
        'error' => 'Unable to load bans right now',
    ], JSON_UNESCAPED_SLASHES);
}
