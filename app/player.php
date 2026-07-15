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

function mineacle_profile_stat_row(string $label, string $value): void
{
    echo '<div class="profile-stat-row">';
    echo '<span>' . h($label) . '</span>';
    echo '<strong>' . h($value) . '</strong>';
    echo '</div>';
}

function mineacle_profile_quick_stat(string $label, string $value): void
{
    echo '<article class="profile-quick-stat">';
    echo '<span>' . h($label) . '</span>';
    echo '<strong>' . h($value) . '</strong>';
    echo '</article>';
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

function mineacle_profile_view_model(array $player): array
{
    $skin = is_array($player['skin'] ?? null) ? $player['skin'] : [];
    $punishment = mineacle_profile_punishment($player);
    $online = mineacle_stats_online($player);
    $username = mineacle_stats_username($player);
    $displayName = mineacle_stats_display_name($player);
    $kills = mineacle_stats_int($player['kills'] ?? 0);
    $deaths = mineacle_stats_int($player['deaths'] ?? 0);

    return [
        'username' => $username,
        'display_name' => $displayName,
        'rank_name' => mineacle_stats_rank_name($player),
        'team_name' => mineacle_stats_team_name($player),
        'team_role' => mineacle_stats_team_role($player),
        'skin_chest' => trim((string) ($skin['chest'] ?? '')),
        'online' => $online,
        'status_label' => $online ? 'Online' : 'Offline',
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
        'team_joined' => mineacle_stats_date_label($player['team_joined_at'] ?? 0),
        'updated' => mineacle_stats_date_label($player['updated_at'] ?? 0),
    ];
}

$navLinks = [
    ['key' => 'home', 'url' => $homeUrl],
    ['key' => 'vote', 'url' => $site['vote_url'] ?? '#'],
    ['key' => 'stats', 'label' => 'Leaderboards', 'url' => $leaderboardsUrl],
    ['key' => 'bans', 'url' => $site['bans_url'] ?? '#'],
];
$storeLink = ['key' => 'store', 'url' => $site['store_url'] ?? '#'];
$currentNavKey = 'stats';
$viewModel = $player ? mineacle_profile_view_model($player) : null;
$pageTitle = $viewModel ? (string) $viewModel['display_name'] : 'Player';
$metaOptions = [];

if ($viewModel !== null) {
    $metaOptions = [
        'meta_title' => $viewModel['display_name'] . ' (@' . $viewModel['username'] . ') - Mineacle Player Profile',
        'meta_description' => 'View ' . $viewModel['display_name'] . '\'s Mineacle stats, team, balance, combat record, playtime, and status.',
        'canonical_url' => 'https://mineacle.net/player/' . rawurlencode((string) $viewModel['username']),
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
        <?php mineacle_page_search_header($site); ?>

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
            <section class="panel profile-hero">
                <div class="profile-skin-card">
                    <?php if ($viewModel['skin_chest'] !== ''): ?>
                        <img src="<?php echo h($viewModel['skin_chest']); ?>" alt="" draggable="false" aria-hidden="true">
                    <?php endif; ?>
                </div>

                <div class="profile-identity">
                    <p class="profile-kicker">Player Profile</p>
                    <span class="profile-online <?php echo $viewModel['online'] ? 'is-online' : 'is-offline'; ?>">
                        <span aria-hidden="true"></span>
                        <?php echo h((string) $viewModel['status_label']); ?>
                    </span>
                    <h1><?php echo h((string) $viewModel['display_name']); ?></h1>
                    <p>@<?php echo h((string) $viewModel['username']); ?></p>
                    <div class="profile-pills">
                        <span class="profile-pill"><?php echo h((string) $viewModel['rank_name']); ?></span>
                        <span class="profile-pill <?php echo h((string) $viewModel['punishment_class']); ?>"><?php echo h((string) $viewModel['punishment_label']); ?></span>
                    </div>
                    <div class="profile-quick-stats" aria-label="Player highlights">
                        <?php
                        mineacle_profile_quick_stat('Balance', (string) $viewModel['balance']);
                        mineacle_profile_quick_stat('Kills', (string) $viewModel['kills']);
                        mineacle_profile_quick_stat('K/D', (string) $viewModel['kd']);
                        mineacle_profile_quick_stat('Playtime', (string) $viewModel['playtime']);
                        ?>
                    </div>
                </div>
            </section>

            <section class="profile-content-grid" aria-label="Player stats">
                <article class="panel profile-section">
                    <h2>Player</h2>
                    <div class="profile-stat-list">
                        <?php
                        mineacle_profile_stat_row('Rank', (string) $viewModel['rank_name']);
                        mineacle_profile_stat_row('Status', (string) $viewModel['status_label']);
                        mineacle_profile_stat_row('Last Seen', (string) $viewModel['last_seen']);
                        mineacle_profile_stat_row('First Joined', (string) $viewModel['first_joined']);
                        ?>
                    </div>
                </article>

                <article class="panel profile-section">
                    <h2>Team</h2>
                    <div class="profile-stat-list">
                        <?php
                        mineacle_profile_stat_row('Team', (string) $viewModel['team_name']);
                        mineacle_profile_stat_row('Role', (string) $viewModel['team_role']);
                        mineacle_profile_stat_row('Joined Team', (string) $viewModel['team_joined']);
                        mineacle_profile_stat_row('Updated', (string) $viewModel['updated']);
                        ?>
                    </div>
                </article>

                <article class="panel profile-section profile-section-wide">
                    <h2>Ranking Summary</h2>
                    <div class="profile-stat-list is-grid">
                        <?php
                        mineacle_profile_stat_row('Money Rank', (string) $viewModel['money_rank']);
                        mineacle_profile_stat_row('Kills Rank', (string) $viewModel['kills_rank']);
                        mineacle_profile_stat_row('Playtime Rank', (string) $viewModel['playtime_rank']);
                        mineacle_profile_stat_row('Deaths', (string) $viewModel['deaths']);
                        ?>
                    </div>
                </article>
            </section>
        <?php endif; ?>

        <?php mineacle_page_footer($site); ?>
    </main>
</div>
<?php mineacle_page_end(); ?>
