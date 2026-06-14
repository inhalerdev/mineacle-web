<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

function mineacle_page_head(string $title): void {
    mineacle_security_headers(false);
    $config = mineacle_config();
    $name = h($config['site']['name']);

    echo '<!doctype html>';
    echo '<html lang="en">';
    echo '<head>';
    echo '<meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<title>' . h($title) . ' | ' . $name . '</title>';
    echo '<meta name="description" content="Mineacle public bans portal">';
    echo '<link rel="icon" type="image/png" href="assets/favicon.png?v=11.1">';
    echo '<link rel="stylesheet" href="assets/styles.css?v=11.1">';
    echo '</head>';
}

function mineacle_header(string $active = 'bans'): void {
    $config = mineacle_config();

    $ip = h($config['site']['ip']);
    $discord = h($config['site']['discord']);
    $store = h($config['site']['store'] ?? 'https://store.mineacle.net');
    $vote = h($config['site']['vote'] ?? 'https://vote.mineacle.net');

    echo '<header class="site-header">';
    echo '<div class="shell header-shell">';

    echo '<a class="brand-link" href="/">';
    echo '<img class="brand-mark" src="assets/brand-mark.png?v=11.1" alt="Mineacle">';
    echo '<span class="brand-copy">';
    echo '<strong>Mineacle Network</strong>';
    echo '<small>The original survival server</small>';
    echo '</span>';
    echo '</a>';

    echo '<nav class="primary-nav" aria-label="Primary">';
    echo '<a class="' . ($active === 'store' ? 'active' : '') . '" href="' . $store . '"><img src="assets/basket.svg" alt=""><span>Store</span></a>';
    echo '<a class="' . ($active === 'vote' ? 'active' : '') . '" href="' . $vote . '"><img src="assets/vote.svg" alt=""><span>Vote</span></a>';
    echo '<a class="' . ($active === 'bans' ? 'active' : '') . '" href="/"><img src="assets/hammer-ban.png" alt=""><span>Bans</span></a>';
    echo '</nav>';

    echo '<div class="header-actions">';
    echo '<a class="ghost-btn" href="' . $discord . '" target="_blank" rel="noopener"><img src="assets/discord.svg" alt=""><span>Discord</span></a>';
    echo '<button class="solid-btn copy-ip" type="button" data-copy="' . $ip . '"><img src="assets/copy.svg" alt=""><span>Copy IP</span></button>';
    echo '</div>';

    echo '</div>';
    echo '</header>';
}

function mineacle_shared_hero(array $options = []): void {
    $config = mineacle_config();
    $page_kicker = $options['kicker'] ?? 'Mineacle Safety';
    $page_title = $options['title'] ?? 'Public Ban List';
    $page_copy = $options['copy'] ?? 'Search active punishments, review public records, and see how Mineacle protects players with a safe, polished experience.';
    $page_eye = $options['eyebrow'] ?? 'Shared hero section';
    $page_cta = $options['cta_text'] ?? 'Browse bans';
    $page_anchor = $options['cta_anchor'] ?? '#bans';
    $ip = h($config['site']['ip']);
    $discord = h($config['site']['discord']);

    echo '<section class="hero-block shell">';
    echo '<div class="hero-frame">';
    echo '<div class="hero-backdrop" aria-hidden="true"></div>';
    echo '<div class="hero-overlay" aria-hidden="true"></div>';

    echo '<div class="hero-main">';
    echo '<div class="hero-copy">';
    echo '<span class="hero-eyebrow">' . h($page_eye) . '</span>';
    echo '<h1>' . h($page_title) . '</h1>';
    echo '<p>' . h($page_copy) . '</p>';

    echo '<div class="hero-actions">';
    echo '<a class="hero-btn hero-btn-primary" href="' . h($page_anchor) . '"><img src="assets/hammer-ban.png" alt=""><span>' . h($page_cta) . '</span></a>';
    echo '<a class="hero-btn hero-btn-secondary" href="' . $discord . '" target="_blank" rel="noopener"><img src="assets/discord.svg" alt=""><span>Support on Discord</span></a>';
    echo '<button class="hero-btn hero-btn-secondary copy-ip" type="button" data-copy="' . $ip . '"><img src="assets/copy.svg" alt=""><span>' . $ip . '</span></button>';
    echo '</div>';

    echo '<div class="hero-points">';
    echo '<span><img src="assets/shield.svg" alt=""> Safe and moderated</span>';
    echo '<span><img src="assets/info.svg" alt=""> Public records</span>';
    echo '<span><img src="assets/vote.svg" alt=""> Same Mineacle branding across every page</span>';
    echo '</div>';
    echo '</div>';

    echo '<aside class="hero-brand-card">';
    echo '<img class="hero-wordmark" src="assets/mineacle-logo.png?v=11.1" alt="Mineacle">';
    echo '<div class="hero-brand-text">';
    echo '<span class="hero-card-kicker">' . h($page_kicker) . '</span>';
    echo '<strong>One shared website style</strong>';
    echo '<p>A cleaner purple, obsidian, and end-themed layout that can carry across bans, vote, store, and future Mineacle pages.</p>';
    echo '</div>';
    echo '</aside>';

    echo '</div>';
    echo '</div>';
    echo '</section>';
}

function mineacle_footer(): void {
    echo '<footer class="site-footer"><div class="shell footer-shell">';
    echo '<div class="footer-brand">';
    echo '<img src="assets/brand-mark.png?v=11.1" alt="Mineacle">';
    echo '<div><strong>Mineacle Network</strong><span>Public punishment records and player safety information</span></div>';
    echo '</div>';
    echo '<p>Mineacle is not affiliated with Mojang Studios or Microsoft. All trademarks belong to their respective owners.</p>';
    echo '</div></footer>';

    echo '<div class="mineacle-toast" id="toast" role="status" aria-live="polite">';
    echo '<div class="mineacle-toast-icon"><img src="assets/copy.svg" alt=""></div>';
    echo '<div class="mineacle-toast-copy">';
    echo '<small id="toastEyebrow">Mineacle Network</small>';
    echo '<strong id="toastTitle">Server IP copied</strong>';
    echo '<span id="toastText">Join with <b id="toastValue">mineacle.net</b></span>';
    echo '</div>';
    echo '</div>';

    echo '<script src="assets/main.js?v=11.1"></script>';
}
