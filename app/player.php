<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/home-data.php';
require_once __DIR__ . '/includes/stats-lib.php';

$site = mineacle_config()['site'] ?? [];
$homeUrl = mineacle_page_home_url($site);
$leaderboardsUrl = mineacle_page_leaderboards_url($site);
$minecraftIp = trim((string) ($site['minecraft_ip'] ?? 'mineacle.net'));

if ($minecraftIp === '') {
    $minecraftIp = 'mineacle.net';
}

$directPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
$directUsername = trim((string) ($_GET['username'] ?? ''));

if ($directPath === '/player.php' && preg_match('/^[A-Za-z0-9_]{1,32}$/', $directUsername) === 1) {
    header('Location: https://mineacle.net/player/' . rawurlencode($directUsername), true, 301);
    exit;
}

$home = mineacle_home_data();

function mineacle_profile_requested_username(): string
{
    $query = trim((string) ($_GET['username'] ?? $_GET['name'] ?? $_GET['player'] ?? $_GET['search'] ?? ''));
    $pathInfo = trim((string) ($_SERVER['PATH_INFO'] ?? ''), '/');
    $requestPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);

    if ($query === '' && $pathInfo !== '') {
        $query = rawurldecode($pathInfo);
    }

    if ($query === '' && is_string($requestPath) && preg_match('#^/player/([^/]+)/?$#', $requestPath, $match) === 1) {
        $query = rawurldecode($match[1]);
    }

    return substr(trim($query), 0, 64);
}

function mineacle_profile_link(mixed $url): string
{
    $value = trim((string) $url);

    return $value !== '' ? $value : '#';
}

function mineacle_profile_is_video_url(string $url): bool
{
    $path = parse_url($url, PHP_URL_PATH);

    return is_string($path) && preg_match('/\.(m4v|mp4|mov|webm)$/i', $path) === 1;
}

function mineacle_profile_versioned_url(string $url, string $version): string
{
    if ($url === '' || $url === '#') {
        return $url;
    }

    $separator = strpos($url, '?') === false ? '?' : '&';

    return $url . $separator . 'v=' . rawurlencode($version);
}

function mineacle_profile_render_hero(array $home, string $minecraftIp, string $assetVersion): void
{
    $heroBackground = trim((string) ($home['hero']['background_image_url'] ?? ''));
    $heroBackgroundUrl = mineacle_home_safe_url($heroBackground);
    $heroBackgroundIsVideo = mineacle_profile_is_video_url($heroBackgroundUrl);

    echo '<section class="top-row">';
    echo '<article class="panel hero-panel"' . ($heroBackgroundIsVideo ? '' : mineacle_home_image_style($home['hero']['background_image_url'] ?? '')) . ' aria-label="Hero">';

    if ($heroBackground !== '') {
        if ($heroBackgroundIsVideo) {
            echo '<video class="hero-background hero-background-video" data-hero-video autoplay muted loop playsinline preload="none" controlslist="nodownload noplaybackrate" disablepictureinpicture draggable="false" aria-hidden="true">';
            echo '<source data-src="' . h(mineacle_profile_versioned_url($heroBackgroundUrl, $assetVersion)) . '" type="video/mp4">';
            echo '</video>';
        } else {
            echo '<img class="hero-background" src="' . h($heroBackgroundUrl) . '" alt="" draggable="false" aria-hidden="true">';
        }
    }

    echo '<div class="hero-copy">';
    echo '<h1 class="hero-logo-title"><img src="' . h(mineacle_profile_versioned_url('/assets/brand/hero-logo-web.png', $assetVersion)) . '" alt="Mineacle"></h1>';
    echo '<div class="hero-actions" aria-label="Server actions">';
    echo '<button class="hero-action hero-action-primary hero-copy-ip" type="button" data-copy-server-ip data-server-ip="' . h($minecraftIp) . '" data-default-label="Play Now" data-copied-label="IP Copied" data-failed-label="Copy Failed" aria-label="Copy Mineacle server IP">';
    echo '<span class="hero-action-icon-stack" aria-hidden="true">';
    echo '<img class="hero-action-icon hero-action-icon-default" src="' . h(mineacle_profile_versioned_url('/assets/icons/copy-ip-play.svg', $assetVersion)) . '" alt="">';
    echo '<img class="hero-action-icon hero-action-icon-copied" src="' . h(mineacle_profile_versioned_url('/assets/icons/copy-ip-tick.svg', $assetVersion)) . '" alt="">';
    echo '</span>';
    echo '<span data-copy-server-label>Play Now</span>';
    echo '</button>';
    echo '<button class="hero-action hero-action-status is-loading" type="button" data-open-join-modal data-server-status data-status-format="hero-join" data-server-ip="' . h($minecraftIp) . '" aria-live="polite">';
    echo '<span data-server-status-count>Join Players Online</span>';
    echo '</button>';
    echo '</div>';
    echo '</div>';
    echo '<span class="sr-only">Hero banner</span>';
    echo '</article>';
    echo '</section>';
}

