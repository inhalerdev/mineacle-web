<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/stats-lib.php';

$site = mineacle_config()['site'] ?? [];
$homeUrl = mineacle_page_home_url($site);
$leaderboardsUrl = mineacle_page_leaderboards_url($site);
$query = trim((string) ($_GET['username'] ?? $_GET['name'] ?? $_GET['player'] ?? $_GET['search'] ?? ''));
$pathInfo = trim((string) ($_SERVER['PATH_INFO'] ?? ''), '/');

if ($query === '' && $pathInfo !== '') {
    $query = rawurldecode($pathInfo);
}

$query = substr(trim($query), 0, 64);
$validUsername = preg_match('/^[A-Za-z0-9_]{1,32}$/', $query) === 1;
$player = null;
$loadError = false;

if ($validUsername) {
    try {
        $player = mineacle_stats_profile_by_username($query);
    } catch (Throwable) {
        $loadError = true;
    }
}

if ($loadError) {
    http_response_code(503);
} elseif (!$validUsername || !$player) {
    http_response_code(404);
}

function mineacle_profile_link(mixed $url): string
{
    $value = trim((string) $url);

    return $value !== '' ? $value : '#';
}

function mineacle_profile_icon(string $name): string
{
    $icons = [
        'calendar' => '<path d="M7 2v3M17 2v3M3.5 8.5h17M5.5 4h13a2 2 0 0 1 2 2v13a2 2 0 0 1-2 2h-13a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/>',
        'world' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.6 3.7 5.6 3.7 9S14.5 18.4 12 21M12 3C9.5 5.6 8.3 8.6 8.3 12s1.2 6.4 3.7 9"/>',
        'money' => '<path d="M12 2v20M17 6.3c-1.1-1-2.7-1.6-4.7-1.6-2.8 0-4.8 1.4-4.8 3.5 0 5 9.7 2.1 9.7 7 0 2.3-2.1 3.9-5.1 3.9-2.2 0-4.2-.7-5.5-2"/>',
        'swords' => '<path d="m4 3 7.2 7.2M3 4l3.5 1L5 8.5M20 3l-7.2 7.2M21 4l-3.5 1 1.5 3.5M8.5 13 4 17.5V21h3.5l4.5-4.5M15.5 13l4.5 4.5V21h-3.5L12 16.5"/>',
        'star' => '<path d="m12 3 2.7 5.5 6.1.9-4.4 4.3 1 6.1-5.4-2.9-5.4 2.9 1-6.1-4.4-4.3 6.1-.9L12 3Z"/>',
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM22 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/>',
        'link' => '<path d="M10 13a5 5 0 0 0 7.1.1l2-2a5 5 0 0 0-7.1-7.1l-1.1 1.1M14 11a5 5 0 0 0-7.1-.1l-2 2A5 5 0 0 0 12 20l1.1-1.1"/>',
        'arrow' => '<path d="M5 12h14M14 7l5 5-5 5"/>',
    ];

    $path = $icons[$name] ?? $icons['star'];

    return '<svg class="mp-icon" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . $path . '</svg>';
}

function mineacle_profile_compact_number(float $value, int $decimals = 2): string
{
    $absolute = abs($value);
    $units = [
        1_000_000_000_000 => 'T',
        1_000_000_000 => 'B',
        1_000_000 => 'M',
        1_000 => 'K',
    ];

    foreach ($units as $threshold => $suffix) {
        if ($absolute >= $threshold) {
            $scaled = $value / $threshold;
            $formatted = number_format($scaled, $decimals, '.', '');
            $formatted = rtrim(rtrim($formatted, '0'), '.');

            return $formatted . $suffix;
        }
    }

    return number_format($value, $decimals > 0 && floor($value) !== $value ? $decimals : 0);
}

function mineacle_profile_compact_money(array $player): string
{
    $cents = mineacle_stats_int($player['balance_cents'] ?? 0);

    return '$' . mineacle_profile_compact_number($cents / 100, 2);
}

function mineacle_profile_compact_playtime(array $player): string
{
    $seconds = max(0, mineacle_stats_int($player['playtime_seconds'] ?? 0));
    $hours = (int) floor($seconds / 3600);

    if ($hours >= 1) {
        return mineacle_profile_compact_number((float) $hours, $hours >= 1000 ? 1 : 0) . 'h';
    }

    return max(1, (int) floor($seconds / 60)) . 'm';
}

