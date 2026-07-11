<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/stats-lib.php';

$site = mineacle_config()['site'] ?? [];
$homeUrl = mineacle_page_home_url($site);
$leaderboardsUrl = mineacle_page_leaderboards_url($site);
$legacyView = strtolower(trim((string) ($_GET['view'] ?? '')));
$category = strtolower(trim((string) ($_GET['category'] ?? '')));
$category = $category !== '' ? $category : ($legacyView === 'teams' ? 'teams' : 'players');
$category = in_array($category, ['players', 'teams', 'economy', 'combat', 'activity'], true) ? $category : 'players';
$scope = strtolower(trim((string) ($_GET['scope'] ?? '')));
$search = trim((string) ($_GET['search'] ?? ''));
$players = [];
$teams = [];
$topPlayers = [];
$loadError = false;
$tableMode = 'players';

$scopeOptions = [
    'players' => [],
    'teams' => [],
    'economy' => [
        'players' => 'Richest Players',
        'teams' => 'Richest Teams',
    ],
    'combat' => [
        'kills' => 'Top Kills',
        'kd' => 'Player K/D',
        'teams' => 'Team K/D',
    ],
    'activity' => [
        'online' => 'Online Now',
        'teams' => 'Active Teams',
        'playtime' => 'Top Playtime',
        'recent' => 'Recently Seen',
        'veterans' => 'Veterans',
        'newest' => 'Newest',
    ],
];

$defaultScopes = [
    'economy' => 'players',
    'combat' => 'kills',
    'activity' => 'online',
];

if (($scopeOptions[$category] ?? []) !== []) {
    $allowedScopes = array_keys($scopeOptions[$category]);
    $scope = in_array($scope, $allowedScopes, true) ? $scope : ($defaultScopes[$category] ?? $allowedScopes[0]);
} else {
    $scope = '';
}

try {
    $topPlayers = mineacle_stats_players(3, 0, 'overall');
} catch (Throwable) {
    $topPlayers = [];
}

try {
    if ($category === 'teams') {
        $teams = mineacle_stats_teams(100, 0, 'overall', $search);
        $tableMode = 'teams';
    } elseif ($category === 'economy' && $scope === 'teams') {
        $teams = mineacle_stats_teams(100, 0, 'balance', $search);
        $tableMode = 'economy_teams';
    } elseif ($category === 'economy') {
        $players = mineacle_stats_players(100, 0, 'money', $search);
        $tableMode = 'economy_players';
    } elseif ($category === 'combat' && $scope === 'teams') {
        $teams = mineacle_stats_teams(100, 0, 'kd_qualified', $search);
        $tableMode = 'combat_teams';
    } elseif ($category === 'combat' && $scope === 'kd') {
        $players = mineacle_stats_players(100, 0, 'kd_qualified', $search);
        $tableMode = 'combat_players';
    } elseif ($category === 'combat') {
        $players = mineacle_stats_players(100, 0, 'kills', $search);
        $tableMode = 'combat_players';
    } elseif ($category === 'activity' && $scope === 'teams') {
        $teams = mineacle_stats_teams(50, 0, 'activity', $search);
        $tableMode = 'activity_teams';
    } elseif ($category === 'activity') {
        $activitySort = match ($scope) {
            'playtime' => 'playtime',
            'recent' => 'recent',
            'veterans' => 'veterans',
            'newest' => 'newest',
            default => 'online',
        };
        $players = mineacle_stats_players(100, 0, $activitySort, $search);
        $tableMode = 'activity_players';
    } else {
        $players = mineacle_stats_players(100, 0, 'overall', $search);
        $tableMode = 'players';
    }
} catch (Throwable) {
    $loadError = true;
}

function mineacle_players_link(mixed $url): string
{
    $value = trim((string) $url);

    return $value !== '' ? $value : '#';
}

function mineacle_players_profile_url(array $player): string
{
    return '/player/' . rawurlencode(mineacle_stats_username($player));
}

function mineacle_leaderboards_url(string $category, string $scope = '', string $search = ''): string
{
    $params = ['category' => $category];

    if ($scope !== '') {
        $params['scope'] = $scope;
    }

    if ($search !== '') {
        $params['search'] = $search;
    }

    return 'https://mineacle.net/leaderboards.php?' . http_build_query($params);
}

