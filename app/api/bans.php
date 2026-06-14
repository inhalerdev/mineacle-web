<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bans-lib.php';

mineacle_security_headers(true);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

try {
    $search = trim((string) ($_GET['search'] ?? ''));
    if (mb_strlen($search) > 32) {
        $search = mb_substr($search, 0, 32);
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

    $payload = [
        'success' => false,
        'error' => 'Unable to load bans right now',
    ];

    $debugRequested = isset($_GET['debug']) && (string) $_GET['debug'] === '1';

    if ($debugRequested) {
        $payload['debug'] = [
            'message' => $e->getMessage(),
            'type' => get_class($e),
            'file' => basename($e->getFile()),
            'line' => $e->getLine(),
        ];
    }

    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
}