function mineacle_profile_kd(array $player): string
{
    $kills = mineacle_stats_int($player['kills'] ?? 0);
    $deaths = mineacle_stats_int($player['deaths'] ?? 0);
    $ratio = mineacle_stats_float($player['kd_ratio'] ?? 0);

    if ($ratio <= 0 && ($kills > 0 || $deaths > 0)) {
        $ratio = $kills / max(1, $deaths);
    }

    return number_format($ratio, 2);
}

function mineacle_profile_relative_time(mixed $value): string
{
    $timestamp = mineacle_stats_int($value);

    if ($timestamp <= 0) {
        return 'Unknown';
    }

    if ($timestamp > 10_000_000_000) {
        $timestamp = (int) floor($timestamp / 1000);
    }

    $difference = max(0, time() - $timestamp);

    if ($difference < 60) {
        return 'just now';
    }

    if ($difference < 3600) {
        return max(1, (int) floor($difference / 60)) . 'm ago';
    }

    if ($difference < 86400) {
        return max(1, (int) floor($difference / 3600)) . 'h ago';
    }

    if ($difference < 604800) {
        return max(1, (int) floor($difference / 86400)) . 'd ago';
    }

    return date('M j, Y', $timestamp);
}

function mineacle_profile_top_percent(mixed $rank, int $totalPlayers): string
{
    $rankValue = mineacle_stats_int($rank);

    if ($rankValue <= 0 || $totalPlayers <= 0) {
        return 'Unranked';
    }

    $percent = max(0.1, min(100, ($rankValue / $totalPlayers) * 100));
    $decimals = $percent < 1 ? 1 : 0;

    return 'Top ' . number_format($percent, $decimals) . '%';
}

function mineacle_profile_human_world(string $world): string
{
    $value = trim($world);

    if ($value === '') {
        return 'Not recorded';
    }

    $value = preg_replace('/^(minecraft:)/i', '', $value) ?? $value;
    $value = preg_replace('/(_the_end|_nether)$/i', ' $1', $value) ?? $value;
    $value = str_replace(['_', '-'], ' ', $value);

    return ucwords(trim($value));
}

function mineacle_profile_optional_data(array $player): array
{
    $defaults = [
        'bio' => '',
        'current_world' => '',
        'favorite_world' => '',
    ];
    $pdo = mineacle_core_db();

    if (!$pdo instanceof PDO) {
        return $defaults;
    }

    $config = mineacle_config();
    $tables = $config['tables'] ?? [];
    $table = (string) ($tables['player_profiles'] ?? 'mineacle_web_profiles');
    $tableSql = mineacle_stats_table_sql($table);

    if ($tableSql === null) {
        return $defaults;
    }

    try {
        $columns = mineacle_stats_columns($pdo, $tableSql);
        $usernameColumn = mineacle_stats_column_from_candidates($columns, ['username']);
        $usernameSql = is_string($usernameColumn) ? mineacle_stats_column_sql($usernameColumn) : null;

        if ($usernameSql === null) {
            return $defaults;
        }

        $map = [
            'bio' => ['bio', 'profile_bio', 'description', 'tagline'],
            'current_world' => ['current_world', 'world_name', 'last_world', 'world'],
            'favorite_world' => ['favorite_world', 'most_played_world', 'preferred_world'],
        ];
        $select = [];

        foreach ($map as $alias => $candidates) {
            $column = mineacle_stats_column_from_candidates($columns, $candidates);
            $columnSql = is_string($column) ? mineacle_stats_column_sql($column) : null;

            if ($columnSql !== null) {
                $select[] = $columnSql . ' AS `' . $alias . '`';
            }
        }

        if ($select === []) {
            return $defaults;
        }

        $statement = $pdo->prepare('SELECT ' . implode(', ', $select) . ' FROM ' . $tableSql . ' WHERE LOWER(' . $usernameSql . ') = LOWER(:username) LIMIT 1');
        $statement->execute([':username' => mineacle_stats_username($player)]);
        $row = $statement->fetch();

        if (!is_array($row)) {
            return $defaults;
        }

        return array_merge($defaults, $row);
    } catch (Throwable) {
        return $defaults;
    }
}

