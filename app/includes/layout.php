<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

function mineacle_page_asset_version(): string
{
    return 'leaderboards-player-20260723';
}

function mineacle_page_clean_text(string $value): string
{
    return trim((string) preg_replace('/\s+/', ' ', $value));
}

function mineacle_page_meta_title(string $title, string $siteName): string
{
    $cleanTitle = mineacle_page_clean_text($title) ?: 'Leaderboards';
    $normalizedTitle = strtolower($cleanTitle);

    if (in_array($normalizedTitle, ['leaderboard', 'leaderboards'], true)) {
        $cleanTitle = 'Leaderboards';
    }

    return $cleanTitle . ' | ' . $siteName;
}

function mineacle_page_meta_description(string $title, string $siteName): string
{
    $cleanTitle = mineacle_page_clean_text($title) ?: 'Leaderboards';

    if (in_array(strtolower($cleanTitle), ['leaderboard', 'leaderboards'], true)) {
        return 'View ' . $siteName . ' player leaderboards, rankings, and server stats.';
    }

    if (strcasecmp($cleanTitle, 'Player') === 0) {
        return 'View a ' . $siteName . ' player profile and server stats.';
    }

    return 'View ' . $cleanTitle . '\'s ' . $siteName . ' player profile and server stats.';
}

function mineacle_page_canonical_url(): string
{
    $path = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);

    if (!is_string($path) || $path === '') {
        $path = '/';
    }

    if ($path === '/' || $path === '/index.php') {
        $path = '/leaderboards';
    }

    return 'https://mineacle.net' . $path;
}

function mineacle_page_public_link(mixed $url): string
{
    $value = trim((string) $url);

    if ($value === '') {
        return '#';
    }

    if (str_starts_with($value, 'mailto:')) {
        $email = substr($value, 7);

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $value : '#';
    }

    if ($value === '#' || str_starts_with($value, '/') || str_starts_with($value, './')) {
        return $value;
    }

    return filter_var($value, FILTER_VALIDATE_URL) ? $value : '#';
}

function mineacle_page_is_local_host(string $url): bool
{
    $host = strtolower((string) parse_url($url, PHP_URL_HOST));

    if ($host === '') {
        return false;
    }

    if (in_array($host, ['localhost', '127.0.0.1', '0.0.0.0', '::1'], true)) {
        return true;
    }

    return preg_match('/^(10\.|192\.168\.|172\.(1[6-9]|2[0-9]|3[0-1])\.)/', $host) === 1;
}

function mineacle_page_leaderboards_url(array $site = []): string
{
    $value = trim((string) ($site['stats_url'] ?? ''));
    $normalized = strtolower(trim($value, " \t\n\r\0\x0B/"));

    if ($value === '#') {
        return '#';
    }

    if ($value === '' || in_array($normalized, ['leaderboards', 'leaderboards.php', 'players', 'players.php'], true)) {
        return '/leaderboards';
    }

    $safe = mineacle_page_public_link($value);

    if ($safe === '#') {
        return '/leaderboards';
    }

    if (mineacle_page_is_local_host($safe)) {
        return '/leaderboards';
    }

    if ($safe === '/leaderboards' || $safe === '/players' || $safe === '/players.php') {
        return '/leaderboards';
    }

    if ($safe === '/leaderboards.php') {
        return '/leaderboards';
    }

    $path = parse_url($safe, PHP_URL_PATH);

    if (is_string($path) && in_array($path, ['/leaderboards', '/leaderboards.php', '/players', '/players.php'], true)) {
        return '/leaderboards';
    }

    return $safe;
}

