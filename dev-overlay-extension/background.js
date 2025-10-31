const EVENT_LIMIT = 500;
const sessions = new Map();

function getSession(tabId) {
  if (!sessions.has(tabId)) {
    sessions.set(tabId, []);
  }
  return sessions.get(tabId);
}

chrome.tabs.onRemoved.addListener((tabId) => {
  sessions.delete(tabId);
});

chrome.runtime.onMessage.addListener((message, sender, sendResponse) => {
  const { type, payload } = message || {};
  const tabId = sender.tab ? sender.tab.id : null;

  if (type === "capture-event" && tabId !== null) {
    const list = getSession(tabId);
    list.push(payload);
    if (list.length > EVENT_LIMIT) {
      list.splice(0, list.length - EVENT_LIMIT);
    }
    chrome.action.setBadgeText({
      tabId,
      text: String(list.length)
    });
    chrome.action.setBadgeBackgroundColor({
      tabId,
      color: "#1f6feb"
    });
    sendResponse({ ok: true });
    return true;
  }

  if (type === "fetch-events" && tabId !== null) {
    const list = filterEvents(getSession(tabId), message.filters, message.search);
    sendResponse({ events: list, count: list.length });
    return true;
  }

  if (type === "clear-events" && tabId !== null) {
    sessions.set(tabId, []);
    chrome.action.setBadgeText({ tabId, text: "" });
    sendResponse({ ok: true, count: 0 });
    return true;
  }

  if (type === "export-events" && tabId !== null) {
    const data = filterEvents(getSession(tabId), message.filters, message.search);
    const blob = data.map((evt) => JSON.stringify(evt)).join("\n");
    const base64 = toBase64(blob);
    sendResponse({
      mime: "application/x-ndjson",
      count: data.length,
      dataUrl: `data:application/x-ndjson;base64,${base64}`
    });
    return true;
  }

  return false;
});

function toBase64(text) {
  if (!text) {
    return "";
  }
  const encoder = new TextEncoder();
  const bytes = encoder.encode(text);
  let binary = "";
  const chunkSize = 0x8000;
  for (let i = 0; i < bytes.length; i += chunkSize) {
    const chunk = bytes.subarray(i, i + chunkSize);
    binary += String.fromCharCode(...chunk);
  }
  return btoa(binary);
}

function filterEvents(list, filters, search) {
  if (!Array.isArray(list) || !list.length) return [];
  const allow = Array.isArray(filters) && filters.length ? new Set(filters) : null;
  const needle = typeof search === "string" && search.length ? search.toLowerCase() : null;

  return list.filter((evt) => {
    if (allow && !allow.has(evt.category)) return false;
    if (!needle) return true;
    const text = `${evt.summary || ""} ${JSON.stringify(evt.detail || {})}`.toLowerCase();
    return text.includes(needle);
  });
}
