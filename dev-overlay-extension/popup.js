const statusEl = document.querySelector("#status");
const toggleBtn = document.querySelector("#toggle");
const refreshBtn = document.querySelector("#refresh");
const clearBtn = document.querySelector("#clear");
const exportBtn = document.querySelector("#export");

let hudVisible = false;

const getActiveTabId = async () => {
  const [tab] = await chrome.tabs.query({ active: true, currentWindow: true });
  return tab?.id ?? null;
};

const sendToActive = async (payload, attempt = 0) => {
  const tabId = await getActiveTabId();
  if (tabId === null) throw new Error("No active tab");

  try {
    return await chrome.tabs.sendMessage(tabId, payload);
  } catch (err) {
    const message = err?.message || "";
    const needsInjection = /receiving end does not exist/i.test(message) && attempt === 0;

    if (needsInjection) {
      await chrome.scripting.executeScript({
        target: { tabId },
        files: ["content.js"]
      });
      return sendToActive(payload, attempt + 1);
    }

    throw err;
  }
};

const setStatus = (text) => {
  statusEl.textContent = text;
};

toggleBtn.addEventListener("click", async () => {
  try {
    const resp = await sendToActive({ type: "hud-toggle", visible: !hudVisible });
    hudVisible = resp?.visible ?? !hudVisible;
    toggleBtn.textContent = hudVisible ? "Hide HUD" : "Show HUD";
    setStatus(hudVisible ? "Overlay visible" : "Overlay hidden");
  } catch (err) {
    setStatus(err.message);
  }
});

refreshBtn.addEventListener("click", async () => {
  try {
    const resp = await sendToActive({ type: "hud-refresh" });
    const count = resp?.count ?? 0;
    setStatus(`Synced ${count} events`);
  } catch (err) {
    setStatus(err.message);
  }
});

clearBtn.addEventListener("click", async () => {
  try {
    const resp = await sendToActive({ type: "hud-clear" });
    const remaining = resp?.count ?? 0;
    setStatus(remaining ? `Cleared events (remaining ${remaining})` : "Cleared events");
  } catch (err) {
    setStatus(err.message);
  }
});

exportBtn.addEventListener("click", async () => {
  try {
    const resp = await sendToActive({ type: "hud-export" });
    if (!resp || !resp.dataUrl) {
      setStatus("Nothing to export");
      return;
    }
    const count = resp.count ?? "?";
    const link = document.createElement("a");
    link.href = resp.dataUrl;
    link.download = `mpsm-dev-overlay-${new Date().toISOString()}.ndjson`;
    document.body.appendChild(link);
    link.click();
    requestAnimationFrame(() => link.remove());
    setStatus(`Exported ${count} events`);
  } catch (err) {
    setStatus(err.message);
  }
});

(async () => {
  try {
    const resp = await sendToActive({ type: "hud-toggle", visible: false });
    hudVisible = resp?.visible ?? false;
    toggleBtn.textContent = hudVisible ? "Hide HUD" : "Show HUD";
    setStatus("Ready");
  } catch (err) {
    setStatus("Unable to reach tab (open sandbox site)");
  }
})();