function mineacle_page_icon(string $name): string
{
    $assetVersion = rawurlencode(mineacle_page_asset_version());
    $iconVersion = '?v=' . $assetVersion;

    if ($name === 'discord') {
        $square = '/assets/icons/discord-square.svg' . $iconVersion;
        $mark = '/assets/icons/discord-mark.svg' . $iconVersion;

        return '<span class="site-icon site-icon-layered discord-icon" aria-hidden="true">'
            . '<img class="discord-icon-square" src="' . h($square) . '" alt="" draggable="false">'
            . '<img class="discord-icon-mark" src="' . h($mark) . '" alt="" draggable="false">'
            . '</span>';
    }

    if ($name === 'store') {
        $mark = '/assets/icons/store.svg' . $iconVersion;

        return '<span class="site-icon site-icon-layered store-icon" aria-hidden="true">'
            . '<span class="store-icon-square"></span>'
            . '<img class="store-icon-mark" src="' . h($mark) . '" alt="" draggable="false">'
            . '</span>';
    }

    if ($name === 'x') {
        $square = '/assets/icons/x-square.svg' . $iconVersion;
        $mark = '/assets/icons/x-mark.svg' . $iconVersion;

        return '<span class="site-icon site-icon-layered x-icon" aria-hidden="true">'
            . '<img class="x-icon-square" src="' . h($square) . '" alt="" draggable="false">'
            . '<img class="x-icon-mark" src="' . h($mark) . '" alt="" draggable="false">'
            . '</span>';
    }

    $officialIcons = [
        'stats' => '/assets/icons/leaderboard.svg' . $iconVersion,
        'vote' => '/assets/icons/vote.svg' . $iconVersion,
        'bans' => '/assets/icons/bans.svg' . $iconVersion,
        'youtube' => '/assets/icons/youtube-pixel.svg' . $iconVersion,
    ];

    if (isset($officialIcons[$name])) {
        return '<img class="site-icon" src="' . h($officialIcons[$name]) . '" alt="" aria-hidden="true" draggable="false">';
    }

    return '';
}

function mineacle_page_footer(array $site): void
{
    $year = date('Y');
    $assetVersion = mineacle_page_asset_version();
    $footerLogoUrl = '/assets/brand/mncl-studios-web.png?v=' . rawurlencode($assetVersion);
    $supportEmail = (string) ($site['support_email'] ?? 'support@mineacle.net');
    $supportLink = trim((string) ($site['support_url'] ?? ''));

    if ($supportLink === '') {
        $supportLink = filter_var($supportEmail, FILTER_VALIDATE_EMAIL) ? 'mailto:' . $supportEmail : '#';
    }

    $quickLinks = [
        ['label' => 'Leaderboards', 'url' => mineacle_page_leaderboards_url($site)],
        ['label' => 'Store', 'url' => (string) ($site['store_url'] ?? '#')],
        ['label' => 'Vote', 'url' => (string) ($site['vote_url'] ?? '#')],
    ];
    $socialLinks = [
        ['key' => 'discord', 'label' => 'Discord', 'url' => (string) ($site['discord_url'] ?? '#')],
        ['key' => 'x', 'label' => 'X/Twitter', 'url' => (string) ($site['x_url'] ?? '#')],
        ['key' => 'youtube', 'label' => 'YouTube', 'url' => (string) ($site['youtube_url'] ?? '#')],
    ];
    $legalLinks = [
        ['label' => 'Terms of Service', 'url' => (string) ($site['terms_url'] ?? '#')],
        ['label' => 'Privacy Policy', 'url' => (string) ($site['privacy_url'] ?? '#')],
        ['label' => 'Refund Policy', 'url' => (string) ($site['refund_url'] ?? '#')],
        ['label' => 'Support', 'url' => $supportLink],
    ];

    echo '<footer class="footer-panel" aria-label="Footer">';
    echo '<div class="footer-inner">';
    echo '<section class="footer-about" aria-label="Mineacle Studios">';
    echo '<div class="footer-brand"><img src="' . h($footerLogoUrl) . '" alt="Mineacle Studios" draggable="false"></div>';
    echo '<p>Mineacle Studios is a small team of Minecraft developers building the custom systems behind Mineacle. After over a year of trial, error, and refinement, we are creating a smooth, polished, community-driven survival experience while staying true to the Minecraft everyone already loves.</p>';
    echo '<div class="footer-socials" aria-label="Social links">';
    foreach ($socialLinks as $link) {
        echo '<a href="' . h(mineacle_page_public_link($link['url'])) . '" aria-label="' . h($link['label']) . '">' . mineacle_page_icon((string) $link['key']) . '</a>';
    }
    echo '</div>';
    echo '</section>';
    echo '<nav class="footer-links" aria-label="Quick links"><h2>Quick Links:</h2>';
    foreach ($quickLinks as $link) {
        echo '<a href="' . h(mineacle_page_public_link($link['url'])) . '">' . h($link['label']) . '</a>';
    }
    echo '</nav>';
    echo '<section class="footer-bug-panel" aria-label="Report a bug">';
    echo '<a class="footer-bug-banner" href="' . h(mineacle_page_public_link($supportLink)) . '">';
    echo '<span><strong>Report a Bug</strong><small>Found an issue? Send it to Mineacle Studios.</small></span>';
    echo '<img src="/assets/brand/bug-mob-web.png" alt="" aria-hidden="true" draggable="false" loading="lazy" decoding="async">';
    echo '</a>';
    echo '</section>';
    echo '<p class="footer-bottom"><img src="/assets/brand/nav-logo-web.png" alt="" aria-hidden="true" draggable="false"><span>';
    echo 'Copyright © ' . h((string) $year) . ' Mineacle Studios. All Rights Reserved. Mineacle is not affiliated with or endorsed by Mojang Studios or Microsoft.';
    echo ' <span class="footer-policy-links">';
    foreach ($legalLinks as $link) {
        echo '<a href="' . h(mineacle_page_public_link($link['url'])) . '">' . h($link['label']) . '</a>';
    }
    echo '</span></span></p>';
    echo '</div>';
    echo '</footer>';
}

