(() => {
  'use strict';

  const fallbackImages = document.querySelectorAll('img[data-fallback-src]');

  fallbackImages.forEach((image) => {
    image.addEventListener('error', () => {
      const fallback = image.dataset.fallbackSrc;

      if (!fallback || image.dataset.fallbackUsed === 'true') {
        return;
      }

      image.dataset.fallbackUsed = 'true';
      image.src = fallback;
    }, { once: true });
  });

  const onlineTarget = document.querySelector('[data-profile-online]');

  if (onlineTarget) {
    const controller = new AbortController();
    const timeout = window.setTimeout(() => controller.abort(), 4500);

    fetch('/api/server-status.php', {
      method: 'GET',
      headers: { Accept: 'application/json' },
      cache: 'no-store',
      signal: controller.signal,
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error(`Server status returned ${response.status}`);
        }

        return response.json();
      })
      .then((payload) => {
        const online = Number.isFinite(Number(payload.players_online))
          ? Math.max(0, Number(payload.players_online))
          : 0;
        const max = Number.isFinite(Number(payload.players_max))
          ? Math.max(0, Number(payload.players_max))
          : 0;

        onlineTarget.textContent = max > 0
          ? `${online.toLocaleString()} / ${max.toLocaleString()}`
          : online.toLocaleString();
      })
      .catch(() => {
        onlineTarget.textContent = 'Unavailable';
      })
      .finally(() => window.clearTimeout(timeout));
  }

  const copyButton = document.querySelector('[data-copy-ip]');
  const toast = document.querySelector('[data-profile-toast]');
  let toastTimer = 0;

  const showToast = (message) => {
    if (!toast) {
      return;
    }

    window.clearTimeout(toastTimer);
    toast.textContent = message;
    toast.hidden = false;
    toastTimer = window.setTimeout(() => {
      toast.hidden = true;
    }, 2400);
  };

  const copyText = async (text) => {
    if (navigator.clipboard && window.isSecureContext) {
      await navigator.clipboard.writeText(text);
      return;
    }

    const input = document.createElement('textarea');
    input.value = text;
    input.setAttribute('readonly', '');
    input.style.position = 'fixed';
    input.style.opacity = '0';
    document.body.appendChild(input);
    input.select();

    const copied = document.execCommand('copy');
    input.remove();

    if (!copied) {
      throw new Error('Copy command failed');
    }
  };

  if (copyButton) {
    copyButton.addEventListener('click', async () => {
      const serverIp = copyButton.dataset.copyIp || copyButton.textContent.trim();

      try {
        await copyText(serverIp);
        showToast('Server IP copied');
      } catch {
        showToast(`Copy ${serverIp}`);
      }
    });
  }
})();
