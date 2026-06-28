<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/cache.php';
require_once __DIR__ . '/../includes/bans-lib.php';

try {
    $search = trim((string) ($_GET['search'] ?? $_GET['q'] ?? ''));
    if (strlen($search) > 64) $search = substr($search, 0, 64);
    $id = max(0, (int) ($_GET['id'] ?? $_GET['ban_id'] ?? 0));
    $config = mineacle_config();

    if ($id > 0) {
        $ttl = (int) ($config['cache']['detail_ttl'] ?? 45);
        $cacheKey = 'ban_detail_v6_' . $id;
        $cached = mineacle_cache_get($cacheKey, $ttl);
        if ($cached !== null) mineacle_json($cached, 200, min(30, $ttl));
        $detail = fetch_litebans_ban_detail($id);
        if ($detail === null) mineacle_json(['success' => false, 'error' => 'Ban record not found'], 404);
        $payload = ['success' => true, 'detail' => $detail]; mineacle_cache_set($cacheKey, $payload); mineacle_json($payload, 200, min(30, $ttl));
    }

    $page = max(1, (int) ($_GET['page'] ?? 1));
    $ttl = (int) ($config['cache']['list_ttl'] ?? 12);
    $cacheKey = 'ban_list_v6_' . $page . '_' . sha1(strtolower($search));
    $cached = mineacle_cache_get($cacheKey, $ttl);
    if ($cached !== null) mineacle_json($cached, 200, min(15, $ttl));
    $payload = fetch_litebans_bans_page($search, $page);
    $response = ['success' => true, 'bans' => $payload['bans'], 'stats' => $payload['stats'] ?? [], 'pagination' => $payload['pagination']];
    mineacle_cache_set($cacheKey, $response);
    mineacle_json($response, 200, min(15, $ttl));
} catch (Throwable $e) {
    error_log('[MineacleBans] ' . $e->getMessage());
    mineacle_json(['success' => false, 'error' => 'Unable to load bans right now'], 500);
}
