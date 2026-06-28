(() => {
  const app = document.querySelector('[data-app]');
  if (!app) return;

  const $ = (id) => document.getElementById(id);
  const state = { page: 1, search: '', loading: false };

  const form = $('banSearchForm');
  const input = $('banSearch');
  const tbody = $('bansTableBody');
  const meta = $('tableMeta');
  const pagination = $('pagination');
  const prev = $('prevPage');
  const next = $('nextPage');
  const pageInfo = $('pageInfo');
  const modal = $('modalBackdrop');
  const modalContent = $('modalContent');
  const modalClose = $('modalClose');
  const copyBtn = $('copyIpButton');

  const infoText = {
    client: {
      title: 'Mineacle Client Guard',
      body: 'Mineacle Client Guard checks the client environment at a high level so the server can reject or review clients that do not match the allowed play experience. It is designed to reduce unfair modded clients without exposing exact detection logic publicly.',
      points: [
        ['Client brand checks', 'Looks for unreadable, missing, or suspicious client-brand signals'],
        ['Allowed environment focus', 'Supports normal player access while helping staff identify risky clients'],
        ['Private enforcement details', 'Detection thresholds and bypass-sensitive details are not shown on the public website']
      ]
    },
    combat: {
      title: 'Combat and Movement Protection',
      body: 'Mineacle watches for suspicious combat and movement behavior that can damage PvP integrity, player trust, and the survival economy.',
      points: [
        ['Combat fairness', 'Reviews unusual combat patterns and impossible interaction behavior'],
        ['Movement safety', 'Helps catch movement anomalies such as unauthorized flight or extreme movement'],
        ['Admin visibility', 'Suspicious signals can be surfaced for staff review and enforcement']
      ]
    },
    community: {
      title: 'Community Safety',
      body: 'The goal is not just punishment. It is protecting real players, builds, PvP, trading, and the long-term server economy.',
      points: [
        ['Economy protection', 'Reduces automation and unfair advantage that can distort player progress'],
        ['World protection', 'Discourages risky client behavior and tools that threaten survival worlds'],
        ['Transparent records', 'Public ban records help players understand active enforcement without exposing private staff data']
      ]
    }
  };

  const esc = (value) => String(value ?? '').replace(/[&<>'"]/g, (char) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    "'": '&#039;',
    '"': '&quot;'
  }[char]));

  const setModal = (html) => {
    if (!modal || !modalContent) return;
    modalContent.innerHTML = html;
    modal.hidden = false;
    document.body.style.overflow = 'hidden';
  };

  const closeModal = () => {
    if (!modal || !modalContent) return;
    modal.hidden = true;
    modalContent.innerHTML = '';
    document.body.style.overflow = '';
  };

  const renderInfoModal = (item) => {
    const points = item.points.map(([label, text]) => (
      `<div class="detail-item"><span>${esc(label)}</span><strong>${esc(text)}</strong></div>`
    )).join('');

    setModal(`<h2 id="modalTitle">${esc(item.title)}</h2><p>${esc(item.body)}</p><div class="detail-grid">${points}</div>`);
  };

  const updateStatusCount = (value) => {
    const online = $('onlineCount');
    if (online) online.textContent = String(value ?? 0);
  };

  const loadStatus = async () => {
    try {
      const response = await fetch('api/server-status.php', { cache: 'no-store' });
      const data = await response.json();
      updateStatusCount(data.players_online);
    } catch (error) {
      updateStatusCount(0);
    }
  };

  const renderRows = (rows) => {
    if (!tbody) return;

    if (!Array.isArray(rows) || rows.length === 0) {
      tbody.innerHTML = '<tr><td colspan="7" class="loading-cell">No active bans found</td></tr>';
      return;
    }

    tbody.innerHTML = rows.map((ban) => `<tr>
      <td><div class="player-cell"><img loading="lazy" src="${esc(ban.skin)}" alt=""><span>${esc(ban.username)}</span></div></td>
      <td class="reason-cell">${esc(ban.reason)}</td>
      <td>${esc(ban.staff)}</td>
      <td>${esc(ban.server)}</td>
      <td>${esc(ban.date)}</td>
      <td><span class="status-pill ${esc(ban.status_type)}">${esc(ban.status)}</span></td>
      <td><button class="row-button" type="button" data-ban-id="${esc(ban.id)}">Info</button></td>
    </tr>`).join('');
  };

  const renderPagination = (paginationData) => {
    if (!pagination || !pageInfo || !prev || !next) return;

    if (!paginationData || (paginationData.total_pages ?? 1) <= 1) {
      pagination.hidden = true;
      return;
    }

    pagination.hidden = false;
    pageInfo.textContent = `Page ${paginationData.page} of ${paginationData.total_pages}`;
    prev.disabled = !paginationData.has_prev;
    next.disabled = !paginationData.has_next;
  };

  const renderTableError = () => {
    if (tbody) tbody.innerHTML = '<tr><td colspan="7" class="loading-cell">Unable to load bans right now</td></tr>';
    if (meta) meta.textContent = 'Records unavailable';
  };

  const loadBans = async () => {
    if (state.loading) return;
    state.loading = true;
    if (meta) meta.textContent = 'Loading records';

    const params = new URLSearchParams({ page: String(state.page) });
    if (state.search) params.set('search', state.search);

    try {
      const response = await fetch(`api/bans.php?${params.toString()}`, { cache: 'no-store' });
      const data = await response.json();

      if (!data.success) {
        renderTableError();
        return;
      }

      renderRows(data.bans);
      renderPagination(data.pagination);

      const total = data.pagination?.total ?? 0;
      const active = data.stats?.active_bans ?? total;
      if (meta) {
        meta.textContent = `${total} result${total === 1 ? '' : 's'} • ${active} active ban${active === 1 ? '' : 's'}`;
      }
    } catch (error) {
      renderTableError();
    } finally {
      state.loading = false;
    }
  };

  const renderBanDetail = (ban) => {
    const pay = ban.can_pay
      ? `<a href="${esc(ban.unban_url)}" target="_blank" rel="noopener">${esc(ban.price)} Unban Checkout</a>`
      : '';

    setModal(`<h2 id="modalTitle">${esc(ban.username)}</h2><p>${esc(ban.reason)}</p><div class="detail-grid">
      <div class="detail-item"><span>Appeal ID</span><strong>${esc(ban.appeal_id)}</strong></div>
      <div class="detail-item"><span>Status</span><strong>${esc(ban.status)} ${esc(ban.type)}</strong></div>
      <div class="detail-item"><span>Staff</span><strong>${esc(ban.staff)}</strong></div>
      <div class="detail-item"><span>Server</span><strong>${esc(ban.server)}</strong></div>
      <div class="detail-item"><span>Issued</span><strong>${esc(ban.date)}</strong></div>
      <div class="detail-item"><span>Expires</span><strong>${esc(ban.expires)}</strong></div>
      <div class="detail-item"><span>Flags</span><strong>${esc(ban.flags_text)}</strong></div>
      <div class="detail-item"><span>Support</span><strong>${esc(ban.support_email)}</strong></div>
    </div><div class="modal-actions"><a href="${esc(ban.discord)}" target="_blank" rel="noopener">Discord Support</a>${pay}</div>`);
  };

  const loadBanDetail = async (id) => {
    setModal('<h2 id="modalTitle">Loading record</h2><p>Fetching punishment details</p>');

    try {
      const response = await fetch(`api/bans.php?id=${encodeURIComponent(id)}`, { cache: 'no-store' });
      const data = await response.json();

      if (!data.success || !data.detail) {
        setModal('<h2 id="modalTitle">Record unavailable</h2><p>That active ban record could not be loaded right now</p>');
        return;
      }

      renderBanDetail(data.detail);
    } catch (error) {
      setModal('<h2 id="modalTitle">Record unavailable</h2><p>That active ban record could not be loaded right now</p>');
    }
  };

  modalClose?.addEventListener('click', closeModal);
  modal?.addEventListener('click', (event) => {
    if (event.target === modal) closeModal();
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && modal && !modal.hidden) closeModal();
  });

  document.querySelectorAll('[data-info]').forEach((button) => {
    button.addEventListener('click', () => {
      const item = infoText[button.dataset.info];
      if (item) renderInfoModal(item);
    });
  });

  copyBtn?.addEventListener('click', async () => {
    const ip = copyBtn.dataset.copyIp || app.dataset.serverIp || 'mineacle.net';

    try {
      await navigator.clipboard.writeText(ip);
    } catch (error) {
      const fallback = document.createElement('textarea');
      fallback.value = ip;
      fallback.setAttribute('readonly', 'readonly');
      fallback.style.position = 'fixed';
      fallback.style.left = '-9999px';
      document.body.appendChild(fallback);
      fallback.select();
      document.execCommand('copy');
      fallback.remove();
    }

    copyBtn.classList.add('copied');
    const copyText = $('copyIpMain');
    if (copyText) copyText.textContent = 'IP COPIED';

    clearTimeout(copyBtn._timer);
    copyBtn._timer = setTimeout(() => {
      copyBtn.classList.remove('copied');
      if (copyText) copyText.textContent = 'MINEACLE.NET';
    }, 1400);
  });

  form?.addEventListener('submit', (event) => {
    event.preventDefault();
    state.search = input ? input.value.trim() : '';
    state.page = 1;
    loadBans();
  });

  let searchTimer;
  input?.addEventListener('input', () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
      state.search = input.value.trim();
      state.page = 1;
      loadBans();
    }, 420);
  });

  prev?.addEventListener('click', () => {
    if (state.page > 1) {
      state.page -= 1;
      loadBans();
    }
  });

  next?.addEventListener('click', () => {
    state.page += 1;
    loadBans();
  });

  tbody?.addEventListener('click', (event) => {
    const button = event.target.closest('[data-ban-id]');
    if (!button) return;
    loadBanDetail(button.dataset.banId);
  });

  loadStatus();
  loadBans();
  setInterval(loadStatus, 30000);
})();