function mineacle_profile_team_summary(array $player): array
{
    $defaults = [
        'name' => mineacle_stats_team_name($player),
        'rank' => 'Unranked',
        'kd' => '0.00',
        'capital' => '$0',
        'members' => 0,
    ];
    $teamId = trim((string) ($player['team_id'] ?? ''));
    $teamName = trim((string) ($player['team_name'] ?? ''));

    if ($teamId === '' && $teamName === '') {
        $defaults['name'] = 'No Team';

        return $defaults;
    }

    try {
        $teams = mineacle_stats_teams(50, 0, 'overall', '');
        $match = null;

        foreach ($teams as $team) {
            $candidateId = trim((string) ($team['team_id'] ?? ''));
            $candidateName = trim((string) ($team['name'] ?? ''));

            if (($teamId !== '' && $candidateId === $teamId) || ($teamName !== '' && strcasecmp($candidateName, $teamName) === 0)) {
                $match = $team;
                break;
            }
        }

        if ($match === null && $teamName !== '') {
            foreach (mineacle_stats_teams(10, 0, 'overall', $teamName) as $team) {
                if (strcasecmp(trim((string) ($team['name'] ?? '')), $teamName) === 0) {
                    $match = $team;
                    break;
                }
            }
        }

        if (!is_array($match)) {
            return $defaults;
        }

        $balanceFormatted = trim((string) ($match['balance_formatted'] ?? ''));
        $balanceCents = mineacle_stats_int($match['balance_cents'] ?? 0);
        $balance = mineacle_stats_float($match['balance'] ?? 0);

        if ($balanceCents !== 0) {
            $capital = '$' . mineacle_profile_compact_number($balanceCents / 100, 2);
        } elseif ($balance !== 0.0) {
            $capital = '$' . mineacle_profile_compact_number($balance, 2);
        } else {
            $capital = $balanceFormatted !== '' ? $balanceFormatted : '$0';
        }

        return [
            'name' => trim((string) ($match['name'] ?? '')) ?: $defaults['name'],
            'rank' => mineacle_stats_rank_label($match['rank'] ?? 0),
            'kd' => number_format(mineacle_stats_float($match['kd_ratio'] ?? 0), 2),
            'capital' => $capital,
            'members' => max(0, mineacle_stats_int($match['members'] ?? 0)),
        ];
    } catch (Throwable) {
        return $defaults;
    }
}

function mineacle_profile_fight_money(mixed $value, bool $isCents): float
{
    $number = is_numeric($value) ? (float) $value : 0.0;

    return $isCents ? $number / 100 : $number;
}

