<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';

mineacle_page_head('Bans | Mineacle Network');
mineacle_header('bans');

?>
<main class="bans-v3-page">
    <section class="bans-v3-hero bans-v32-logo-only-fold bans-v34-background-fold bans-records-hero" aria-label="Public ban records">
        <div class="ban-hero-content">
            <div class="ban-hero-copy">
                <span class="ban-hero-kicker">Mineacle Enforcement</span>
                <h1>Public Ban Records</h1>
                <p>Active bans are listed openly so players can see how Mineacle keeps Survival fair. MineacleClientGuard and staff review suspicious client, movement, combat, and building patterns before action is taken.</p>
            </div>

            <div class="guard-info-tiles" aria-label="What MineacleClientGuard watches for">
                <article class="guard-info-tile">
                    <span class="guard-info-number">01</span>
                    <h2>Allowed clients</h2>
                    <p>Checks client brand signals when available and compares them to the launchers and loaders we allow. One signal helps review, but does not decide everything by itself.</p>
                </article>
                <article class="guard-info-tile">
                    <span class="guard-info-number">02</span>
                    <h2>Movement review</h2>
                    <p>Watches for movement that does not line up with normal Minecraft physics, including fly-like motion, odd vertical changes, and reduced knockback.</p>
                </article>
                <article class="guard-info-tile">
                    <span class="guard-info-number">03</span>
                    <h2>Combat &amp; building</h2>
                    <p>Looks for repeated patterns around attack timing, aura-like hits, fast placement, auto placement, and scaffold-style building.</p>
                </article>
                <article class="guard-info-tile">
                    <span class="guard-info-number">04</span>
                    <h2>Evidence over time</h2>
                    <p>Flags build a history first. Lower confidence alerts staff, repeated patterns raise violations, and obvious repeat abuse can lead to automatic action.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="bans-v3-results" id="ban-results" aria-label="Active ban results">
        <div class="bans-v3-results-head">
            <div>
                <span class="bans-v3-kicker">Search Records</span>
                <h2>Active bans</h2>
            </div>
            <div class="bans-list-meta js-ban-meta" id="banCount">Loading records</div>
        </div>

        <form class="bans-v3-search js-ban-search-form bans-v31-results-search" id="banSearchForm" role="search">
            <label class="sr-only" for="banSearch">Search bans</label>
            <input id="banSearch" class="js-ban-search" type="search" name="q" autocomplete="off" placeholder="Search Minecraft username">
            <button class="btn red" type="submit">Search</button>
            <button class="btn soft js-ban-clear" id="clearSearch" type="button">Clear</button>
        </form>

        <div class="ban-table-shell">
            <div class="ban-table js-ban-table" id="banList" aria-live="polite">
                <div class="ban-loading">Loading active bans</div>
            </div>
        </div>

        <div class="pagination-row" id="banPagination">
            <button class="btn soft js-ban-prev" id="prevPage" type="button">Previous</button>
            <span class="page-indicator js-ban-page" id="pageInfo">Page 1</span>
            <button class="btn soft js-ban-next" id="nextPage" type="button">Next</button>
        </div>
    </section>
</main>
<?php mineacle_footer(); ?>