function mineacle_profile_render_join_modal(string $minecraftIp, string $assetVersion): void
{
    echo '<div class="join-modal" data-join-modal hidden>';
    echo '<div class="join-modal-backdrop" data-close-join-modal></div>';
    echo '<section class="join-modal-panel" role="dialog" aria-modal="true" aria-labelledby="joinModalTitle" tabindex="-1">';
    echo '<button class="join-modal-close" type="button" data-close-join-modal aria-label="Close how to join"></button>';
    echo '<div class="join-modal-copy"><p>Java Edition 1.21.11 to 26+</p><h2 id="joinModalTitle">Join Mineacle</h2></div>';
    echo '<div class="join-modal-media"><img data-join-gif data-src="' . h(mineacle_profile_versioned_url('/assets/brand/mineacle-how-to-join.gif', $assetVersion)) . '" alt="How to join Mineacle on Java Edition"></div>';
    echo '<div class="join-modal-actions">';
    echo '<p class="join-modal-ip"><span>Server IP:</span> <strong>' . h($minecraftIp) . '</strong></p>';
    echo '<button class="hero-action hero-action-primary hero-copy-ip join-modal-copy-ip" type="button" data-copy-server-ip data-server-ip="' . h($minecraftIp) . '" data-default-label="Copy IP" data-copied-label="IP Copied" data-failed-label="Copy Failed" aria-label="Copy Mineacle server IP">';
    echo '<span class="hero-action-icon-stack" aria-hidden="true">';
    echo '<img class="hero-action-icon hero-action-icon-default" src="' . h(mineacle_profile_versioned_url('/assets/icons/copy-ip-play.svg', $assetVersion)) . '" alt="">';
    echo '<img class="hero-action-icon hero-action-icon-copied" src="' . h(mineacle_profile_versioned_url('/assets/icons/copy-ip-tick.svg', $assetVersion)) . '" alt="">';
    echo '</span>';
    echo '<span data-copy-server-label>Copy IP</span>';
    echo '</button>';
    echo '</div>';
    echo '</section>';
    echo '</div>';
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

    return [
        'has_team' => $hasTeam,
        'name' => $hasTeam ? $teamName : 'No Team',
        'role' => $hasTeam ? mineacle_stats_team_role($player) : 'None',
    ];
}

function mineacle_profile_view_model(array $player, ?array $team): array
{
    $skin = is_array($player['skin'] ?? null) ? $player['skin'] : [];
    $punishment = mineacle_profile_punishment($player);
    $online = mineacle_stats_online($player);
    $teamView = mineacle_profile_team_view_model($player, $team);
    $rankName = mineacle_stats_rank_name($player);
    $displayName = mineacle_stats_display_name($player);

    return [
        'username' => mineacle_stats_username($player),
        'display_name' => $displayName,
        'headline' => $rankName . ' ' . $displayName,
        'rank_name' => $rankName,
        'skin_head' => trim((string) ($skin['head'] ?? '')),
        'skin_bust' => trim((string) (($skin['bust'] ?? '') ?: ($skin['chest'] ?? ''))),
        'online' => $online,
        'status_label' => $online ? 'Online' : 'Offline',
        'location_label' => $online ? 'Located in Survival' : 'Last seen ' . mineacle_stats_last_seen_label($player),
        'last_seen' => mineacle_stats_last_seen_label($player),
        'punishment_label' => (string) $punishment['label'],
        'punishment_class' => (string) $punishment['class'],
        'balance' => mineacle_stats_money_label($player),
        'kills' => number_format(mineacle_stats_int($player['kills'] ?? 0)),
        'deaths' => number_format(mineacle_stats_int($player['deaths'] ?? 0)),
        'kd' => mineacle_profile_kd($player),
        'playtime' => mineacle_stats_playtime_label($player),
        'money_rank' => mineacle_stats_rank_label($player['money_rank'] ?? 0),
        'kills_rank' => mineacle_stats_rank_label($player['kills_rank'] ?? 0),
        'playtime_rank' => mineacle_stats_rank_label($player['playtime_rank'] ?? 0),
        'first_joined' => mineacle_stats_date_label($player['first_joined_at'] ?? 0),
        'updated' => mineacle_stats_date_label($player['updated_at'] ?? 0),
        'team' => $teamView,
    ];
}

