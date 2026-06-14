<?php
declare(strict_types=1);

/*
 * Public canonical route enforcement.
 * If someone manually visits /bans.php, redirect them to /
 */
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
if (!defined('MINEACLE_INTERNAL_RENDER') && preg_match('~/bans\.php$~i', $requestPath)) {
    header('Location: /', true, 301);
    exit;
}

require_once __DIR__ . '/includes/layout.php';
$config = mineacle_config();
mineacle_page_head('Bans');
?>
<body>
<?php mineacle_header('bans'); ?>

<main class="page">
  <?php mineacle_shared_hero([
      'eyebrow' => 'Mineacle moderation',
      'kicker' => 'Mineacle safety systems',
      'title' => 'Public Ban List',
      'copy' => 'Mineacle is built to feel safe, clear, and easy to use. Search active bans, review public punishment records, and see how our safety systems help protect the community.',
      'cta_text' => 'Browse public bans',
      'cta_anchor' => '#bans'
  ]); ?>

  <section class="shell trust-section">
    <div class="section-heading">
      <span class="section-kicker">Why Mineacle takes safety seriously</span>
      <h2>Our protection flow</h2>
      <p>We want Mineacle to stay welcoming, polished, and safe for all ages allowed on the server. These systems help us protect players, their experience, and the community as a whole.</p>
    </div>

    <div class="trust-grid">
      <article class="trust-card">
        <div class="trust-icon"><img src="assets/shield.svg" alt=""></div>
        <strong>1. Mineacle Security</strong>
        <p>Commands, access, and public-facing systems are filtered and cleaned up to keep the server easier to use and safer to navigate.</p>
      </article>
      <article class="trust-card">
        <div class="trust-icon"><img src="assets/lock.svg" alt=""></div>
        <strong>2. MineacleClientGuard</strong>
        <p>Allowed client checks and client-side protection help reduce unsafe connections and keep the gameplay environment more trustworthy.</p>
      </article>
      <article class="trust-card">
        <div class="trust-icon"><img src="assets/hammer-ban.png" alt=""></div>
        <strong>3. Serious moderation</strong>
        <p>Ban records exist for transparency. Major offenses are handled seriously so players understand that Mineacle is meant to stay safe and fair.</p>
      </article>
    </div>
  </section>

  <section class="shell bans-section" id="bans">
    <div class="section-heading compact">
      <span class="section-kicker">Public records</span>
      <h2>Active Bans</h2>
      <p>Sorted newest to oldest. Unbanned and expired players are removed automatically.</p>
    </div>

    <div class="ban-list-wrap">
      <div class="ban-toolbar">
        <div class="ban-title">
          <span class="tag compact">25 per page</span>
          <h3>Search the ban list</h3>
          <p>Use a username to quickly find a player</p>
        </div>

        <div class="searchbar">
          <img src="assets/search.svg" alt="">
          <input id="banSearch" type="search" placeholder="Search username..." autocomplete="off" maxlength="32">
        </div>

        <div class="ban-count" id="banCount">Loading...</div>
      </div>

      <div class="ban-list" id="banList">
        <div class="empty">Loading LiteBans data...</div>
      </div>

      <div class="ban-pagination" id="banPagination" hidden>
        <button class="btn soft" type="button" id="prevPage">Previous</button>
        <span id="pageInfo">Page 1 of 1</span>
        <button class="btn soft" type="button" id="nextPage">Next</button>
      </div>
    </div>
  </section>
</main>

<div class="modal" id="banModal" aria-hidden="true">
  <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="modalName">
    <div class="modal-head">
      <img id="modalAvatar" src="" alt="">
      <div class="modal-title">
        <h2 id="modalName">Player</h2>
        <span id="modalStatus" class="badge active">Active</span>
      </div>
      <button class="close-modal" type="button" data-close-modal aria-label="Close">×</button>
    </div>
    <div class="modal-body">
      <div class="detail-grid">
        <div class="detail"><span>Reason</span><span id="modalReason"></span></div>
        <div class="detail"><span>Type</span><span id="modalType"></span></div>
        <div class="detail"><span>Duration</span><span id="modalDuration"></span></div>
        <div class="detail"><span>Date</span><span id="modalDate"></span></div>
        <div class="detail"><span>Appeal ID</span><span id="modalAppeal"></span></div>
        <div class="detail"><span>Email</span><span id="modalEmail"></span></div>
        <div class="detail"><span>Discord</span><span id="modalDiscord"></span></div>
      </div>
      <div class="modal-actions" id="modalActions"></div>
      <div class="modal-note" id="modalNote"></div>
    </div>
  </div>
</div>

<?php mineacle_footer(); ?>
</body>
</html>
