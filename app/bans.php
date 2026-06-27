<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';

$config = mineacle_config();
$site = $config['site'] ?? [];
$home = h((string) ($site['home'] ?? 'https://mineacle.net/home'));
$bans = h((string) ($site['bans'] ?? 'https://bans.mineacle.net'));
$stats = h((string) ($site['stats'] ?? 'https://stats.mineacle.net'));
$store = h((string) ($site['store'] ?? 'https://store.mineacle.net'));
$discord = h((string) ($site['discord'] ?? 'https://discord.gg/VwbwWftefM'));

mineacle_page_head('Bans');

?>
<main class="mineacle-bans-shell" data-mineacle-bans-app>
    <aside class="mineacle-bans-rail" aria-label="Mineacle navigation">
        <a class="mineacle-bans-rail-logo" href="<?php echo $bans; ?>" aria-label="Mineacle Bans">
            <img src="assets/mineacle-logo-purple.png?v=bansclean1.0.5" alt="Mineacle">
        </a>

        <nav class="mineacle-bans-rail-nav" aria-label="Primary links">
            <a class="mineacle-bans-rail-link" href="<?php echo $home; ?>" aria-label="Home" title="Home">
                <img src="assets/mineacle-logo-purple.png?v=bansclean1.0.5" alt="">
            </a>
            <a class="mineacle-bans-rail-link" href="<?php echo $store; ?>" aria-label="Store" title="Store">
                <img src="assets/store.svg?v=bansclean1.0.5" alt="">
            </a>
            <a class="mineacle-bans-rail-link is-active" href="<?php echo $bans; ?>" aria-label="Bans" title="Bans">
                <img src="assets/hammer.svg?v=bansclean1.0.5" alt="">
            </a>
            <a class="mineacle-bans-rail-link" href="<?php echo $stats; ?>" aria-label="Stats" title="Stats">
                <img src="assets/users.svg?v=bansclean1.0.5" alt="">
            </a>
        </nav>

        <a class="mineacle-bans-rail-link mineacle-bans-rail-support" href="<?php echo $discord; ?>" target="_blank" rel="noopener" aria-label="Discord support" title="Discord support">
            <img src="assets/discord.svg?v=bansclean1.0.5" alt="">
        </a>
    </aside>

    <div class="mineacle-bans-main">
        <header class="mineacle-bans-topbar">
            <form class="mineacle-bans-search" id="banSearchForm" role="search">
                <label class="sr-only" for="banSearch">Search punishments</label>
                <div class="mineacle-bans-search-field">
                    <img src="assets/search.svg?v=bansclean1.0.5" alt="" aria-hidden="true">
                    <input id="banSearch" type="text" name="q" autocomplete="off" placeholder="Search active banned players, UUID, staff, reason, or server">
                    <button class="mineacle-bans-clear" id="clearSearch" type="button" aria-label="Clear search" title="Clear search">x</button>
                </div>
                <button class="mineacle-bans-search-action" type="submit">Search</button>
            </form>
        </header>
    </div>
</main>
<?php mineacle_footer(); ?>