function mineacle_profile_recent_fights(array $player, int $limit = 5): array
{
    $pdo = mineacle_core_db();

    if (!$pdo instanceof PDO) {
        return [];
    }

    $config = mineacle_config();
    $tables = $config['tables'] ?? [];
    $table = trim((string) ($tables['fights'] ?? ''));

    if ($table === '') {
        $environmentTable = getenv('PLAYER_FIGHTS_TABLE');
        $table = is_string($environmentTable) && trim($environmentTable) !== ''
            ? trim($environmentTable)
            : 'mineacle_web_fights';
    }

    if (!preg_match('/^[A-Za-z0-9_]+$/', $table)) {
        return [];
    }

    $tableSql = '`' . $table . '`';

    try {
        $columns = mineacle_stats_columns($pdo, $tableSql);
    } catch (Throwable) {
        return [];
    }

    $uuid = preg_replace('/[^a-f0-9]/', '', strtolower((string) ($player['uuid'] ?? ''))) ?: '';
    $username = mineacle_stats_username($player);
    $createdColumn = mineacle_stats_column_from_candidates($columns, ['created_at', 'occurred_at', 'fight_time', 'timestamp', 'time']);
    $createdSql = is_string($createdColumn) ? mineacle_stats_column_sql($createdColumn) : null;
    $changeColumn = mineacle_stats_column_from_candidates($columns, ['change_cents', 'money_change_cents', 'balance_change_cents', 'change', 'money_change', 'wager']);
    $changeSql = is_string($changeColumn) ? mineacle_stats_column_sql($changeColumn) : null;
    $changeIsCents = is_string($changeColumn) && str_contains(strtolower($changeColumn), 'cents');
    $limit = max(1, min(20, $limit));

    $playerUuidColumn = mineacle_stats_column_from_candidates($columns, ['player_uuid', 'uuid']);
    $playerNameColumn = mineacle_stats_column_from_candidates($columns, ['player_username', 'username', 'player_name']);
    $opponentColumn = mineacle_stats_column_from_candidates($columns, ['opponent_username', 'opponent_name', 'opponent']);
    $resultColumn = mineacle_stats_column_from_candidates($columns, ['result', 'outcome']);
    $identityColumn = $uuid !== '' && is_string($playerUuidColumn) ? $playerUuidColumn : $playerNameColumn;
    $identitySql = is_string($identityColumn) ? mineacle_stats_column_sql($identityColumn) : null;
    $opponentSql = is_string($opponentColumn) ? mineacle_stats_column_sql($opponentColumn) : null;
    $resultSql = is_string($resultColumn) ? mineacle_stats_column_sql($resultColumn) : null;

    if ($identitySql !== null && $opponentSql !== null && $resultSql !== null) {
        $select = [
            $opponentSql . ' AS opponent',
            $resultSql . ' AS result',
            ($changeSql ?? '0') . ' AS change_value',
            ($createdSql ?? '0') . ' AS created_value',
        ];
        $whereExpression = $identitySql;
        $identityValue = $identityColumn === $playerUuidColumn && $uuid !== '' ? $uuid : $username;

        if ($identityColumn === $playerUuidColumn) {
            $whereExpression = "REPLACE(LOWER({$identitySql}), '-', '')";
        }

        $sql = 'SELECT ' . implode(', ', $select) . ' FROM ' . $tableSql . ' WHERE ' . $whereExpression . ' = :identity';

        if ($createdSql !== null) {
            $sql .= ' ORDER BY ' . $createdSql . ' DESC';
        }

        $sql .= ' LIMIT ' . $limit;
        $statement = $pdo->prepare($sql);
        $statement->execute([':identity' => $identityValue]);
        $fights = [];

        foreach ($statement->fetchAll() as $row) {
            if (!is_array($row)) {
                continue;
            }

            $result = strtoupper(trim((string) ($row['result'] ?? '')));
            $won = in_array($result, ['WIN', 'WON', 'VICTORY', '1', 'TRUE'], true);
            $change = mineacle_profile_fight_money($row['change_value'] ?? 0, $changeIsCents);

            $fights[] = [
                'opponent' => trim((string) ($row['opponent'] ?? 'Unknown')) ?: 'Unknown',
                'won' => $won,
                'change' => $won ? abs($change) : -abs($change),
                'date' => mineacle_profile_relative_time($row['created_value'] ?? 0),
            ];
        }

        return $fights;
    }

    $winnerNameColumn = mineacle_stats_column_from_candidates($columns, ['winner_username', 'winner_name', 'winner']);
    $loserNameColumn = mineacle_stats_column_from_candidates($columns, ['loser_username', 'loser_name', 'loser']);
    $winnerUuidColumn = mineacle_stats_column_from_candidates($columns, ['winner_uuid']);
    $loserUuidColumn = mineacle_stats_column_from_candidates($columns, ['loser_uuid']);
    $hasDisplayNames = is_string($winnerNameColumn) && is_string($loserNameColumn);
    $useUuid = !$hasDisplayNames && $uuid !== '' && is_string($winnerUuidColumn) && is_string($loserUuidColumn);
    $winnerColumn = $useUuid ? $winnerUuidColumn : $winnerNameColumn;
    $loserColumn = $useUuid ? $loserUuidColumn : $loserNameColumn;
    $winnerSql = is_string($winnerColumn) ? mineacle_stats_column_sql($winnerColumn) : null;
    $loserSql = is_string($loserColumn) ? mineacle_stats_column_sql($loserColumn) : null;

    if ($winnerSql === null || $loserSql === null) {
        return [];
    }

    $select = [
        $winnerSql . ' AS winner_value',
        $loserSql . ' AS loser_value',
        ($changeSql ?? '0') . ' AS change_value',
        ($createdSql ?? '0') . ' AS created_value',
    ];
    $identity = $useUuid ? $uuid : $username;
    $winnerWhere = $useUuid ? "REPLACE(LOWER({$winnerSql}), '-', '')" : 'LOWER(' . $winnerSql . ')';
    $loserWhere = $useUuid ? "REPLACE(LOWER({$loserSql}), '-', '')" : 'LOWER(' . $loserSql . ')';
    $identityParam = $useUuid ? $identity : strtolower($identity);
    $sql = 'SELECT ' . implode(', ', $select) . ' FROM ' . $tableSql
        . ' WHERE ' . $winnerWhere . ' = :identity OR ' . $loserWhere . ' = :identity';

    if ($createdSql !== null) {
        $sql .= ' ORDER BY ' . $createdSql . ' DESC';
    }

    $sql .= ' LIMIT ' . $limit;
    $statement = $pdo->prepare($sql);
    $statement->execute([':identity' => $identityParam]);
    $fights = [];

    foreach ($statement->fetchAll() as $row) {
        if (!is_array($row)) {
            continue;
        }

        $winner = trim((string) ($row['winner_value'] ?? ''));
        $loser = trim((string) ($row['loser_value'] ?? ''));
        $won = $useUuid
            ? (preg_replace('/[^a-f0-9]/', '', strtolower($winner)) === $uuid)
            : (strcasecmp($winner, $username) === 0);
        $opponent = $won ? $loser : $winner;
        $change = mineacle_profile_fight_money($row['change_value'] ?? 0, $changeIsCents);

        $fights[] = [
            'opponent' => $opponent !== '' ? $opponent : 'Unknown',
            'won' => $won,
            'change' => $won ? abs($change) : -abs($change),
            'date' => mineacle_profile_relative_time($row['created_value'] ?? 0),
        ];
    }

    return $fights;
}

