<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/cache.php';
$config = mineacle_config();
$inviteCode = trim((string) ($config['site']['discord_invite_code'] ?? ''));
if ($inviteCode === '') mineacle_json(['success' => false, 'message' => 'Discord invite code is not configured'], 500);
$cacheKey = 'discord_v6_' . $inviteCode; $cached = mineacle_cache_get($cacheKey, 90); if ($cached !== null) mineacle_json($cached, 200, 60);
$url = 'https://discord.com/api/v10/invites/' . rawurlencode($inviteCode) . '?with_counts=true&with_expiration=true';
$context = stream_context_create(['http' => ['method' => 'GET', 'timeout' => 4, 'header' => "Accept: application/json\r\nUser-Agent: MineacleWebsite/6.0"]]);
$response = @file_get_contents($url, false, $context); if ($response === false) mineacle_json(['success' => false, 'message' => 'Discord count unavailable'], 502);
$data = json_decode($response, true); if (!is_array($data)) mineacle_json(['success' => false, 'message' => 'Discord returned invalid data'], 502);
$payload = ['success' => true, 'member_count' => (int) ($data['approximate_member_count'] ?? 0), 'online_count' => (int) ($data['approximate_presence_count'] ?? 0), 'guild_name' => (string) ($data['guild']['name'] ?? 'Mineacle Network'), 'invite_code' => $inviteCode, 'updated_at' => time()];
mineacle_cache_set($cacheKey, $payload); mineacle_json($payload, 200, 60);
