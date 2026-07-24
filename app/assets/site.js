(() => {
  'use strict';

  document.documentElement.classList.add('is-ready');

  const leaderboardPage = document.querySelector('.leaderboard-page');
  const searchForm = document.querySelector('[data-player-search-form]');
  const searchRoot = document.querySelector('[data-player-search]');
  const searchInput = document.getElementById('playerSearchInput');
  const searchResults = document.querySelector('[data-player-search-results]');
  const clearButton = searchForm ? searchForm.querySelector('.search-clear') : null;
  const searchDelayMs = 160;

  let searchTimer = 0;
  let searchController = null;
  let searchRun = 0;
  let leaderboardController = null;
  let leaderboardRun = 0;
  let currentLeaderboardUrl = `${window.location.pathname}${window.location.search}`;

  const setSearchExpanded = (expanded) => {
    if (searchInput) {
      searchInput.setAttribute('aria-expanded', expanded ? 'true' : 'false');
    }
  };

  const updateClearButton = () => {
    if (searchInput && clearButton) {
      clearButton.hidden = searchInput.value.trim() === '';
    }
  };

  const cancelSearch = () => {
    window.clearTimeout(searchTimer);
    searchRun += 1;

    if (searchController) {
      searchController.abort();
      searchController = null;
    }
  };

  const hideSearchResults = () => {
    cancelSearch();

    if (searchResults) {
      searchResults.hidden = true;
      searchResults.replaceChildren();
    }

    setSearchExpanded(false);
  };

  const searchIsEnabled = () => {
    return !searchForm || searchForm.dataset.playerSearchEnabled !== 'false';
  };

  const playerProfileUrl = (username) => {
    return `/player/${encodeURIComponent(username)}`;
  };

  const playerHeadUrl = (player) => {
    const skin = player && typeof player.skin === 'object' ? player.skin : null;
    return skin && typeof skin.head === 'string' ? skin.head.trim() : '';
  };

  const applyPunishmentClass = (row, player) => {
    const status = player && typeof player.punishment_status === 'object'
      ? player.punishment_status
      : null;
    const state = status && typeof status.search_state === 'string' ? status.search_state : '';

    if (state === 'permanent_ban') {
      row.classList.add('is-perm-banned');
    } else if (state === 'temporary_ban') {
      row.classList.add('is-temp-banned');
    } else if (state === 'permanent_mute' || state === 'temporary_mute') {
      row.classList.add('is-muted');
    }
  };

  const renderSearchResults = (players) => {
    if (!searchResults) {
      return;
    }

    searchResults.replaceChildren();

    if (!Array.isArray(players) || players.length === 0) {
      searchResults.hidden = true;
      setSearchExpanded(false);
      return;
    }

    const fragment = document.createDocumentFragment();

    players.slice(0, 8).forEach((player) => {
      const username = typeof player.name === 'string' ? player.name.trim() : '';

      if (username === '') {
        return;
      }

      const displayName = typeof player.display_name === 'string' && player.display_name.trim() !== ''
        ? player.display_name.trim()
        : username;
      const rankLabel = typeof player.rank_label === 'string' ? player.rank_label.trim() : '';
      const rankColor = typeof player.rank_color === 'string' && /^#[0-9a-f]{6}$/i.test(player.rank_color.trim())
        ? player.rank_color.trim()
        : '#bbbbbb';
      const statusLabel = typeof player.status_label === 'string' ? player.status_label.trim() : '';
      const statusLine = typeof player.status_line === 'string' ? player.status_line.trim() : '';
      const headUrl = playerHeadUrl(player);
      const row = document.createElement('a');
      const copy = document.createElement('span');
      const rankedName = document.createElement('span');
      const display = document.createElement('span');
      const action = document.createElement('span');

      row.className = `player-search-option ${player.online === true ? 'is-online-player' : 'is-offline-player'}`;
      row.href = playerProfileUrl(username);
      row.setAttribute('role', 'option');
      applyPunishmentClass(row, player);

      if (headUrl !== '') {
        const head = document.createElement('img');
        head.className = 'player-search-head';
        head.src = headUrl;
        head.alt = '';
        head.loading = 'lazy';
        head.decoding = 'async';
        head.draggable = false;
        head.setAttribute('aria-hidden', 'true');
        head.addEventListener('error', () => {
          row.classList.add('has-no-avatar');
          head.remove();
        }, { once: true });
        row.append(head);
      } else {
        row.classList.add('has-no-avatar');
      }

      rankedName.className = 'player-search-name ranked-player-name';
      rankedName.style.setProperty('--rank-color', rankColor);

      if (rankLabel === '+') {
        rankedName.classList.add('is-plus-rank');
      }

      if (rankLabel !== '') {
        const rank = document.createElement('span');
        rank.className = 'ranked-player-name__rank';
        rank.textContent = rankLabel;
        rankedName.append(rank);
      }

      display.className = 'ranked-player-name__name';
      display.textContent = displayName;
      rankedName.append(display);

      copy.className = 'player-search-copy';
      copy.append(rankedName);

      if (statusLabel !== '' || statusLine !== '') {
        const meta = document.createElement('small');
        meta.className = 'player-search-meta';
        meta.textContent = [statusLabel, statusLine].filter(Boolean).join(' · ');
        copy.append(meta);
      }

      action.className = 'player-search-action';
      action.textContent = 'View Stats';
      row.append(copy, action);
      fragment.append(row);
    });

    searchResults.append(fragment);
    searchResults.hidden = searchResults.children.length === 0;
    setSearchExpanded(!searchResults.hidden);
  };

  const fetchSearchResults = async (query) => {
    if (!searchResults || query === '' || !searchIsEnabled()) {
      hideSearchResults();
      return;
    }

    cancelSearch();

    const run = searchRun + 1;
    const controller = new AbortController();
    searchRun = run;
    searchController = controller;

    try {
      const response = await fetch(`/api/player-search.php?q=${encodeURIComponent(query)}&limit=8`, {
        headers: { Accept: 'application/json' },
        cache: 'no-store',
        credentials: 'same-origin',
        signal: controller.signal
      });

      if (!response.ok || run !== searchRun) {
        return;
      }

      const payload = await response.json();

      if (run !== searchRun || !searchInput || searchInput.value.trim() !== query) {
        return;
      }

      renderSearchResults(payload && payload.success ? payload.players : []);
    } catch (error) {
      if (!(error instanceof DOMException) || error.name !== 'AbortError') {
        hideSearchResults();
      }
    } finally {
      if (searchController === controller) {
        searchController = null;
      }
    }
  };

  const queueSearch = () => {
    if (!searchInput || !searchIsEnabled()) {
      hideSearchResults();
      return;
    }

    window.clearTimeout(searchTimer);
    const query = searchInput.value.trim();

    if (query === '') {
      hideSearchResults();
      return;
    }

    searchTimer = window.setTimeout(() => {
      fetchSearchResults(query);
    }, searchDelayMs);
  };

  const normalizeLeaderboardUrl = (value) => {
    const source = new URL(value, window.location.href);

    if (!/^\/(?:leaderboards(?:\.php)?|players(?:\.php)?)\/?$/i.test(source.pathname)) {
      return null;
    }

    const target = new URL('/leaderboards', window.location.origin);
    target.search = source.search;
    return target;
  };

  const leaderboardView = (root) => {
    const board = root.querySelector('#leaderboardRankings');

    return {
      board,
      topCard: root.querySelector('.leaderboard-top-card'),
      categoryGrid: root.querySelector('.leaderboard-category-grid'),
      heading: board ? board.querySelector('.leaderboard-section-heading') : null,
      filterRow: board ? board.querySelector('.leaderboard-view-row') : null,
      results: board ? board.querySelector('[data-leaderboard-results]') : null,
      categoryInput: board ? board.querySelector('[data-leaderboard-category-input]') : null,
      viewInput: board ? board.querySelector('[data-leaderboard-view-input]') : null,
      searchForm: root.querySelector('[data-player-search-form]'),
      searchInput: root.querySelector('#playerSearchInput'),
      searchLabel: board ? board.querySelector('label[for="playerSearchInput"]') : null
    };
  };

  const completeLeaderboardView = (view) => {
    return Object.values(view).every((node) => node instanceof Element);
  };

  const moveChildren = (current, next) => {
    const fragment = document.createDocumentFragment();

    while (next.firstChild) {
      fragment.append(next.firstChild);
    }

    current.replaceChildren(fragment);
  };

  const syncDocumentMetadata = (nextDocument) => {
    if (nextDocument.title) {
      document.title = nextDocument.title;
    }

    ['link[rel="canonical"]', 'meta[property="og:url"]', 'meta[property="og:title"]'].forEach((selector) => {
      const current = document.querySelector(selector);
      const next = nextDocument.querySelector(selector);

      if (!(current instanceof Element) || !(next instanceof Element)) {
        return;
      }

      if (current instanceof HTMLLinkElement && next instanceof HTMLLinkElement) {
        current.href = next.href;
      } else if (current instanceof HTMLMetaElement && next instanceof HTMLMetaElement) {
        current.content = next.content;
      }
    });
  };

  const setLeaderboardStatus = (message) => {
    const status = document.querySelector('[data-leaderboard-status]');

    if (status) {
      status.textContent = message;
    }
  };

  const loadLeaderboard = async (value, pushHistory = true) => {
    if (!leaderboardPage) {
      return false;
    }

    const target = normalizeLeaderboardUrl(value);
    const current = leaderboardView(document);

    if (!target || !completeLeaderboardView(current)) {
      return false;
    }

    if (leaderboardController) {
      leaderboardController.abort();
    }

    const run = leaderboardRun + 1;
    const controller = new AbortController();
    const scrollPosition = { x: window.scrollX, y: window.scrollY };
    leaderboardRun = run;
    leaderboardController = controller;
    current.board.setAttribute('aria-busy', 'true');
    setLeaderboardStatus('Updating leaderboard…');

    try {
      const response = await fetch(target.href, {
        headers: {
          Accept: 'text/html',
          'X-Requested-With': 'fetch'
        },
        cache: 'no-store',
        credentials: 'same-origin',
        signal: controller.signal
      });

      if (!response.ok) {
        setLeaderboardStatus('The leaderboard could not be updated. Please try again.');
        return false;
      }

      const nextDocument = new DOMParser().parseFromString(await response.text(), 'text/html');

      if (run !== leaderboardRun) {
        return null;
      }

      const next = leaderboardView(nextDocument);

      if (!completeLeaderboardView(next)) {
        setLeaderboardStatus('The leaderboard could not be updated. Please try again.');
        return false;
      }

      const focused = document.activeElement;
      const focusAttribute = focused instanceof HTMLAnchorElement
        ? ['data-leaderboard-category-link', 'data-leaderboard-view-link'].find((attribute) => focused.hasAttribute(attribute))
        : null;

      moveChildren(current.topCard, next.topCard);
      moveChildren(current.categoryGrid, next.categoryGrid);
      moveChildren(current.heading, next.heading);
      moveChildren(current.filterRow, next.filterRow);
      moveChildren(current.results, next.results);
      current.topCard.setAttribute('aria-label', next.topCard.getAttribute('aria-label') || 'Top leaderboard entries');
      current.board.setAttribute('aria-label', next.board.getAttribute('aria-label') || 'Leaderboard rankings');
      current.categoryInput.value = next.categoryInput.value;
      current.viewInput.value = next.viewInput.value;
      current.searchForm.dataset.playerSearchEnabled = next.searchForm.dataset.playerSearchEnabled || 'true';
      current.searchInput.placeholder = next.searchInput.placeholder;
      current.searchInput.value = next.searchInput.value;
      current.searchLabel.textContent = next.searchLabel.textContent;

      hideSearchResults();
      updateClearButton();
      syncDocumentMetadata(nextDocument);

      const relativeUrl = `${target.pathname}${target.search}`;

      if (pushHistory && relativeUrl !== currentLeaderboardUrl) {
        window.history.pushState({ leaderboard: true }, '', relativeUrl);
      }

      currentLeaderboardUrl = relativeUrl;
      setLeaderboardStatus('Leaderboard updated.');

      if (focusAttribute) {
        const nextFocus = document.querySelector(`[${focusAttribute}][aria-current="page"]`);

        if (nextFocus instanceof HTMLElement) {
          nextFocus.focus({ preventScroll: true });
        }
      }

      window.requestAnimationFrame(() => {
        window.scrollTo(scrollPosition.x, scrollPosition.y);
      });

      return true;
    } catch (error) {
      if (error instanceof DOMException && error.name === 'AbortError') {
        return null;
      }

      setLeaderboardStatus('The leaderboard could not be updated. Please try again.');
      return false;
    } finally {
      if (leaderboardController === controller) {
        leaderboardController = null;
      }

      if (run === leaderboardRun) {
        current.board.removeAttribute('aria-busy');
      }
    }
  };

  if (searchInput) {
    searchInput.addEventListener('input', () => {
      updateClearButton();
      queueSearch();
    });
    searchInput.addEventListener('focus', queueSearch);
    searchInput.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') {
        hideSearchResults();
      }
    });
  }

  if (clearButton && searchInput) {
    clearButton.addEventListener('click', () => {
      searchInput.value = '';
      updateClearButton();
      hideSearchResults();
      searchInput.focus();
    });
  }

  if (searchForm) {
    searchForm.addEventListener('submit', async (event) => {
      if (!searchInput) {
        return;
      }

      if (searchForm.dataset.playerSearchSubmit !== 'filter') {
        const query = searchInput.value.trim();

        if (query !== '') {
          event.preventDefault();
          window.location.assign(playerProfileUrl(query));
        }

        return;
      }

      if (!leaderboardPage) {
        return;
      }

      event.preventDefault();
      hideSearchResults();

      const target = new URL(searchForm.action, window.location.href);
      target.search = new URLSearchParams(new FormData(searchForm)).toString();
      await loadLeaderboard(target.href, true);
    });
  }

  document.addEventListener('click', async (event) => {
    if (!(event.target instanceof Element)) {
      return;
    }

    if (searchRoot && !searchRoot.contains(event.target)) {
      hideSearchResults();
    }

    if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
      return;
    }

    const link = event.target.closest('[data-leaderboard-category-link], [data-leaderboard-view-link], [data-leaderboard-page-link]');

    if (!(link instanceof HTMLAnchorElement) || link.target === '_blank') {
      return;
    }

    if (link.getAttribute('aria-disabled') === 'true' || link.getAttribute('aria-current') === 'page') {
      event.preventDefault();
      return;
    }

    const target = normalizeLeaderboardUrl(link.href);

    if (!target) {
      return;
    }

    event.preventDefault();
    hideSearchResults();
    await loadLeaderboard(target.href, true);
  });

  if (leaderboardPage) {
    if ('scrollRestoration' in window.history) {
      window.history.scrollRestoration = 'manual';
    }

    window.addEventListener('popstate', async () => {
      const loaded = await loadLeaderboard(window.location.href, false);

      if (loaded === false) {
        window.history.replaceState({ leaderboard: true }, '', currentLeaderboardUrl);
      }
    });
  }

  updateClearButton();
})();