function mineacle_profile_view_model(array $player): array
{
    $optional = mineacle_profile_optional_data($player);
    $team = mineacle_profile_team_summary($player);
    $totalPlayers = 0;

    try {
        $totalPlayers = mineacle_stats_players_count('overall');
    } catch (Throwable) {
        $totalPlayers = 0;
    }

    $username = mineacle_stats_username($player);
    $skinIdentifier = rawurlencode($username);
    $online = mineacle_stats_online($player);
    $moneyRank = mineacle_stats_int($player['money_rank'] ?? 0);
    $killsRank = mineacle_stats_int($player['kills_rank'] ?? 0);
    $playtimeRank = mineacle_stats_int($player['playtime_rank'] ?? 0);
    $bio = trim((string) ($optional['bio'] ?? ''));

    if ($bio === '') {
        $bio = 'Playing, building, and making a name on Mineacle';
    }

    return [
        'username' => $username,
        'display_name' => mineacle_stats_display_name($player),
        'rank_name' => mineacle_stats_rank_name($player),
        'online' => $online,
        'status_label' => $online ? 'Online now' : 'Offline',
        'first_joined' => mineacle_stats_date_label($player['first_joined_at'] ?? 0),
        'last_seen' => $online ? 'just now' : mineacle_profile_relative_time($player['last_seen'] ?? 0),
        'bio' => $bio,
        'crafty_bust' => 'https://render.crafty.gg/3d/bust/' . $skinIdentifier . '?width=260&height=260&x=0&y=0&z=0&shadow=false',
        'mineskin_bust' => 'https://mineskin.eu/armor/bust/' . $skinIdentifier . '/220.png',
        'balance' => mineacle_profile_compact_money($player),
        'balance_rank' => mineacle_stats_rank_label($moneyRank),
        'kills' => number_format(mineacle_stats_int($player['kills'] ?? 0)),
        'kills_rank' => mineacle_stats_rank_label($killsRank),
        'deaths' => number_format(mineacle_stats_int($player['deaths'] ?? 0)),
        'kd' => mineacle_profile_kd($player),
        'playtime' => mineacle_profile_compact_playtime($player),
        'playtime_rank' => mineacle_stats_rank_label($playtimeRank),
        'baltop_position' => mineacle_stats_rank_label($moneyRank),
        'baltop_percent' => mineacle_profile_top_percent($moneyRank, $totalPlayers),
        'world' => mineacle_profile_human_world((string) ($optional['current_world'] ?? '')),
        'favorite_world' => mineacle_profile_human_world((string) (($optional['favorite_world'] ?? '') ?: ($optional['current_world'] ?? ''))),
        'team' => $team,
        'recent_fights' => mineacle_profile_recent_fights($player),
    ];
}