function mineacle_leaderboards_money_from_cents(int $cents): string
{
    return '$' . number_format($cents / 100, 2);
}

function mineacle_leaderboards_team_name(array $team): string
{
    $name = trim((string) ($team['name'] ?? ''));

    return $name !== '' ? $name : 'Unnamed Team';
}

function mineacle_leaderboards_team_money(array $team): string
{
    $formatted = trim((string) ($team['balance_formatted'] ?? ''));

    if ($formatted !== '') {
        return $formatted;
    }

    $cents = mineacle_stats_int($team['balance_cents'] ?? 0);

    if ($cents > 0) {
        return mineacle_leaderboards_money_from_cents($cents);
    }

    $balance = mineacle_stats_float($team['balance'] ?? 0);

    return '$' . number_format($balance, 2);
}

function mineacle_leaderboards_team_online_label(array $team): string
{
    $online = mineacle_stats_int($team['online_members'] ?? 0);
    $members = mineacle_stats_int($team['members'] ?? 0);

    return number_format($online) . ' / ' . number_format($members);
}

function mineacle_leaderboards_team_caption(array $team): string
{
    $onlineMembers = is_array($team['online_member_list'] ?? null) ? $team['online_member_list'] : [];
    $names = [];

    foreach ($onlineMembers as $member) {
        if (!is_array($member)) {
            continue;
        }

        $name = trim((string) ($member['display_name'] ?? ''));

        if ($name === '') {
            $name = trim((string) ($member['username'] ?? ''));
        }

        if ($name !== '') {
            $names[] = $name;
        }
    }

    if ($names !== []) {
        $shown = array_slice($names, 0, 3);
        $remaining = count($names) - count($shown);

        return 'Online: ' . implode(', ', $shown) . ($remaining > 0 ? ' +' . $remaining : '');
    }

    $owner = trim((string) ($team['owner_name'] ?? ''));

    return $owner !== '' ? 'Owner: ' . $owner : 'Team profile';
}

function mineacle_leaderboards_kd(int $kills, int $deaths, mixed $stored = null): string
{
    $ratio = mineacle_stats_float($stored);

    if ($ratio <= 0 && ($kills > 0 || $deaths > 0)) {
        $ratio = $kills / max(1, $deaths);
    }

    return number_format($ratio, 2);
}

function mineacle_leaderboards_category_title(string $category, string $scope): string
{
    if ($category === 'teams') {
        return 'Teams';
    }

    if ($category === 'economy') {
        return $scope === 'teams' ? 'Richest Teams' : 'Richest Players';
    }

    if ($category === 'combat') {
        return match ($scope) {
            'kd' => 'Best Player K/D',
            'teams' => 'Deadliest Teams',
            default => 'Top Killers',
        };
    }

    if ($category === 'activity') {
        return match ($scope) {
            'teams' => 'Most Active Teams',
            'playtime' => 'Top Playtime',
            'recent' => 'Recently Seen',
            'veterans' => 'Server Veterans',
            'newest' => 'Newest Players',
            default => 'Online Now',
        };
    }

    return 'Players';
}

function mineacle_leaderboards_category_description(string $category, string $scope): string
{
    if ($category === 'teams') {
        return 'Overall team standings ranked by capital, K/D, kills, and members.';
    }

    if ($category === 'economy') {
        return $scope === 'teams'
            ? 'Teams controlling the most capital on Mineacle.'
            : 'Players with the strongest economy standings.';
    }

    if ($category === 'combat') {
        return $scope === 'teams'
            ? 'Teams ranked by qualified combat K/D with at least 25 kills.'
            : ($scope === 'kd' ? 'Players ranked by K/D with at least 25 kills.' : 'Players with the most confirmed kills.');
    }

    if ($category === 'activity') {
        return match ($scope) {
            'teams' => 'Teams with the most members online right now.',
            'playtime' => 'Players with the highest total playtime.',
            'recent' => 'Players most recently seen on Mineacle.',
            'veterans' => 'The earliest recorded Mineacle players.',
            'newest' => 'The newest players recorded by Mineacle Core.',
            default => 'Players currently online on Mineacle.',
        };
    }

    return 'Overall Top 100 players ranked by balance, kills, K/D, and playtime.';
}

