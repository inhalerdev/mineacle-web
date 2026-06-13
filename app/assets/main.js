
const toast = document.getElementById("toast");
const banList = document.getElementById("banList");
const banSearch = document.getElementById("banSearch");
const banCount = document.getElementById("banCount");
const banModal = document.getElementById("banModal");

function escapeHtml(value) {
  return String(value ?? "").replace(/[&<>"']/g, (char) => ({
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#039;"
  }[char]));
}

function showToast(title, subtitle) {
  if (!toast) return;

  toast.querySelector("strong").textContent = title;
  toast.querySelector("span").textContent = subtitle;
  toast.classList.remove("show");
  void toast.offsetWidth;
  toast.classList.add("show");
  clearTimeout(showToast.timer);
  showToast.timer = setTimeout(() => toast.classList.remove("show"), 3000);
}

function badgeClass(ban) {
  return ban.status_type || "active";
}

function actionButton(ban) {
  if (ban.ipban) {
    return `<button class="btn soft disabled" type="button" disabled><img src="assets/lock.svg" alt=""> No Action</button>`;
  }

  if (ban.can_pay) {
    return `<a class="btn red" href="${escapeHtml(ban.unban_url)}">${escapeHtml(ban.price)} Unban</a>`;
  }

  return `<button class="btn soft" type="button" data-info="${escapeHtml(ban.id)}">View</button>`;
}

function renderBans(rows) {
  if (!banList) return;

  if (!rows.length) {
    banList.innerHTML = `
      <div class="empty">
        <strong>No active bans found</strong>
        <span>Try another username or check again later</span>
      </div>
    `;
    if (banCount) banCount.textContent = "0 shown";
    return;
  }

  banList.innerHTML = rows.map(ban => `
    <article class="ban-row">
      <img class="ban-avatar" src="${escapeHtml(ban.skin)}" alt="${escapeHtml(ban.username)}">
      <div class="ban-player">
        <div class="ban-player-line">
          <span class="ban-name">${escapeHtml(ban.username)}</span>
          <button class="info-btn" type="button" data-info="${escapeHtml(ban.id)}" aria-label="View ${escapeHtml(ban.username)} ban info">
            <img src="assets/info.svg" alt="">
          </button>
        </div>
        <span class="ban-date">${escapeHtml(ban.date)}</span>
      </div>
      <div class="ban-reason">
        ${escapeHtml(ban.reason)}
        <div class="ban-meta">${escapeHtml(ban.type)} • ${escapeHtml(ban.duration)}</div>
      </div>
      <div class="ban-status">
        <span class="badge ${escapeHtml(badgeClass(ban))}">${ban.ipban ? '<img src="assets/lock.svg" alt="">' : ""}${escapeHtml(ban.status)}</span>
      </div>
      <div class="ban-action">${actionButton(ban)}</div>
    </article>
  `).join("");

  if (banCount) banCount.textContent = `${rows.length} shown`;

  document.querySelectorAll("[data-info]").forEach(button => {
    button.addEventListener("click", () => openBanInfo(button.dataset.info, rows));
  });
}

async function loadBans() {
  if (!banList) return;

  const search = banSearch ? banSearch.value.trim() : "";
  const url = `api/bans.php?search=${encodeURIComponent(search)}`;

  try {
    const response = await fetch(url, { headers: { "Accept": "application/json" } });
    const payload = await response.json();

    if (!payload.success) {
      banList.innerHTML = `<div class="error">${escapeHtml(payload.error || "Unable to load bans")}</div>`;
      if (banCount) banCount.textContent = "0 shown";
      return;
    }

    window.mineacleBans = payload.bans || [];
    renderBans(window.mineacleBans);
  } catch (error) {
    banList.innerHTML = `<div class="error">Unable to load bans right now</div>`;
    if (banCount) banCount.textContent = "0 shown";
  }
}

function openBanInfo(id, rows = window.mineacleBans || []) {
  const ban = rows.find(row => String(row.id) === String(id));
  if (!ban || !banModal) return;

  document.getElementById("modalAvatar").src = ban.skin;
  document.getElementById("modalName").textContent = ban.username;
  document.getElementById("modalStatus").className = `badge ${badgeClass(ban)}`;
  document.getElementById("modalStatus").innerHTML = `${ban.ipban ? '<img src="assets/lock.svg" alt="">' : ""}${escapeHtml(ban.status)}`;

  document.getElementById("modalReason").textContent = ban.reason;
  document.getElementById("modalType").textContent = ban.type;
  document.getElementById("modalDuration").textContent = ban.duration;
  document.getElementById("modalDate").textContent = ban.date;
  document.getElementById("modalAppeal").textContent = ban.appeal_id;
  document.getElementById("modalEmail").textContent = ban.support_email;
  document.getElementById("modalDiscord").textContent = ban.discord;

  const actions = document.getElementById("modalActions");
  const note = document.getElementById("modalNote");

  if (ban.ipban) {
    actions.innerHTML = `<button class="btn soft disabled" disabled><img src="assets/lock.svg" alt=""> Permanent IP Ban</button>`;
    note.textContent = "This is an IP ban. It is marked permanently banned and has no public dispute or paid-unban option.";
  } else if (ban.can_pay) {
    actions.innerHTML = `
      <a class="btn red" href="${escapeHtml(ban.unban_url)}">${escapeHtml(ban.price)} Pay to be unbanned</a>
      <a class="btn soft" href="${escapeHtml(ban.discord)}" target="_blank" rel="noopener">Contact Discord</a>
    `;
    note.textContent = "Use the payment option for eligible bans, or contact support if you believe this punishment is incorrect.";
  } else {
    actions.innerHTML = `<a class="btn soft" href="${escapeHtml(ban.discord)}" target="_blank" rel="noopener">Contact Support</a>`;
    note.textContent = "This punishment is not currently eligible for paid unban. Contact support if you need more information.";
  }

  banModal.classList.add("show");
}

function closeModal() {
  if (banModal) banModal.classList.remove("show");
}

document.querySelectorAll(".copy-ip").forEach(button => {
  button.addEventListener("click", async () => {
    const value = button.dataset.copy || "mineacle.net";
    try {
      await navigator.clipboard.writeText(value);
      showToast("Server IP copied", value);
    } catch {
      showToast("Copy server IP", value);
    }
  });
});

if (banSearch) {
  let timer = null;
  banSearch.addEventListener("input", () => {
    clearTimeout(timer);
    timer = setTimeout(loadBans, 180);
  });
}

document.querySelectorAll("[data-close-modal]").forEach(button => {
  button.addEventListener("click", closeModal);
});

document.addEventListener("keydown", event => {
  if (event.key === "Escape") closeModal();
});

if (banModal) {
  banModal.addEventListener("click", event => {
    if (event.target === banModal) closeModal();
  });
}


loadBans();