function mineacle_profile_rail(array $site, string $homeUrl, string $leaderboardsUrl): void
{
    $navLinks = [
        ['key' => 'home', 'url' => $homeUrl],
        ['key' => 'vote', 'url' => $site['vote_url'] ?? '#'],
        ['key' => 'stats', 'label' => 'Leaderboards', 'url' => $leaderboardsUrl],
        ['key' => 'bans', 'url' => $site['bans_url'] ?? '#'],
    ];

    echo '<aside class="rail" aria-label="Primary navigation">';
    echo '<a class="rail-logo" href="' . h($homeUrl) . '" aria-label="Home"><img src="/assets/brand/nav-logo-web.png" alt=""></a>';
    echo '<nav class="rail-nav" aria-label="Server links">';

    foreach ($navLinks as $link) {
        $active = (string) $link['key'] === 'stats';
        echo '<a class="rail-link' . ($active ? ' is-active' : '') . '" href="' . h(mineacle_profile_link($link['url'])) . '" aria-label="' . h((string) ($link['label'] ?? $link['key'])) . '"' . ($active ? ' aria-current="page"' : '') . '>';
        echo mineacle_page_icon((string) $link['key']);
        echo '</a>';
    }

    echo '<a class="rail-link rail-store-button" href="' . h(mineacle_profile_link($site['store_url'] ?? '#')) . '" aria-label="Store">' . mineacle_page_icon('store') . '</a>';
    echo '</nav>';
    echo '<div class="rail-social" aria-label="Social links">';
    echo '<a class="rail-link" href="' . h(mineacle_profile_link($site['discord_url'] ?? '#')) . '" aria-label="Discord">' . mineacle_page_icon('discord') . '</a>';
    echo '<a class="rail-link" href="' . h(mineacle_profile_link($site['x_url'] ?? '#')) . '" aria-label="X">' . mineacle_page_icon('x') . '</a>';
    echo '</div>';
    echo '</aside>';
}

$viewModel = $player ? mineacle_profile_view_model($player) : null;
$pageTitle = $viewModel ? (string) $viewModel['display_name'] : 'Player';
$metaOptions = [];

if ($viewModel !== null) {
    $metaOptions = [
        'meta_title' => $viewModel['display_name'] . ' (@' . $viewModel['username'] . ') - Mineacle Player Profile',
        'meta_description' => 'View ' . $viewModel['display_name'] . '\'s Mineacle balance, combat record, team, rankings, playtime, and online status.',
        'canonical_url' => 'https://mineacle.net/player/' . rawurlencode((string) $viewModel['username']),
    ];
} elseif (!$loadError) {
    $metaOptions = [
        'robots' => 'noindex,follow',
        'meta_description' => 'The requested Mineacle player profile could not be found.',
    ];
}