function mineacle_leaderboards_player_head(array $player): string
{
    $skin = is_array($player['skin'] ?? null) ? $player['skin'] : [];

    return trim((string) ($skin['head'] ?? ''));
}

$navLinks = [
    ['key' => 'home', 'url' => $homeUrl],
    ['key' => 'vote', 'url' => $site['vote_url'] ?? '#'],
    ['key' => 'stats', 'label' => 'Leaderboards', 'url' => $leaderboardsUrl],
    ['key' => 'bans', 'url' => $site['bans_url'] ?? '#'],
];
$storeLink = ['key' => 'store', 'url' => $site['store_url'] ?? '#'];
$currentNavKey = 'stats';
$hasResults = str_contains($tableMode, 'teams') ? $teams !== [] : $players !== [];
$resultCount = str_contains($tableMode, 'teams') ? count($teams) : count($players);
$categoryTitle = mineacle_leaderboards_category_title($category, $scope);
$categoryDescription = mineacle_leaderboards_category_description($category, $scope);
$searchPlaceholder = str_contains($tableMode, 'teams') ? 'Search teams..' : 'Search players..';
$categoryCards = [
    'players' => ['label' => 'Players', 'sub' => 'Overall Top 100', 'icon' => '/assets/icons/leaderboard-top-overall.png'],
    'teams' => ['label' => 'Teams', 'sub' => 'Team standings', 'icon' => '/assets/icons/leaderboard-top-teams.png'],
    'economy' => ['label' => 'Economy', 'sub' => 'Money leaders', 'icon' => '/assets/icons/leaderboard-balance-top.png'],
    'combat' => ['label' => 'Combat', 'sub' => 'PvP rankings', 'icon' => '/assets/icons/leaderboard-top-pvp.png'],
    'activity' => ['label' => 'Activity', 'sub' => 'Online and active', 'icon' => '/assets/icons/leaderboard-activity.png'],
];

