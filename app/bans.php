<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';

mineacle_page_head('Bans');
mineacle_header('bans');

?>
<main class="mineacle-litebans-page" data-mineacle-bans-app>
    <div class="mineacle-litebans-list-view" id="banListView">
        <section class="mineacle-lb-hero" aria-label="Mineacle punishments">
            <div class="mineacle-lb-hero-card">
                <span class="mineacle-lb-hero-mark">M</span>
                <div>
                    <h1>Mineacle Punishments</h1>
                    <p>Search public LiteBans records, review staff action details, and see repeat punishment history with Mineacle's cleaner record view.</p>
                </div>
            </div>
        </section>

        <section class="mineacle-lb-search-panel" aria-label="Player search">
            <div class="mineacle-lb-section-title">
                <span>Player Search</span>
                <h2>Find a record</h2>
            </div>

            <form class="mineacle-lb-search" id="banSearchForm" role="search">
                <label class="sr-only" for="banSearch">Search punishments</label>
                <div class="mineacle-lb-search-field">
                    <span aria-hidden="true">Player</span>
                    <input id="banSearch" class="js-ban-search" type="text" name="q" autocomplete="off" placeholder="Enter player name, UUID, staff, reason, or server">
                    <button class="ban-search-clear js-ban-clear" id="clearSearch" type="button" aria-label="Clear search" title="Clear search">x</button>
                </div>
                <button class="mineacle-lb-primary-action" type="submit">Search</button>
            </form>
        </section>

        <section class="mineacle-lb-stats" aria-label="LiteBans statistics">
            <div class="mineacle-lb-section-title mineacle-lb-section-title-center">
                <span>Server Statistics</span>
                <h2>LiteBans overview</h2>
            </div>
            <div class="mineacle-lb-stat-grid" id="mineacleStatsGrid">
                <article class="mineacle-lb-stat-card is-red"><strong>--</strong><span>Active Bans</span><small>of --</small></article>
                <article class="mineacle-lb-stat-card is-gold"><strong>--</strong><span>Active Mutes</span><small>of --</small></article>
                <article class="mineacle-lb-stat-card is-cyan"><strong>--</strong><span>Total Warnings</span><small>all time</small></article>
                <article class="mineacle-lb-stat-card is-slate"><strong>--</strong><span>Total Kicks</span><small>all time</small></article>
            </div>
        </section>

        <section class="mineacle-lb-activity" aria-label="Recent activity">
            <div class="mineacle-lb-section-title">
                <span>Recent Activity</span>
                <h2>Latest records</h2>
            </div>
            <div class="mineacle-lb-recent-grid">
                <article class="mineacle-lb-panel">
                    <header>
                        <h3>Recent Bans</h3>
                        <span class="mineacle-lb-count-pill" id="recentBanCount">0</span>
                    </header>
                    <div class="mineacle-lb-recent-list" id="recentBanList">
                        <div class="mineacle-lb-loading">Loading recent bans</div>
                    </div>
                </article>

                <article class="mineacle-lb-panel">
                    <header>
                        <h3>Record Notes</h3>
                        <span class="mineacle-lb-count-pill is-soft">Live</span>
                    </header>
                    <div class="mineacle-lb-note-panel">
                        <strong>LiteBans connected</strong>
                        <p>Records keep their original reason, staff, date, server origin, server scope, duration, flags, and related punishment history when the database provides it.</p>
                    </div>
                </article>
            </div>
        </section>

        <section class="mineacle-lb-table-section bans-v3-results" id="ban-results" aria-label="Punishment results">
            <div class="mineacle-lb-table-head">
                <div>
                    <span class="bans-v3-kicker">Records</span>
                    <h2>Bans</h2>
                </div>
                <div class="bans-list-meta js-ban-meta" id="banCount">Loading records</div>
            </div>

            <div class="mineacle-lb-table-shell">
                <div class="ban-table js-ban-table mineacle-lb-table" id="banList" aria-live="polite">
                    <div class="mineacle-lb-loading">Loading bans</div>
                </div>
            </div>

            <div class="pagination-row mineacle-lb-pagination" id="banPagination">
                <button class="btn soft js-ban-prev" id="prevPage" type="button">Previous</button>
                <span class="page-indicator js-ban-page" id="pageInfo">Page 1</span>
                <button class="btn soft js-ban-next" id="nextPage" type="button">Next</button>
            </div>
        </section>
    </div>

    <section class="mineacle-lb-detail-view" id="banDetailView" aria-label="Ban detail" hidden>
        <nav class="mineacle-lb-breadcrumb" aria-label="Breadcrumb">
            <a href="./" data-ban-list-link>Home</a>
            <span>/</span>
            <a href="./" data-ban-list-link>Bans</a>
            <span>/</span>
            <strong id="detailBreadcrumbId">#</strong>
        </nav>

        <article class="mineacle-lb-detail-card">
            <header class="mineacle-lb-detail-head">
                <div>
                    <span class="mineacle-lb-detail-kicker" id="detailType">Ban</span>
                    <h1 id="detailTitle">Ban record</h1>
                </div>
                <span class="mineacle-lb-status-badge" id="detailStatus">Loading</span>
            </header>

            <div class="mineacle-lb-detail-body">
                <aside class="mineacle-lb-player-card">
                    <img id="detailAvatar" src="assets/mineacle-square-logo.png" alt="">
                    <strong id="detailPlayer">Player</strong>
                    <span id="detailAppealId">MCL-000000</span>
                </aside>

                <div class="mineacle-lb-detail-grid" id="detailGrid"></div>
            </div>
        </article>

        <article class="mineacle-lb-panel mineacle-lb-other-panel">
            <header>
                <h2>Other Punishments</h2>
                <span class="mineacle-lb-count-pill" id="detailOtherCount">0</span>
            </header>
            <div class="mineacle-lb-other-table" id="detailOtherPunishments"></div>
        </article>
    </section>
</main>
<?php mineacle_footer(); ?>
