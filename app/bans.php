<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
$config = mineacle_config();
mineacle_page_head('Bans');
?>
<body>
<?php mineacle_header(); ?>

<main class="page">
  <section class="hero shell">
    <div class="hero-card">
      <div class="hero-copy">
        <span class="tag">Mineacle Player Safety</span>
        <h1>Public Ban List</h1>
        <p>
          Search active Mineacle punishments. This page only shows safe public LiteBans details —
          never private player IP addresses or database secrets.
        </p>

        <div class="hero-actions">
          <button class="btn primary copy-ip" data-copy="<?= h($config['site']['ip']) ?>">
            <img src="assets/copy.svg" alt=""> Copy IP
          </button>
          <a class="btn soft" href="<?= h($config['site']['discord']) ?>" target="_blank" rel="noopener">
            <img src="assets/discord.svg" alt=""> Get Help
          </a>
        </div>
      </div>

      <div class="hero-logo-wrap" aria-hidden="true">
        <img class="hero-logo" src="assets/mineacle-logo.png?v=8" alt="">
      </div>
    </div>
  </section>

  <section class="shell bans-section">
    <div class="ban-list-wrap">
      <div class="ban-toolbar">
        <div class="ban-title">
          <span class="tag compact">Newest First</span>
          <h2>Active Bans</h2>
          <p>Unbanned and expired players disappear automatically</p>
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
    </div>

    <div class="rule-grid">
      <article class="rule-card">
        <img src="assets/shield.svg" alt="">
        <strong>Player Bans</strong>
        <span>Eligible active player bans may show an unban payment option</span>
      </article>
      <article class="rule-card">
        <img src="assets/lock.svg" alt="">
        <strong>IP Bans</strong>
        <span>Shown as permanently banned with no public dispute or payment option</span>
      </article>
      <article class="rule-card">
        <img src="assets/info.svg" alt="">
        <strong>Info Popup</strong>
        <span>Shows reason, duration, date, appeal ID, email, Discord, and action</span>
      </article>
    </div>
  </section>
</main>

<div class="modal" id="banModal" aria-hidden="true">
  <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="modalName">
    <div class="modal-head">
      <img id="modalAvatar" src="" alt="">
      <div>
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
