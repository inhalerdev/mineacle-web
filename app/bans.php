<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/layout.php';
$config = mineacle_config(); $site = $config['site'] ?? [];
$bans = h((string) ($site['bans'] ?? 'https://bans.mineacle.net')); $stats = h((string) ($site['stats'] ?? 'https://stats.mineacle.net')); $vote = h((string) ($site['vote'] ?? 'https://vote.mineacle.net')); $store = h((string) ($site['store'] ?? 'https://store.mineacle.net')); $discord = h((string) ($site['discord'] ?? 'https://discord.gg/VwbwWftefM')); $x = h((string) ($site['x'] ?? 'https://x.com/mineaclenetwork')); $ip = h((string) ($site['ip'] ?? 'mineacle.net'));
mineacle_page_head('Bans', 'Search active Mineacle bans and learn how Mineacle Client Guard helps protect the server community');
?>
<main class="app-shell" data-app data-server-ip="<?php echo $ip; ?>">
    <aside class="rail" aria-label="Mineacle navigation">
        <a class="rail-logo" href="<?php echo $bans; ?>" aria-label="Mineacle Bans"><img src="assets/icons/mineacle-m.svg" alt="Mineacle"></a>
        <nav class="rail-nav" aria-label="Primary links">
            <a class="rail-link active" href="<?php echo $bans; ?>" aria-label="Bans" title="Bans"><img src="assets/icons/hammer.svg" alt=""></a>
            <a class="rail-link" href="<?php echo $stats; ?>" aria-label="Stats" title="Stats"><img src="assets/icons/users.svg" alt=""></a>
            <a class="rail-link" href="<?php echo $vote; ?>" aria-label="Vote" title="Vote"><img src="assets/icons/star.svg" alt=""></a>
            <a class="rail-link" href="<?php echo $store; ?>" aria-label="Store" title="Store"><img src="assets/icons/store.svg" alt=""></a>
        </nav>
        <a class="rail-link rail-discord" href="<?php echo $discord; ?>" target="_blank" rel="noopener" aria-label="Discord" title="Discord"><img src="assets/icons/discord.svg" alt=""></a>
    </aside>

    <section class="content-lane">
        <header class="topbar" aria-label="Search">
            <form class="search-module module" id="banSearchForm" role="search">
                <label class="sr-only" for="banSearch">Search punishments</label>
                <input id="banSearch" name="q" autocomplete="off" placeholder="Search bans, usernames, UUID, staff, reason, or server">
                <button class="search-button" id="banSearchAction" type="submit" aria-label="Search"><img src="assets/icons/search.svg" alt=""></button>
            </form>
        </header>

        <section class="hero-module module" aria-labelledby="heroTitle">
            <div class="hero-copy">
                <p class="eyebrow">Mineacle Network</p>
                <h1 id="heroTitle">Active Ban Records</h1>
                <p>Search public active bans and view basic punishment details from the Mineacle database</p>
                <button class="ip-pill" id="copyIpButton" type="button" data-copy-ip="<?php echo $ip; ?>">
                    <span class="ip-pill-main" id="copyIpMain">MINEACLE.NET</span>
                    <span class="ip-pill-sub">CURRENTLY ONLINE: <b id="onlineCount">0</b></span>
                </button>
            </div>
            <div class="hero-mark" aria-hidden="true"><img src="assets/icons/mineacle-m.svg" alt=""></div>
        </section>

        <section class="info-grid" aria-label="Mineacle Client Guard protection modules">
            <button class="info-card module" type="button" data-info="client"><span>01</span><strong>Client Guard</strong><small>Allowed clients and suspicious brand checks</small></button>
            <button class="info-card module" type="button" data-info="combat"><span>02</span><strong>Combat Protection</strong><small>Detects suspicious combat and movement behavior</small></button>
            <button class="info-card module" type="button" data-info="community"><span>03</span><strong>Community Safety</strong><small>Protects survival economy, builds, and fair play</small></button>
        </section>

        <section class="table-module module" aria-labelledby="recentBansTitle">
            <div class="section-head">
                <div><p class="eyebrow">Records</p><h2 id="recentBansTitle">Recent Active Bans</h2></div>
                <div class="table-meta" id="tableMeta">Loading records</div>
            </div>
            <div class="table-wrap">
                <table class="bans-table">
                    <thead><tr><th>Player</th><th>Reason</th><th>Staff</th><th>Server</th><th>Date</th><th>Status</th><th></th></tr></thead>
                    <tbody id="bansTableBody"><tr><td colspan="7" class="loading-cell">Loading active bans</td></tr></tbody>
                </table>
            </div>
            <div class="pagination" id="pagination" hidden><button id="prevPage" type="button">Previous</button><span id="pageInfo">Page 1</span><button id="nextPage" type="button">Next</button></div>
        </section>

        <footer class="footer-module module" aria-label="Mineacle footer">
            <section class="footer-brand">
                <img class="footer-logo" src="assets/mineacle-studios.png" alt="Mineacle Studios">
                <h2>Mineacle</h2>
                <p>Protecting a fair Minecraft survival community with transparent active ban records</p>
                <div class="footer-socials"><a href="<?php echo $discord; ?>" target="_blank" rel="noopener" aria-label="Discord"><img src="assets/icons/discord.svg" alt=""></a><a href="<?php echo $x; ?>" target="_blank" rel="noopener" aria-label="X"><img src="assets/icons/x.svg" alt=""></a></div>
            </section>
            <nav class="footer-column" aria-label="Quick links"><h3>Quick Links</h3><a href="<?php echo $bans; ?>">Bans</a><a href="<?php echo $stats; ?>">Stats</a><a href="<?php echo $vote; ?>">Vote</a><a href="<?php echo $store; ?>">Store</a></nav>
            <nav class="footer-column" aria-label="Support"><h3>Support</h3><a href="<?php echo $discord; ?>" target="_blank" rel="noopener">Discord</a><a href="mailto:<?php echo h((string) ($site['support_email'] ?? 'support@mineacle.net')); ?>">Contact</a><a href="<?php echo $bans; ?>">Records</a></nav>
            <nav class="footer-column" aria-label="Legal"><h3>Legal</h3><a href="#">Terms of Use</a><a href="#">Privacy Policy</a><a href="#">Appeal Policy</a></nav>
            <div class="footer-bottom"><span>Copyright © 2026 Mineacle Network. All Rights Reserved.</span><span>Not affiliated with Microsoft or Mojang AB.</span></div>
        </footer>
    </section>
</main>

<div class="modal-backdrop" id="modalBackdrop" hidden>
    <section class="modal-card" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
        <button class="modal-close" id="modalClose" type="button" aria-label="Close">×</button>
        <div id="modalContent"></div>
    </section>
</div>
<?php mineacle_page_end(); ?>
