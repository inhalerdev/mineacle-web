(() => {
  'use strict';

  const statusEl = document.getElementById('navPlayersOnline');
  const valueEl = document.getElementById('navPlayersOnlineValue');

  if (!statusEl || !valueEl) {
    return;
  }

  const endpoint = statusEl.getAttribute('data-status-url') || 'api/server-status.php';

  const setStatus = (count, state) => {
    statusEl.classList.remove('is-live', 'is-offline', 'is-unknown');

    if (Number.isFinite(count) && count >= 0) {
      valueEl.textContent = count.toLocaleString();
      statusEl.classList.add(count > 0 ? 'is-live' : 'is-offline');
      return;
    }

    valueEl.textContent = '0';
    statusEl.classList.add(state || 'is-unknown');
  };

  const readCount = (payload) => {
    if (!payload || typeof payload !== 'object') {
      return null;
    }

    const direct = payload.players_online ?? payload.online_players ?? payload.online;
    const nested = payload.players && typeof payload.players === 'object'
      ? (payload.players.online ?? payload.players.now)
      : null;

    const value = direct ?? nested;
    const count = Number(value);

    return Number.isFinite(count) ? count : null;
  };

  const refresh = async () => {
    try {
      const response = await fetch(endpoint, {
        method: 'GET',
        cache: 'no-store',
        headers: {
          'Accept': 'application/json'
        }
      });

      if (!response.ok) {
        setStatus(null, 'is-unknown');
        return;
      }

      const payload = await response.json();
      const count = readCount(payload);

      if (count !== null) {
        setStatus(count);
        return;
      }

      if (payload && payload.online === false) {
        setStatus(0, 'is-offline');
        return;
      }

      setStatus(null, 'is-unknown');
    } catch (_error) {
      setStatus(null, 'is-unknown');
    }
  };

  refresh();
  window.setInterval(refresh, 60000);
})();