function mineacle_profile_stat_item(string $label, string $value, string $icon, string $alt = ''): void
{
    echo '<article class="profile-summary-item">';
    echo '<span>' . h($label) . '</span>';
    echo '<strong><img src="' . h($icon) . '" alt="' . h($alt) . '" draggable="false"> ' . h($value) . '</strong>';
    echo '</article>';
}

$query = mineacle_profile_requested_username();
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

$navLinks = [
    ['key' => 'home', 'url' => $homeUrl],
    ['key' => 'vote', 'url' => $site['vote_url'] ?? '#'],
    ['key' => 'stats', 'label' => 'Leaderboards', 'url' => $leaderboardsUrl],
    ['key' => 'bans', 'url' => $site['bans_url'] ?? '#'],
];
$storeLink = ['key' => 'store', 'url' => $site['store_url'] ?? '#'];
$currentNavKey = 'stats';
$assetVersion = mineacle_page_asset_version();
$viewModel = $player ? mineacle_profile_view_model($player, $team) : null;
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
        <?php mineacle_profile_render_hero($home, $minecraftIp, $assetVersion); ?>

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
            <section class="panel profile-main-panel" aria-label="<?php echo h((string) $viewModel['display_name']); ?> stats">
                <div class="profile-bust-stage">
                    <?php if ($viewModel['skin_bust'] !== ''): ?>
                        <img src="<?php echo h((string) $viewModel['skin_bust']); ?>" alt="" draggable="false" aria-hidden="true">
                    <?php else: ?>
                        <span><?php echo h(strtoupper(substr((string) $viewModel['display_name'], 0, 1))); ?></span>
                    <?php endif; ?>
                </div>

                <div class="profile-player-lockup">
                    <h1><?php echo h((string) $viewModel['headline']); ?></h1>
                    <div class="profile-state-lines">
                        <p>
                            <strong class="<?php echo $viewModel['online'] ? 'is-online' : 'is-offline'; ?>"><?php echo h((string) $viewModel['status_label']); ?></strong>
                            <span><?php echo h((string) $viewModel['location_label']); ?></span>
                        </p>
                        <p>
                            <strong class="<?php echo h((string) $viewModel['punishment_class']); ?>"><?php echo h((string) $viewModel['punishment_label']); ?></strong>
                            <span>Updated <?php echo h((string) $viewModel['updated']); ?></span>
                        </p>
                    </div>
                </div>

                <div class="profile-summary-bar" aria-label="Player stat summary">
                    <?php
                    mineacle_profile_stat_item('Player Balance', (string) $viewModel['balance'], '/assets/icons/player-money.png?v=' . rawurlencode($assetVersion), 'Balance');
                    mineacle_profile_stat_item('Player Kills', (string) $viewModel['kills'], '/assets/icons/player-sword.png?v=' . rawurlencode($assetVersion), 'Kills');
                    mineacle_profile_stat_item('Player Deaths', (string) $viewModel['deaths'], '/assets/icons/player-heart.png?v=' . rawurlencode($assetVersion), 'Deaths');
                    mineacle_profile_stat_item('Player Playtime', (string) $viewModel['playtime'], '/assets/icons/player-clock.png?v=' . rawurlencode($assetVersion), 'Playtime');
                    mineacle_profile_stat_item('Player Team', (string) $viewModel['team']['name'], '/assets/icons/player-castle.png?v=' . rawurlencode($assetVersion), 'Team');
                    mineacle_profile_stat_item('Team Role', (string) $viewModel['team']['role'], '/assets/icons/player-person.png?v=' . rawurlencode($assetVersion), 'Role');
                    ?>
                </div>
            </section>

            <section class="panel profile-recent-panel" aria-label="Recent fights">
                <span class="sr-only">Recent fights will appear here once fight events are connected.</span>
            </section>
        <?php endif; ?>

        <?php mineacle_page_footer($site); ?>
    </main>
</div>
<?php mineacle_profile_render_join_modal($minecraftIp, $assetVersion); ?>
<?php mineacle_page_end(); ?>
