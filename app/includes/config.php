<?php

declare(strict_types=1);

$mineacleEnv = static function (array $keys, string $default = ''): string {
    foreach ($keys as $key) {
        $value = getenv($key);

        if (is_string($value) && $value !== '') {
            return $value;
        }

        $envValue = $_ENV[$key] ?? null;

        if (is_string($envValue) && $envValue !== '') {
            return $envValue;
        }

        $serverValue = $_SERVER[$key] ?? null;

        if (is_string($serverValue) && $serverValue !== '') {
            return $serverValue;
        }
    }

    return $default;
};

return [
    'site' => [
        'name' => $mineacleEnv(['SITE_NAME', 'site_name'], 'Mineacle'),
        'stats_url' => $mineacleEnv(['STATS_URL', 'stats_url'], '/leaderboards'),
        'store_url' => $mineacleEnv(['STORE_URL', 'store_url'], 'https://store.mineacle.net/'),
        'bans_url' => $mineacleEnv(['BANS_URL', 'bans_url'], '#'),
        'vote_url' => $mineacleEnv(['VOTE_URL', 'vote_url'], '#'),
        'discord_url' => $mineacleEnv(['DISCORD_URL', 'discord_url'], '#'),
        'x_url' => $mineacleEnv(['X_URL', 'x_url'], '#'),
        'youtube_url' => $mineacleEnv(['YOUTUBE_URL', 'youtube_url'], '#'),
        'terms_url' => $mineacleEnv(['TERMS_URL', 'terms_url'], '#'),
        'privacy_url' => $mineacleEnv(['PRIVACY_URL', 'privacy_url'], '#'),
        'refund_url' => $mineacleEnv(['REFUND_URL', 'refund_url'], '#'),
        'support_url' => $mineacleEnv(['SUPPORT_URL', 'support_url']),
        'support_email' => $mineacleEnv(['SUPPORT_EMAIL', 'support_email'], 'support@mineacle.net'),
    ],
    'mysql' => [
        'host' => $mineacleEnv(['DB_HOST', 'db_host'], '127.0.0.1'),
        'port' => (int) $mineacleEnv(['DB_PORT', 'db_port'], '3306'),
        'core_database' => $mineacleEnv(['DB_CORE_NAME', 'CORE_DB_NAME', 'db_core_name'], 'mineacle_core'),
        'litebans_database' => $mineacleEnv(['DB_LITEBANS_NAME', 'LITEBANS_DB_NAME', 'db_litebans_name'], 'mineacle_litebans'),
        'username' => $mineacleEnv(['DB_USERNAME', 'DB_USER', 'db_username', 'db_user'], 'website_user'),
        'password' => $mineacleEnv(['DB_PASSWORD', 'db_password']),
        'charset' => $mineacleEnv(['DB_CHARSET', 'db_charset'], 'utf8mb4'),
        'timeout' => (int) $mineacleEnv(['DB_TIMEOUT', 'db_timeout'], '2'),
    ],
    'tables' => [
        'player_profiles' => $mineacleEnv(['PLAYER_PROFILES_TABLE', 'player_profiles_table'], 'mineacle_web_profiles'),
        'teams' => $mineacleEnv(['TEAMS_TABLE', 'PLAYER_TEAMS_TABLE', 'teams_table'], 'mineacle_web_teams'),
        'fights' => $mineacleEnv(['PLAYER_FIGHTS_TABLE', 'player_fights_table'], 'mineacle_web_fights'),
        'litebans_bans' => $mineacleEnv(['LITEBANS_BANS_TABLE', 'litebans_bans_table'], 'litebans_bans'),
        'litebans_mutes' => $mineacleEnv(['LITEBANS_MUTES_TABLE', 'litebans_mutes_table'], 'litebans_mutes'),
    ],
    'skins' => [
        'provider' => strtolower($mineacleEnv(['SKIN_PROVIDER', 'skin_provider'], 'mc-api')),
        'head_size' => (int) $mineacleEnv(['SKIN_HEAD_SIZE', 'skin_head_size'], '96'),
        'chest_size' => (int) $mineacleEnv(['SKIN_CHEST_SIZE', 'skin_chest_size'], '320'),
        'bust_size' => (int) $mineacleEnv(['SKIN_BUST_SIZE', 'skin_bust_size'], '512'),
    ],
    'security' => [
        'debug' => strtolower($mineacleEnv(['APP_DEBUG', 'app_debug'])) === 'true',
    ],
];