function mineacle_page_head(string $title = 'Leaderboards', array $options = []): void
{
    mineacle_security_headers();
    $config = mineacle_config();
    $site = $config['site'] ?? [];
    $name = mineacle_page_clean_text((string) ($site['name'] ?? 'Mineacle')) ?: 'Mineacle';
    $customTitle = mineacle_page_clean_text((string) ($options['meta_title'] ?? ''));
    $customDescription = mineacle_page_clean_text((string) ($options['meta_description'] ?? ''));
    $customCanonical = trim((string) ($options['canonical_url'] ?? ''));
    $metaTitle = $customTitle !== '' ? $customTitle : mineacle_page_meta_title($title, $name);
    $metaDescription = $customDescription !== '' ? $customDescription : mineacle_page_meta_description($title, $name);
    $canonicalUrl = $customCanonical !== '' ? $customCanonical : mineacle_page_canonical_url();

    echo '<!doctype html>';
    echo '<html lang="en">';
    echo '<head>';
    echo '<meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . h($metaTitle) . '</title>';
    echo '<meta name="description" content="' . h($metaDescription) . '">';
    echo '<link rel="canonical" href="' . h($canonicalUrl) . '">';
    echo '<meta property="og:site_name" content="' . h($name) . '">';
    echo '<meta property="og:type" content="website">';
    echo '<meta property="og:title" content="' . h($metaTitle) . '">';
    echo '<meta property="og:description" content="' . h($metaDescription) . '">';
    echo '<meta property="og:url" content="' . h($canonicalUrl) . '">';
    echo '<meta name="twitter:card" content="summary">';
    echo '<meta name="twitter:title" content="' . h($metaTitle) . '">';
    echo '<meta name="twitter:description" content="' . h($metaDescription) . '">';

    if (($options['robots'] ?? '') !== '') {
        $robots = (string) $options['robots'];
        echo '<meta name="robots" content="' . h($robots) . '">';
    }

    $assetVersion = mineacle_page_asset_version();

    echo '<link rel="icon" type="image/png" href="/assets/fav-web.png?v=' . h($assetVersion) . '">';
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
    echo '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@600;700;800&display=swap">';
    $stylesheets = $options['stylesheets'] ?? ['/assets/site.css'];

    if (!is_array($stylesheets) || $stylesheets === []) {
        $stylesheets = ['/assets/site.css'];
    }

    foreach ($stylesheets as $stylesheet) {
        $stylesheetUrl = trim((string) $stylesheet);

        if ($stylesheetUrl === '') {
            continue;
        }

        $separator = str_contains($stylesheetUrl, '?') ? '&' : '?';
        echo '<link rel="stylesheet" href="' . h($stylesheetUrl . $separator . 'v=' . $assetVersion) . '">';
    }
    echo '</head>';
    $bodyClass = mineacle_page_clean_text((string) ($options['body_class'] ?? ''));
    echo $bodyClass !== '' ? '<body class="' . h($bodyClass) . '">' : '<body>';
}

function mineacle_page_end(array $options = []): void
{
    $scripts = $options['scripts'] ?? ['/assets/site.js'];

    if (!is_array($scripts)) {
        $scripts = ['/assets/site.js'];
    }

    foreach ($scripts as $script) {
        $scriptUrl = trim((string) $script);

        if ($scriptUrl === '') {
            continue;
        }

        $separator = str_contains($scriptUrl, '?') ? '&' : '?';
        echo '<script src="' . h($scriptUrl . $separator . 'v=' . mineacle_page_asset_version()) . '"></script>';
    }

    echo '</body>';
    echo '</html>';
}
