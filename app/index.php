<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';

$config = mineacle_config();
$site = $config['site'] ?? [];
$home = h((string) ($site['home'] ?? 'https://mineacle.net'));
$store = h((string) ($site['store'] ?? 'https://store.mineacle.net'));
$bans = h((string) ($site['bans'] ?? 'https://bans.mineacle.net'));
$stats = h((string) ($site['stats'] ?? 'https://stats.mineacle.net'));
$vote = h((string) ($site['vote'] ?? 'https://vote.mineacle.net'));
$discord = h((string) ($site['discord'] ?? 'https://discord.gg/VwbwWftefM'));
$serverIpRaw = (string) ($site['ip'] ?? 'mineacle.net');
$serverIp = h($serverIpRaw);
$serverIpDisplay = h(strtoupper($serverIpRaw));

mineacle_page_head('Home', 'home');

?>
<main class="mineacle-home-shell" data-mineacle-home>
    <aside class="mineacle-home-rail" aria-label="Mineacle navigation">
        <a class="mineacle-home-logo" href="<?php echo $home; ?>" aria-label="Mineacle Home">
            <img src="assets/mineacle-logo-purple.png?v=home1.0.0" alt="Mineacle">
        </a>

        <nav class="mineacle-home-nav" aria-label="Primary links">
            <a class="mineacle-home-nav-link mineacle-home-nav-store" href="<?php echo $store; ?>" aria-label="Store" title="Store">
                <span class="mineacle-home-icon mineacle-home-icon-store"></span>
            </a>

            <div class="mineacle-home-nav-group">
                <a class="mineacle-home-nav-link mineacle-home-nav-vote" href="<?php echo $vote; ?>" aria-label="Vote" title="Vote">
                    <span class="mineacle-home-icon mineacle-home-icon-vote"></span>
                </a>
                <a class="mineacle-home-nav-link mineacle-home-nav-bans" href="<?php echo $bans; ?>" aria-label="Bans" title="Bans">
                    <span class="mineacle-home-icon mineacle-home-icon-bans"></span>
                </a>
                <a class="mineacle-home-nav-link mineacle-home-nav-stats" href="<?php echo $stats; ?>" aria-label="Stats" title="Stats">
                    <span class="mineacle-home-icon mineacle-home-icon-stats"></span>
                </a>
            </div>
        </nav>

        <a class="mineacle-home-discord" href="<?php echo $discord; ?>" target="_blank" rel="noopener" aria-label="Discord" title="Discord">
            <span class="mineacle-home-icon mineacle-home-icon-discord"></span>
        </a>
    </aside>

    <section class="mineacle-home-main" aria-label="Mineacle home">
        <section class="mineacle-home-card mineacle-home-hero" aria-labelledby="mineacleHomeTitle">
            <div class="mineacle-home-hero-copy">
                <p class="mineacle-home-kicker">Mineacle Network</p>
                <h1 id="mineacleHomeTitle">A cleaner hub for the Mineacle network.</h1>
                <p>Jump into the server, find punishments, vote rewards, store access, and network stats from one focused home base.</p>
                <button class="mineacle-home-server" type="button" data-copy-ip="<?php echo $serverIp; ?>" data-default-label="<?php echo $serverIpDisplay; ?>">
                    <span class="mineacle-home-server-ip"><?php echo $serverIpDisplay; ?></span>
                    <small><b id="mineaclePlayerCountValue">0</b> players online</small>
                </button>
            </div>
            <img class="mineacle-home-hero-logo" src="assets/mineacle-main-logo.png?v=home1.0.0" alt="" aria-hidden="true">
        </section>

        <aside class="mineacle-home-card mineacle-home-login" aria-labelledby="mineacleLoginTitle">
            <div>
                <p class="mineacle-home-kicker">Community</p>
                <h2 id="mineacleLoginTitle">Log In</h2>
                <p>Use Discord for support, staff tools, appeals, and community access.</p>
            </div>
            <a href="<?php echo $discord; ?>" target="_blank" rel="noopener">Open Discord</a>
        </aside>

        <article class="mineacle-home-card mineacle-home-info mineacle-home-info-store">
            <span class="mineacle-home-info-icon mineacle-home-icon mineacle-home-icon-store"></span>
            <h2>Store</h2>
            <p>Ranks, keys, perks, and upgrades for your next session.</p>
            <a href="<?php echo $store; ?>">Visit store</a>
        </article>

        <article class="mineacle-home-card mineacle-home-info mineacle-home-info-bans">
            <span class="mineacle-home-info-icon mineacle-home-icon mineacle-home-icon-bans"></span>
            <h2>Bans</h2>
            <p>Search public active ban records from the live database.</p>
            <a href="<?php echo $bans; ?>">Search bans</a>
        </article>

        <article class="mineacle-home-card mineacle-home-info mineacle-home-info-vote">
            <span class="mineacle-home-info-icon mineacle-home-icon mineacle-home-icon-vote"></span>
            <h2>Vote</h2>
            <p>Support the server and collect rewards through vote links.</p>
            <a href="<?php echo $vote; ?>">Vote now</a>
        </article>
    </section>
</main>
<?php mineacle_page_end('home'); ?>