mineacle_page_head('Leaderboards');
?>
<div class="site-shell">
    <aside class="rail" aria-label="Primary navigation">
        <a class="rail-logo" href="<?php echo h($homeUrl); ?>" aria-label="Home">
            <img src="/assets/brand/nav-logo-web.png" alt="">
        </a>

        <nav class="rail-nav" aria-label="Server links">
            <?php foreach ($navLinks as $link): ?>
                <?php $isActiveNavLink = (string) $link['key'] === $currentNavKey; ?>
                <a class="rail-link<?php echo $isActiveNavLink ? ' is-active' : ''; ?>" href="<?php echo h(mineacle_players_link($link['url'])); ?>" aria-label="<?php echo h((string) ($link['label'] ?? $link['key'])); ?>"<?php echo $isActiveNavLink ? ' aria-current="page"' : ''; ?>>
                    <?php echo mineacle_page_icon((string) $link['key']); ?>
                </a>
            <?php endforeach; ?>
            <?php $isStoreActive = (string) $storeLink['key'] === $currentNavKey; ?>
            <a class="rail-link rail-store-button<?php echo $isStoreActive ? ' is-active' : ''; ?>" href="<?php echo h(mineacle_players_link($storeLink['url'])); ?>" aria-label="Store"<?php echo $isStoreActive ? ' aria-current="page"' : ''; ?>>
                <?php echo mineacle_page_icon((string) $storeLink['key']); ?>
            </a>
        </nav>

        <div class="rail-social" aria-label="Social links">
            <a class="rail-link" href="<?php echo h(mineacle_players_link($site['discord_url'] ?? '#')); ?>" aria-label="Discord">
                <?php echo mineacle_page_icon('discord'); ?>
            </a>
            <a class="rail-link" href="<?php echo h(mineacle_players_link($site['x_url'] ?? '#')); ?>" aria-label="X">
                <?php echo mineacle_page_icon('x'); ?>
            </a>
        </div>
    </aside>

    <main class="home-grid players-page leaderboard-page" aria-label="Leaderboards">
        <section class="leaderboard-hero-shell" aria-label="Leaderboard overview">
            <div class="panel leaderboard-hero leaderboard-hero-main">
                <div class="leaderboard-copy">
                    <p>Survival Rankings</p>
                    <h1>Leaderboards</h1>
                    <span>The leaderboard is where Mineacle's best prove it. Track top players, strongest teams, richest economies, PvP leaders, and the activity shaping the server.</span>
                </div>
            </div>

            <aside class="panel leaderboard-top-card" aria-label="Global top 3 players">
                <h2>Global Top 3 Players</h2>
                <div class="leaderboard-top-three">
                    <?php foreach ([1, 0, 2] as $slot): ?>
                        <?php $player = $topPlayers[$slot] ?? null; ?>
                        <?php $rank = $slot + 1; ?>
                        <article class="leaderboard-top-player is-rank-<?php echo h((string) $rank); ?>">
                            <span class="leaderboard-top-block" aria-hidden="true"></span>
                            <strong><?php echo $player !== null ? h(mineacle_stats_display_name($player)) : 'Pending'; ?></strong>
                            <small>#<?php echo h((string) $rank); ?></small>
                        </article>
                    <?php endforeach; ?>
                </div>
            </aside>
        </section>

        <section class="panel leaderboard-board leaderboard-hub" aria-label="<?php echo h($categoryTitle); ?>">
            <div class="leaderboard-hub-top">
                <nav class="leaderboard-category-grid" aria-label="Leaderboard categories">
                    <?php foreach ($categoryCards as $key => $card): ?>
                        <?php $isActive = $category === $key; ?>
                        <a class="leaderboard-category-card<?php echo $isActive ? ' is-active' : ''; ?>" href="<?php echo h(mineacle_leaderboards_url($key)); ?>"<?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                            <img src="<?php echo h($card['icon']); ?>" alt="" aria-hidden="true" loading="lazy" decoding="async" draggable="false">
                            <span><?php echo h($card['label']); ?></span>
                            <small><?php echo h($card['sub']); ?></small>
                        </a>
                    <?php endforeach; ?>
                </nav>

                <form class="leaderboard-search" method="get" action="<?php echo h($leaderboardsUrl); ?>">
                    <input type="hidden" name="category" value="<?php echo h($category); ?>">
                    <?php if ($scope !== ''): ?>
                        <input type="hidden" name="scope" value="<?php echo h($scope); ?>">
                    <?php endif; ?>
                    <label for="leaderboardSearch">Search the players writing Mineacle history</label>
                    <div>
                        <input id="leaderboardSearch" name="search" type="search" placeholder="<?php echo h($searchPlaceholder); ?>" value="<?php echo h($search); ?>" autocomplete="off">
                        <button type="submit">Search</button>
                    </div>
                </form>
            </div>

            <?php if (($scopeOptions[$category] ?? []) !== []): ?>
                <nav class="leaderboard-subfilters" aria-label="<?php echo h($categoryTitle); ?> filters">
                    <?php foreach ($scopeOptions[$category] as $scopeKey => $scopeLabel): ?>
                        <?php $isActiveScope = $scope === $scopeKey; ?>
                        <a class="<?php echo $isActiveScope ? 'is-active' : ''; ?>" href="<?php echo h(mineacle_leaderboards_url($category, (string) $scopeKey, $search)); ?>"<?php echo $isActiveScope ? ' aria-current="page"' : ''; ?>>
                            <?php echo h((string) $scopeLabel); ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
            <?php endif; ?>

            <div class="leaderboard-board-header">
                <div>
                    <h2><?php echo h($categoryTitle); ?></h2>
                    <p><?php echo h($categoryDescription); ?></p>
                </div>
                <span><?php echo h(number_format($resultCount)); ?> results</span>
            </div>

            <?php if ($loadError): ?>
                <section class="profile-message">
                    <h1>Unable to load leaderboards right now</h1>
                    <p>Check the Mineacle Core database connection, then try again.</p>
                </section>
            <?php elseif (!$hasResults): ?>
                <section class="profile-message">
                    <h1>No leaderboard data found yet</h1>
                    <p><?php echo str_contains($tableMode, 'teams') ? 'Teams will appear here once Mineacle Core writes team standings.' : 'Players will appear here once Mineacle Core writes profile stats.'; ?></p>
                </section>
            <?php elseif (str_contains($tableMode, 'teams')): ?>
                <div class="leaderboard-table-head leaderboard-table-head-teams" aria-hidden="true">
                    <span>#</span>
                    <span>Team</span>
                    <span>Members</span>
                    <span>Online</span>
                    <span>Capital</span>
                    <span>Kills</span>
                    <span>K/D</span>
                </div>

                <div class="players-list">
                    <?php foreach ($teams as $team): ?>
                        <?php
                        $rank = mineacle_stats_int($team['rank'] ?? 0);
                        $kills = mineacle_stats_int($team['kills'] ?? 0);
                        $deaths = mineacle_stats_int($team['deaths'] ?? 0);
                        ?>
                        <article class="player-card leaderboard-table-row leaderboard-team-row">
                            <span class="leaderboard-team-rank">#<?php echo h((string) $rank); ?></span>
                            <span class="player-card-main">
                                <strong><?php echo h(mineacle_leaderboards_team_name($team)); ?></strong>
                                <span><?php echo h(mineacle_leaderboards_team_caption($team)); ?></span>
                            </span>
                            <span class="player-card-stat"><?php echo h(number_format(mineacle_stats_int($team['members'] ?? 0))); ?></span>
                            <span class="player-card-status <?php echo mineacle_stats_int($team['online_members'] ?? 0) > 0 ? 'is-online' : 'is-offline'; ?>">
                                <span aria-hidden="true"></span>
                                <?php echo h(mineacle_leaderboards_team_online_label($team)); ?>
                            </span>
                            <span class="player-card-stat"><?php echo h(mineacle_leaderboards_team_money($team)); ?></span>
                            <span class="player-card-stat"><?php echo h(number_format($kills)); ?></span>
                            <span class="player-card-stat"><?php echo h(mineacle_leaderboards_kd($kills, $deaths, $team['kd_ratio'] ?? 0)); ?></span>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="leaderboard-table-head leaderboard-table-head-players" aria-hidden="true">
                    <span>#</span>
                    <span>Player</span>
                    <span>Team</span>
                    <span>Balance</span>
                    <span>Kills</span>
                    <span>Deaths</span>
                    <span>K/D</span>
                    <span>Playtime</span>
                    <span>Status</span>
                </div>

                <div class="players-list">
                    <?php foreach ($players as $index => $player): ?>
                        <?php
                        $head = mineacle_leaderboards_player_head($player);
                        $online = mineacle_stats_online($player);
                        ?>
                        <a class="player-card leaderboard-table-row leaderboard-player-row" href="<?php echo h(mineacle_players_profile_url($player)); ?>">
                            <span class="leaderboard-team-rank">#<?php echo h((string) ($index + 1)); ?></span>
                            <span class="player-card-main leaderboard-player-main">
                                <span class="player-card-head">
                                    <?php if ($head !== ''): ?>
                                        <img src="<?php echo h($head); ?>" alt="" aria-hidden="true" loading="lazy" decoding="async" draggable="false">
                                    <?php endif; ?>
                                </span>
                                <span>
                                    <strong><?php echo h(mineacle_stats_display_name($player)); ?></strong>
                                    <span>@<?php echo h(mineacle_stats_username($player)); ?></span>
                                </span>
                            </span>
                            <span class="player-card-stat"><?php echo h(mineacle_stats_team_name($player)); ?></span>
                            <span class="player-card-stat"><?php echo h(mineacle_stats_money_label($player)); ?></span>
                            <span class="player-card-stat"><?php echo h(number_format(mineacle_stats_int($player['kills'] ?? 0))); ?></span>
                            <span class="player-card-stat"><?php echo h(number_format(mineacle_stats_int($player['deaths'] ?? 0))); ?></span>
                            <span class="player-card-stat"><?php echo h(mineacle_leaderboards_kd(mineacle_stats_int($player['kills'] ?? 0), mineacle_stats_int($player['deaths'] ?? 0), $player['kd_ratio'] ?? 0)); ?></span>
                            <span class="player-card-stat"><?php echo h(mineacle_stats_playtime_label($player)); ?></span>
                            <span class="player-card-status <?php echo $online ? 'is-online' : 'is-offline'; ?>">
                                <span aria-hidden="true"></span>
                                <?php echo h(mineacle_stats_last_seen_label($player)); ?>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <?php mineacle_page_footer($site); ?>
    </main>
</div>
<?php mineacle_page_end(); ?>