ob_start();
mineacle_page_head($pageTitle, $metaOptions);
$head = (string) ob_get_clean();
$profileAssetVersion = 'profile-v2-001';
$profileStylesheet = '<link rel="stylesheet" href="/assets/player-page.css?v=' . h($profileAssetVersion) . '">';
echo str_replace('</head>', $profileStylesheet . '</head>', $head);
?>
<div class="site-shell mp-site-shell">
    <?php mineacle_profile_rail($site, $homeUrl, $leaderboardsUrl); ?>

    <main class="mp-page" aria-label="Player profile">
        <?php if ($loadError): ?>
            <section class="mp-message-card">
                <span class="mp-message-mark">!</span>
                <h1>Unable to load player stats right now</h1>
                <p>Check the Mineacle Core database connection, then try again</p>
                <a href="<?php echo h($leaderboardsUrl); ?>">Back to leaderboards</a>
            </section>
        <?php elseif ($viewModel === null): ?>
            <section class="mp-message-card">
                <span class="mp-message-mark">?</span>
                <h1>Player not found</h1>
                <p>No stored Mineacle profile was found for <?php echo h($query !== '' ? $query : 'that player'); ?></p>
                <a href="<?php echo h($leaderboardsUrl); ?>">Back to leaderboards</a>
            </section>
        <?php else: ?>
            <section class="mp-hero mp-panel">
                <div class="mp-avatar-card" aria-hidden="true">
                    <img
                        src="<?php echo h((string) $viewModel['crafty_bust']); ?>"
                        data-fallback-src="<?php echo h((string) $viewModel['mineskin_bust']); ?>"
                        alt=""
                        draggable="false"
                        decoding="async"
                    >
                </div>

                <div class="mp-identity">
                    <h1><?php echo h((string) $viewModel['display_name']); ?></h1>
                    <span class="mp-rank-badge"><?php echo h((string) $viewModel['rank_name']); ?></span>
                    <p class="mp-online-status <?php echo $viewModel['online'] ? 'is-online' : 'is-offline'; ?>">
                        <span aria-hidden="true"></span>
                        <?php echo h((string) $viewModel['status_label']); ?>
                    </p>
                    <div class="mp-profile-dates">
                        <span><?php echo mineacle_profile_icon('calendar'); ?> Joined <?php echo h((string) $viewModel['first_joined']); ?></span>
                        <i aria-hidden="true"></i>
                        <span><?php echo mineacle_profile_icon('clock'); ?> Last seen <?php echo h((string) $viewModel['last_seen']); ?></span>
                    </div>
                    <p class="mp-bio"><?php echo h((string) $viewModel['bio']); ?></p>
                </div>

                <article class="mp-team-card">
                    <h2>Team: <?php echo h((string) $viewModel['team']['name']); ?></h2>
                    <dl>
                        <div><dt>Team Rank</dt><dd><?php echo h((string) $viewModel['team']['rank']); ?></dd></div>
                        <div><dt>Team K/D</dt><dd><?php echo h((string) $viewModel['team']['kd']); ?></dd></div>
                        <div><dt>Total Capital</dt><dd><?php echo h((string) $viewModel['team']['capital']); ?></dd></div>
                        <div><dt>Members</dt><dd><?php echo h((string) $viewModel['team']['members']); ?></dd></div>
                    </dl>
                </article>
            </section>

            <section class="mp-stat-strip mp-panel" aria-label="Player highlights">
                <article class="mp-stat-item">
                    <span class="mp-stat-label"><i aria-hidden="true"></i>Balance</span>
                    <strong><?php echo h((string) $viewModel['balance']); ?></strong>
                    <small>Rank <?php echo h((string) $viewModel['balance_rank']); ?></small>
                </article>
                <article class="mp-stat-item">
                    <span class="mp-stat-label">Kills</span>
                    <strong><?php echo h((string) $viewModel['kills']); ?></strong>
                    <small>Rank <?php echo h((string) $viewModel['kills_rank']); ?></small>
                </article>
                <article class="mp-stat-item">
                    <span class="mp-stat-label">Deaths</span>
                    <strong class="is-neutral"><?php echo h((string) $viewModel['deaths']); ?></strong>
                    <small>&nbsp;</small>
                </article>
                <article class="mp-stat-item">
                    <span class="mp-stat-label">K/D Ratio</span>
                    <strong><?php echo h((string) $viewModel['kd']); ?></strong>
                    <small><?php echo (float) $viewModel['kd'] >= 1.0 ? 'Above Average' : 'Building Momentum'; ?></small>
                </article>
                <article class="mp-stat-item">
                    <span class="mp-stat-label">Playtime</span>
                    <strong><?php echo h((string) $viewModel['playtime']); ?></strong>
                    <small>Rank <?php echo h((string) $viewModel['playtime_rank']); ?></small>
                </article>
                <article class="mp-stat-item">
                    <span class="mp-stat-label">Baltop Position</span>
                    <strong><?php echo h((string) $viewModel['baltop_position']); ?></strong>
                    <small><?php echo h((string) $viewModel['baltop_percent']); ?></small>
                </article>
            </section>

            <section class="mp-lower-grid">
                <article class="mp-fights mp-panel" id="recent-fights">
                    <header class="mp-section-heading">
                        <h2>Recent Fights</h2>
                        <span aria-hidden="true"></span>
                    </header>

                    <?php if ($viewModel['recent_fights'] === []): ?>
                        <div class="mp-empty-fights">
                            <?php echo mineacle_profile_icon('swords'); ?>
                            <strong>No recent fights recorded</strong>
                            <span>Fight history will appear here when Mineacle Core exports it</span>
                        </div>
                    <?php else: ?>
                        <div class="mp-fight-table" role="table" aria-label="Recent fights">
                            <div class="mp-fight-row mp-fight-head" role="row">
                                <span role="columnheader">Opponent</span>
                                <span role="columnheader">Result</span>
                                <span role="columnheader">Change</span>
                                <span role="columnheader">Date</span>
                            </div>
                            <?php foreach ($viewModel['recent_fights'] as $fight): ?>
                                <?php
                                $opponent = (string) ($fight['opponent'] ?? 'Unknown');
                                $won = (bool) ($fight['won'] ?? false);
                                $change = (float) ($fight['change'] ?? 0);
                                $headUrl = 'https://mineskin.eu/helm/' . rawurlencode($opponent) . '/52.png';
                                ?>
                                <a class="mp-fight-row" role="row" href="/player/<?php echo rawurlencode($opponent); ?>">
                                    <span class="mp-fight-opponent" role="cell">
                                        <img src="<?php echo h($headUrl); ?>" alt="" draggable="false" loading="lazy" decoding="async">
                                        <b><?php echo h($opponent); ?></b>
                                    </span>
                                    <strong class="<?php echo $won ? 'is-win' : 'is-loss'; ?>" role="cell"><?php echo $won ? 'WIN' : 'LOSS'; ?></strong>
                                    <strong class="<?php echo $change >= 0 ? 'is-positive' : 'is-negative'; ?>" role="cell">
                                        <?php echo $change >= 0 ? '+' : '-'; ?>$<?php echo h(number_format(abs($change), abs($change) < 100 ? 2 : 0)); ?>
                                    </strong>
                                    <time role="cell"><?php echo h((string) ($fight['date'] ?? 'Unknown')); ?></time>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <a class="mp-view-fights" href="#recent-fights">View all fights <?php echo mineacle_profile_icon('arrow'); ?></a>
                </article>

                <article class="mp-snapshot mp-panel">
                    <header class="mp-section-heading">
                        <h2>Server Snapshot</h2>
                        <span aria-hidden="true"></span>
                    </header>
                    <dl class="mp-snapshot-list">
                        <div><dt><?php echo mineacle_profile_icon('world'); ?><span>World</span></dt><dd><?php echo h((string) $viewModel['world']); ?></dd></div>
                        <div><dt><?php echo mineacle_profile_icon('money'); ?><span>Balance Rank</span></dt><dd><?php echo h((string) $viewModel['balance_rank']); ?></dd></div>
                        <div><dt><?php echo mineacle_profile_icon('swords'); ?><span>Kills Rank</span></dt><dd><?php echo h((string) $viewModel['kills_rank']); ?></dd></div>
                        <div><dt><?php echo mineacle_profile_icon('clock'); ?><span>Playtime Rank</span></dt><dd><?php echo h((string) $viewModel['playtime_rank']); ?></dd></div>
                        <div><dt><?php echo mineacle_profile_icon('star'); ?><span>Favorite World</span></dt><dd><?php echo h((string) $viewModel['favorite_world']); ?></dd></div>
                        <div><dt><?php echo mineacle_profile_icon('users'); ?><span>Online Players</span></dt><dd data-profile-online>Checking...</dd></div>
                        <div>
                            <dt><?php echo mineacle_profile_icon('link'); ?><span>Server IP</span></dt>
                            <dd><button type="button" class="mp-copy-ip" data-copy-ip="<?php echo h((string) ($site['minecraft_ip'] ?? 'mineacle.net')); ?>"><?php echo h((string) ($site['minecraft_ip'] ?? 'mineacle.net')); ?></button></dd>
                        </div>
                    </dl>
                </article>
            </section>

            <div class="mp-toast" data-profile-toast role="status" aria-live="polite" hidden>Server IP copied</div>
        <?php endif; ?>
    </main>
</div>
<script src="/assets/player-page.js?v=<?php echo h($profileAssetVersion); ?>"></script>
<?php mineacle_page_end(); ?>
