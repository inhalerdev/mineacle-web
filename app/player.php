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
$team = null;
$loadError = false;

if ($validUsername) {
    try {
        $player = mineacle_stats_profile_by_username($query);
        $team = is_array($player) ? mineacle_stats_team_by_profile($player) : null;
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

function mineacle_profile_kd(array $player): string
{
    return mineacle_stats_kd_label(
        mineacle_stats_int($player['kills'] ?? 0),
        mineacle_stats_int($player['deaths'] ?? 0),
        $player['kd_ratio'] ?? 0
    );
}

function mineacle_profile_punishment(array $player): array
{
    $status = is_array($player['punishment_status'] ?? null) ? $player['punishment_status'] : [];
    $ban = is_array($status['ban'] ?? null) ? $status['ban'] : [];
    $mute = is_array($status['mute'] ?? null) ? $status['mute'] : [];

    if (!empty($ban['active'])) {
        return [
            'label' => ($ban['kind'] ?? '') === 'temporary' ? 'Temp Banned' : 'Perm Banned',
            'class' => ($ban['kind'] ?? '') === 'temporary' ? 'is-warning' : 'is-danger',
        ];
    }

    if (!empty($mute['active'])) {
        return [
            'label' => ($mute['kind'] ?? '') === 'temporary' ? 'Temp Muted' : 'Perm Muted',
            'class' => 'is-muted',
        ];
    }

    return [
        'label' => 'Clear',
        'class' => 'is-clear',
    ];
}

function mineacle_profile_team_view_model(array $player, ?array $team): array
{
    $profileTeamName = mineacle_stats_team_name($player);
    $teamName = $team !== null ? trim((string) ($team['name'] ?? '')) : '';

    if ($teamName === '') {
        $teamName = $profileTeamName;
    }

    $hasTeam = $teamName !== '' && strcasecmp($teamName, 'No Team') !== 0;
    $teamKills = $team !== null ? mineacle_stats_int($team['kills'] ?? 0) : 0;
    $teamDeaths = $team !== null ? mineacle_stats_int($team['deaths'] ?? 0) : 0;
    $members = $team !== null ? mineacle_stats_int($team['members'] ?? 0) : 0;
    $onlineMembers = $team !== null ? mineacle_stats_int($team['online_members'] ?? 0) : 0;
    $rank = $team !== null ? mineacle_stats_int($team['rank'] ?? 0) : 0;

    return [
        'has_team' => $hasTeam,
        'name' => $hasTeam ? $teamName : 'No Team',
        'role' => $hasTeam ? mineacle_stats_team_role($player) : 'None',
        'rank' => $rank > 0 ? '#' . number_format($rank) : 'Unranked',
        'kd' => $team !== null ? mineacle_stats_kd_label($teamKills, $teamDeaths, $team['kd_ratio'] ?? 0) : '0.00',
        'capital' => $team !== null ? mineacle_stats_team_money_label($team) : '$0.00',
        'members' => number_format($members),
        'online' => number_format($onlineMembers) . ' / ' . number_format($members),
        'kills' => number_format($teamKills),
        'deaths' => number_format($teamDeaths),
        'joined' => $hasTeam ? mineacle_stats_date_label($player['team_joined_at'] ?? 0) : 'Not joined',
    ];
}

function mineacle_profile_view_model(array $player, ?array $team, array $site): array
{
    $skin = is_array($player['skin'] ?? null) ? $player['skin'] : [];
    $punishment = mineacle_profile_punishment($player);
    $online = mineacle_stats_online($player);
    $username = mineacle_stats_username($player);
    $displayName = mineacle_stats_display_name($player);
    $kills = mineacle_stats_int($player['kills'] ?? 0);
    $deaths = mineacle_stats_int($player['deaths'] ?? 0);
    $teamView = mineacle_profile_team_view_model($player, $team);
    $minecraftIp = trim((string) ($site['minecraft_ip'] ?? 'mineacle.net'));

    if ($minecraftIp === '') {
        $minecraftIp = 'mineacle.net';
    }

    return [
        'username' => $username,
        'display_name' => $displayName,
        'rank_name' => mineacle_stats_rank_name($player),
        'skin_head' => trim((string) ($skin['head'] ?? '')),
        'online' => $online,
        'status_label' => $online ? 'Online now' : 'Offline',
        'last_seen' => mineacle_stats_last_seen_label($player),
        'punishment_label' => (string) $punishment['label'],
        'punishment_class' => (string) $punishment['class'],
        'playtime' => mineacle_stats_playtime_label($player),
        'balance' => mineacle_stats_money_label($player),
        'kills' => number_format($kills),
        'deaths' => number_format($deaths),
        'kd' => mineacle_profile_kd($player),
        'money_rank' => mineacle_stats_rank_label($player['money_rank'] ?? 0),
        'kills_rank' => mineacle_stats_rank_label($player['kills_rank'] ?? 0),
        'playtime_rank' => mineacle_stats_rank_label($player['playtime_rank'] ?? 0),
        'first_joined' => mineacle_stats_date_label($player['first_joined_at'] ?? 0),
        'updated' => mineacle_stats_date_label($player['updated_at'] ?? 0),
        'team' => $teamView,
        'server_ip' => $minecraftIp,
        'summary' => $teamView['has_team']
            ? 'Playing survival on Mineacle. Currently listed with ' . $teamView['name'] . ' as ' . $teamView['role'] . '.'
            : 'Playing survival on Mineacle. This player is not currently listed on a team.',
    ];
}

function mineacle_profile_stat_tile(string $label, string $value, string $hint = '', bool $accent = false): void
{
    echo '<article class="profile-stat-tile' . ($accent ? ' is-accent' : '') . '">';
    echo '<span>' . h($label) . '</span>';
    echo '<strong>' . h($value) . '</strong>';

    if ($hint !== '') {
        echo '<small>' . h($hint) . '</small>';
    }

    echo '</article>';
}

function mineacle_profile_snapshot_row(string $label, string $value): void
{
    echo '<div class="profile-snapshot-row">';
    echo '<span>' . h($label) . '</span>';
    echo '<strong>' . h($value) . '</strong>';
    echo '</div>';
}

$navLinks = [
    ['key' => 'home', 'url' => $homeUrl],
    ['key' => 'vote', 'url' => $site['vote_url'] ?? '#'],
    ['key' => 'stats', 'label' => 'Leaderboards', 'url' => $leaderboardsUrl],
    ['key' => 'bans', 'url' => $site['bans_url'] ?? '#'],
];
$storeLink = ['key' => 'store', 'url' => $site['store_url'] ?? '#'];
$currentNavKey = 'stats';
$viewModel = $player ? mineacle_profile_view_model($player, $team, $site) : null;
$pageTitle = $viewModel ? (string) $viewModel['display_name'] : 'Player';
$metaOptions = [];

if ($viewModel !== null) {
    $metaOptions = [
        'meta_title' => $viewModel['display_name'] . ' (@' . $viewModel['username'] . ') - Mineacle Player Profile',
        'meta_description' => 'View ' . $viewModel['display_name'] . '\'s Mineacle stats, team, balance, combat record, playtime, and status.',
        'canonical_url' => 'https://mineacle.net/player.php?username=' . rawurlencode((string) $viewModel['username']),
    ];
} elseif (!$loadError) {
    $metaOptions = [
        'robots' => 'noindex,follow',
        'meta_description' => 'The requested Mineacle player profile could not be found.',
    ];
}

mineacle_page_head($pageTitle, $metaOptions);
?>
<div class="site-shell">
    <aside class="rail" aria-label="Primary navigation">
        <a class="rail-logo" href="<?php echo h($homeUrl); ?>" aria-label="Home">
            <img src="/assets/brand/nav-logo-web.png" alt="">
        </a>

        <nav class="rail-nav" aria-label="Server links">
            <?php foreach ($navLinks as $link): ?>
                <?php $isActiveNavLink = (string) $link['key'] === $currentNavKey; ?>
                <a class="rail-link<?php echo $isActiveNavLink ? ' is-active' : ''; ?>" href="<?php echo h(mineacle_profile_link($link['url'])); ?>" aria-label="<?php echo h((string) ($link['label'] ?? $link['key'])); ?>"<?php echo $isActiveNavLink ? ' aria-current="page"' : ''; ?>>
                    <?php echo mineacle_page_icon((string) $link['key']); ?>
                </a>
            <?php endforeach; ?>
            <?php $isStoreActive = (string) $storeLink['key'] === $currentNavKey; ?>
            <a class="rail-link rail-store-button<?php echo $isStoreActive ? ' is-active' : ''; ?>" href="<?php echo h(mineacle_profile_link($storeLink['url'])); ?>" aria-label="Store"<?php echo $isStoreActive ? ' aria-current="page"' : ''; ?>>
                <?php echo mineacle_page_icon((string) $storeLink['key']); ?>
            </a>
        </nav>

        <div class="rail-social" aria-label="Social links">
            <a class="rail-link" href="<?php echo h(mineacle_profile_link($site['discord_url'] ?? '#')); ?>" aria-label="Discord">
                <?php echo mineacle_page_icon('discord'); ?>
            </a>
            <a class="rail-link" href="<?php echo h(mineacle_profile_link($site['x_url'] ?? '#')); ?>" aria-label="X">
                <?php echo mineacle_page_icon('x'); ?>
            </a>
        </div>
    </aside>

    <main class="home-grid profile-page" aria-label="Player profile">
        <?php if ($loadError): ?>
            <section class="panel profile-message">
                <h1>Unable to load player stats right now</h1>
                <p>Please check the Mineacle Core database connection, then try again.</p>
            </section>
        <?php elseif ($viewModel === null): ?>
            <section class="panel profile-message">
                <h1>Player not found</h1>
                <p>No stored Mineacle profile was found for <?php echo h($query !== '' ? $query : 'that player'); ?>.</p>
                <a class="profile-message-link" href="<?php echo h($leaderboardsUrl); ?>">Back to leaderboards</a>
            </section>
        <?php else: ?>
            <section class="profile-dashboard" aria-label="<?php echo h((string) $viewModel['display_name']); ?> stats">
                <section class="panel profile-overview-card">
                    <div class="profile-avatar-card">
                        <?php if ($viewModel['skin_head'] !== ''): ?>
                            <img src="<?php echo h((string) $viewModel['skin_head']); ?>" alt="" draggable="false" aria-hidden="true">
                        <?php else: ?>
                            <span><?php echo h(strtoupper(substr((string) $viewModel['display_name'], 0, 1))); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="profile-identity">
                        <h1><?php echo h((string) $viewModel['display_name']); ?></h1>
                        <div class="profile-pills">
                            <span class="profile-pill is-rank"><?php echo h((string) $viewModel['rank_name']); ?></span>
                            <span class="profile-pill <?php echo h((string) $viewModel['punishment_class']); ?>"><?php echo h((string) $viewModel['punishment_label']); ?></span>
                        </div>
                        <span class="profile-online <?php echo $viewModel['online'] ? 'is-online' : 'is-offline'; ?>">
                            <span aria-hidden="true"></span>
                            <?php echo h((string) $viewModel['status_label']); ?>
                        </span>
                        <div class="profile-meta-line">
                            <span>Joined <?php echo h((string) $viewModel['first_joined']); ?></span>
                            <span>Last seen <?php echo h((string) $viewModel['last_seen']); ?></span>
                        </div>
                        <p><?php echo h((string) $viewModel['summary']); ?></p>
                    </div>

                    <aside class="profile-team-card" aria-label="Team snapshot">
                        <h2>Team: <?php echo h((string) $viewModel['team']['name']); ?></h2>
                        <?php
                        mineacle_profile_snapshot_row('Team Rank', (string) $viewModel['team']['rank']);
                        mineacle_profile_snapshot_row('Team K/D', (string) $viewModel['team']['kd']);
                        mineacle_profile_snapshot_row('Total Capital', (string) $viewModel['team']['capital']);
                        mineacle_profile_snapshot_row('Members', (string) $viewModel['team']['members']);
                        ?>
                    </aside>
                </section>

                <section class="panel profile-stat-strip" aria-label="Player stat highlights">
                    <?php
                    mineacle_profile_stat_tile('Balance', (string) $viewModel['balance'], (string) $viewModel['money_rank'], true);
                    mineacle_profile_stat_tile('Kills', (string) $viewModel['kills'], (string) $viewModel['kills_rank']);
                    mineacle_profile_stat_tile('Deaths', (string) $viewModel['deaths']);
                    mineacle_profile_stat_tile('K/D Ratio', (string) $viewModel['kd']);
                    mineacle_profile_stat_tile('Playtime', (string) $viewModel['playtime'], (string) $viewModel['playtime_rank']);
                    mineacle_profile_stat_tile('Baltop Position', (string) $viewModel['money_rank']);
                    ?>
                </section>

                <section class="profile-lower-grid">
                    <article class="panel profile-table-card">
                        <div class="profile-panel-heading">
                            <h2>Combat Summary</h2>
                        </div>
                        <div class="profile-combat-table">
                            <div><span>Metric</span><span>Value</span><span>Rank</span></div>
                            <div><strong>Kills</strong><span><?php echo h((string) $viewModel['kills']); ?></span><span><?php echo h((string) $viewModel['kills_rank']); ?></span></div>
                            <div><strong>Deaths</strong><span><?php echo h((string) $viewModel['deaths']); ?></span><span>-</span></div>
                            <div><strong>K/D Ratio</strong><span><?php echo h((string) $viewModel['kd']); ?></span><span>Qualified at 25 kills</span></div>
                            <div><strong>Account Status</strong><span><?php echo h((string) $viewModel['punishment_label']); ?></span><span><?php echo h((string) $viewModel['updated']); ?></span></div>
                        </div>
                    </article>

                    <article class="panel profile-table-card">
                        <div class="profile-panel-heading">
                            <h2>Server Snapshot</h2>
                        </div>
                        <div class="profile-snapshot-list">
                            <?php
                            mineacle_profile_snapshot_row('Team', (string) $viewModel['team']['name']);
                            mineacle_profile_snapshot_row('Team Role', (string) $viewModel['team']['role']);
                            mineacle_profile_snapshot_row('Team Online', (string) $viewModel['team']['online']);
                            mineacle_profile_snapshot_row('Balance Rank', (string) $viewModel['money_rank']);
                            mineacle_profile_snapshot_row('Kills Rank', (string) $viewModel['kills_rank']);
                            mineacle_profile_snapshot_row('Playtime Rank', (string) $viewModel['playtime_rank']);
                            mineacle_profile_snapshot_row('Server IP', (string) $viewModel['server_ip']);
                            ?>
                        </div>
                    </article>
                </section>
            </section>
        <?php endif; ?>

        <?php mineacle_page_footer($site); ?>
    </main>
</div>
<?php mineacle_page_end(); ?>
