(() => {
  if (window.__MPSM_DEV_OVERLAY__) {
    chrome.runtime.sendMessage({ type: "hud-refresh" }, () => {});
    return;
  }
  window.__MPSM_DEV_OVERLAY__ = true;

  const EVENT_CAPACITY = 1000;
  const TEXT_LIMIT = 4000;
  const STACK_IGNORE = ["content.js", "runtime.js", "extensions::"];
  const FILTER_ORDER = [
    { key: "network", label: "Network" },
    { key: "ws", label: "WebSocket" },
    { key: "beacon", label: "Beacon" },
    { key: "ui", label: "User Actions" },
    { key: "dom", label: "DOM" },
    { key: "storage", label: "Storage" },
    { key: "console", label: "Console" },
    { key: "navigation", label: "Navigation" },
    { key: "state", label: "State" },
    { key: "custom", label: "Custom" }
  ];

  const state = {
    events: [],
    filters: new Set(FILTER_ORDER.map((f) => f.key)),
    search: "",
    capturing: true,
    autoFollow: true,
    highlightMode: false,
    selectedId: null,
    marqueeInterval: null,
    metrics: {
      network: 0,
      errors: 0,
      dom: 0,
      ws: 0,
      beacon: 0,
      ui: 0
    }
  };

  const ui = {
    root: null,
    shadow: null,
    marquee: null,
    timeline: null,
    inspectorTitle: null,
    inspectorView: null,
    stats: null,
    filterBar: null,
    searchInput: null,
    highlightOverlay: null,
    highlightTooltip: null
  };

  let eventCounter = 1;
  const indicatorRegistry = new Map();
  let liveLayer = null;
  let liveLayerEventsBound = false;

  function ensureGlobalStyles() {
    if (document.getElementById("mpsm-overlay-global-style")) return;
    const globalStyle = document.createElement("style");
    globalStyle.id = "mpsm-overlay-global-style";
    globalStyle.textContent = `
      .mpsm-live-layer {
        position: fixed;
        inset: 0;
        pointer-events: none;
        z-index: 2147483646;
      }
      .mpsm-live-badge {
        position: absolute;
        background: rgba(31, 111, 235, 0.92);
        color: #ffffff;
        padding: 2px 6px;
        border-radius: 4px;
        font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, monospace;
        font-size: 11px;
        box-shadow: 0 4px 18px rgba(1, 4, 9, 0.35);
        transform: translate(-50%, -120%);
        white-space: nowrap;
        transition: opacity 0.2s ease;
        opacity: 1;
      }
      .mpsm-live-badge[data-category="ui"] {
        background: rgba(34, 197, 94, 0.9);
      }
      .mpsm-live-badge[data-category="dom"] {
        background: rgba(59, 130, 246, 0.9);
      }
      .mpsm-live-badge[data-category="storage"] {
        background: rgba(250, 204, 21, 0.9);
        color: #0d1117;
      }
      .mpsm-live-badge[data-category="console"] {
        background: rgba(248, 113, 113, 0.92);
      }
      .mpsm-live-badge[data-category="state"] {
        background: rgba(236, 72, 153, 0.92);
      }
      .mpsm-live-badge[data-category="navigation"] {
        background: rgba(249, 115, 22, 0.92);
      }
    `;
    document.head.appendChild(globalStyle);
  }

  function ensureLiveLayer() {
    ensureGlobalStyles();
    if (!liveLayer || !liveLayer.isConnected) {
      liveLayer = document.createElement("div");
      liveLayer.className = "mpsm-live-layer";
      document.body.appendChild(liveLayer);
    }
    if (!liveLayerEventsBound) {
      liveLayerEventsBound = true;
      const rebound = () => updateIndicatorPositions();
      window.addEventListener("scroll", rebound, true);
      window.addEventListener("resize", rebound);
    }
    return liveLayer;
  }

  function updateIndicatorPosition(target, node) {
    if (!target || !node) return;
    if (!document.contains(target)) {
      node.remove();
      indicatorRegistry.delete(target);
      return;
    }
    const rect = target.getBoundingClientRect();
    if (rect.width === 0 && rect.height === 0) {
      node.style.display = "none";
      return;
    }
    node.style.display = "block";
    const top = rect.top + window.scrollY;
    const left = rect.left + window.scrollX + rect.width / 2;
    node.style.top = `${top}px`;
    node.style.left = `${left}px`;
  }

  function updateIndicatorPositions() {
    indicatorRegistry.forEach((entry, target) => {
      if (!document.contains(target)) {
        entry.node.remove();
        indicatorRegistry.delete(target);
        return;
      }
      updateIndicatorPosition(target, entry.node);
    });
  }

  function showLiveBadge(selector, label, category) {
    if (!selector) return;
    ensureLiveLayer();
    let target;
    try {
      target = document.querySelector(selector);
    } catch (error) {
      return;
    }
    if (!target || !document.contains(target)) return;
    if (ui.root && ui.root.contains(target)) return;

    const cleanLabel = (label || "event").slice(0, 80);
    let entry = indicatorRegistry.get(target);
    if (!entry) {
      const node = document.createElement("div");
      node.className = "mpsm-live-badge";
      ensureLiveLayer().appendChild(node);
      entry = { node, timer: null };
      indicatorRegistry.set(target, entry);
    }

    entry.node.dataset.category = category || "";
    entry.node.textContent = cleanLabel;
    updateIndicatorPosition(target, entry.node);
    entry.node.style.opacity = "1";

    if (entry.timer) clearTimeout(entry.timer);
    entry.timer = window.setTimeout(() => {
      entry.node.style.opacity = "0";
    }, 2500);
  }

  function safeSlice(text, limit = TEXT_LIMIT) {
    if (typeof text !== "string") return text;
    return text.length > limit ? text.slice(0, limit) + " ...[truncated]" : text;
  }

  function toPlain(obj) {
    try {
      return JSON.parse(JSON.stringify(obj));
    } catch (error) {
      return String(obj);
    }
  }

  function buildSelector(node) {
    if (!node || !(node instanceof Element)) return "";
    const id = node.id ? `#${node.id}` : "";
    const className = node.className && typeof node.className === "string"
      ? "." + node.className.trim().split(/\s+/).join(".")
      : "";
    const name = node.getAttribute("name");
    const nameAttr = name ? `[name="${name}"]` : "";
    return `${node.tagName.toLowerCase()}${id}${className}${nameAttr}`;
  }

  function captureStack() {
    const stack = new Error().stack || "";
    return stack
      .split("\n")
      .slice(2)
      .filter((line) => !STACK_IGNORE.some((needle) => line.includes(needle)))
      .join("\n");
  }

  function sendToBackground(event) {
    try {
      chrome.runtime.sendMessage({ type: "capture-event", payload: event }, () => {});
    } catch (error) {
      // ignored
    }
  }

  function updateMetrics(event) {
    if (event.type === "network") state.metrics.network += 1;
    if (event.type === "console" && event.detail?.level === "error") state.metrics.errors += 1;
    if (event.type === "dom") state.metrics.dom += 1;
    if (event.type === "ws") state.metrics.ws += 1;
    if (event.type === "beacon") state.metrics.beacon += 1;
    if (event.type === "ui") state.metrics.ui += 1;
  }

  function summarizeEvent(event) {
    const time = new Date(event.ts).toLocaleTimeString();
    const summary = event.summary || event.label || event.type;
    return `[${time}] ${summary}`;
  }

  function renderMarquee() {
    if (!ui.marquee) return;
    if (!state.events.length) {
      ui.marquee.textContent = "MPSM Dev Overlay initialized.";
      return;
    }
    const recent = state.events.slice(-5).map(summarizeEvent).join(" | ");
    ui.marquee.textContent = recent;
  }

  function renderStats() {
    if (!ui.stats) return;
    ui.stats.innerHTML = `
      <div class="hud-stat">
        <span class="hud-stat__label">Events</span>
        <span class="hud-stat__value">${state.events.length}</span>
      </div>
      <div class="hud-stat">
        <span class="hud-stat__label">Network</span>
        <span class="hud-stat__value">${state.metrics.network}</span>
      </div>
      <div class="hud-stat">
        <span class="hud-stat__label">Warnings</span>
        <span class="hud-stat__value">${state.metrics.errors}</span>
      </div>
      <div class="hud-stat">
        <span class="hud-stat__label">DOM</span>
        <span class="hud-stat__value">${state.metrics.dom}</span>
      </div>
      <div class="hud-stat">
        <span class="hud-stat__label">WebSocket</span>
        <span class="hud-stat__value">${state.metrics.ws}</span>
      </div>
      <div class="hud-stat">
        <span class="hud-stat__label">Beacon</span>
        <span class="hud-stat__value">${state.metrics.beacon}</span>
      </div>
      <div class="hud-stat">
        <span class="hud-stat__label">UI</span>
        <span class="hud-stat__value">${state.metrics.ui}</span>
      </div>
    `;
  }

  function renderFilters() {
    if (!ui.filterBar) return;
    ui.filterBar.innerHTML = "";
    FILTER_ORDER.forEach(({ key, label }) => {
      const chip = document.createElement("button");
      chip.type = "button";
      chip.className = "hud-chip";
      chip.dataset.active = state.filters.has(key) ? "true" : "false";
      chip.textContent = label;
      chip.addEventListener("click", () => {
        if (state.filters.has(key)) {
          state.filters.delete(key);
        } else {
          state.filters.add(key);
        }
        chip.dataset.active = state.filters.has(key) ? "true" : "false";
        renderTimeline();
      });
      ui.filterBar.appendChild(chip);
    });
  }

  function filteredEvents() {
    return state.events.filter((evt) => {
      if (!state.filters.has(evt.category)) return false;
      if (!state.search) return true;
      const text = `${evt.summary || ""} ${JSON.stringify(evt.detail || {})}`.toLowerCase();
      return text.includes(state.search);
    });
  }

  function renderTimeline() {
    if (!ui.timeline) return;
    const events = filteredEvents().slice().reverse();
    const fragment = document.createDocumentFragment();

    if (!events.length) {
      const empty = document.createElement("div");
      empty.className = "hud-empty";
      empty.textContent = state.events.length
        ? "No events match the active filters."
        : "Waiting for activity...";
      fragment.appendChild(empty);
    } else {
      events.forEach((evt) => {
        const item = document.createElement("div");
        item.className = `hud-timeline-item hud-${evt.category}`;
        item.dataset.id = String(evt.id);
        if (evt.id === state.selectedId) {
          item.classList.add("is-selected");
        }
        item.innerHTML = `
          <div class="hud-timeline-item__title">${evt.summary || evt.type}</div>
          <div class="hud-timeline-item__meta">
            <span>${new Date(evt.ts).toLocaleTimeString()}</span>
            <span>${evt.detail?.method || evt.detail?.stage || ""}</span>
            <span>${evt.detail?.status ?? ""}</span>
          </div>
        `;
        item.addEventListener("click", () => selectEvent(evt.id));
        fragment.appendChild(item);
      });
    }
    ui.timeline.innerHTML = "";
    ui.timeline.appendChild(fragment);
  }

  function buildInspectorSections(evt) {
    const sections = [];
    const basic = JSON.stringify({
      id: evt.id,
      time: new Date(evt.ts).toISOString(),
      type: evt.type,
      category: evt.category,
      summary: evt.summary
    }, null, 2);

    sections.push({ title: "Overview", content: basic });

    if (evt.detail && Object.keys(evt.detail).length) {
      sections.push({ title: "Detail", content: JSON.stringify(evt.detail, null, 2) });
    }

    if (evt.responseBody) {
      sections.push({ title: "Response", content: evt.responseBody });
    }

    if (evt.requestBody) {
      sections.push({ title: "Request", content: evt.requestBody });
    }

    if (evt.stack) {
      sections.push({ title: "Stack", content: evt.stack });
    }

    if (evt.context && Object.keys(evt.context).length) {
      sections.push({ title: "Context", content: JSON.stringify(evt.context, null, 2) });
    }

    return sections;
  }

  function renderInspector() {
    if (!ui.inspectorTitle || !ui.inspectorView) return;
    const evt = state.events.find((item) => item.id === state.selectedId);

    if (!evt) {
      ui.inspectorTitle.textContent = "Select an event";
      ui.inspectorView.innerHTML = `<div class="hud-empty">Choose an event from the timeline to inspect details.</div>`;
      return;
    }

    ui.inspectorTitle.textContent = evt.summary || evt.type;
    const sections = buildInspectorSections(evt);
    const fragment = document.createDocumentFragment();

    sections.forEach((section) => {
      const wrapper = document.createElement("section");
      wrapper.className = "hud-section";

      const header = document.createElement("header");
      header.className = "hud-section__header";
      header.innerHTML = `
        <span>${section.title}</span>
        <div class="hud-section__actions">
          <button type="button" data-action="copy">Copy</button>
          <button type="button" data-action="minimize">Collapse</button>
        </div>
      `;

      const body = document.createElement("pre");
      body.className = "hud-section__body";
      body.textContent = section.content;

      header.addEventListener("click", (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) return;
        const action = target.dataset.action;
        if (action === "copy") {
          navigator.clipboard?.writeText(section.content).catch(() => {});
          flashStatus("Copied to clipboard");
        }
        if (action === "minimize") {
          body.classList.toggle("is-collapsed");
          target.textContent = body.classList.contains("is-collapsed") ? "Expand" : "Collapse";
        }
      });

      wrapper.appendChild(header);
      wrapper.appendChild(body);
      fragment.appendChild(wrapper);
    });

    ui.inspectorView.innerHTML = "";
    ui.inspectorView.appendChild(fragment);
  }

  function flashStatus(message) {
    if (!ui.root) return;
    let toast = ui.shadow.getElementById("mpsm-hud-toast");
    if (!toast) {
      toast = document.createElement("div");
      toast.id = "mpsm-hud-toast";
      toast.className = "hud-toast";
      ui.shadow.appendChild(toast);
    }
    toast.textContent = message;
    toast.dataset.visible = "true";
    setTimeout(() => {
      toast.dataset.visible = "false";
    }, 1500);
  }

  function selectEvent(id) {
    state.selectedId = id;
    renderTimeline();
    renderInspector();
  }

  function addEvent(raw) {
    if (!state.capturing && raw.category !== "custom") {
      return;
    }
    const highlightSelector = raw.context?.selector || raw.detail?.selector;
    const event = {
      id: eventCounter++,
      ts: Date.now(),
      ...raw
    };
    state.events.push(event);
    if (state.events.length > EVENT_CAPACITY) {
      state.events.shift();
    }
    if (!state.selectedId || (state.autoFollow && raw.category !== "custom")) {
      state.selectedId = event.id;
    }

    updateMetrics(event);
    if (highlightSelector) {
      showLiveBadge(highlightSelector, event.summary || event.type, event.category);
    }
    renderTimeline();
    renderInspector();
    renderStats();
    renderMarquee();
    sendToBackground(event);
  }

  function createHUD() {
    const host = document.createElement("div");
    host.id = "mpsm-dev-overlay-root";
    document.documentElement.appendChild(host);

    const shadow = host.attachShadow({ mode: "open" });
    ui.root = host;
    ui.shadow = shadow;

    const style = document.createElement("style");
    style.textContent = `
      :host, :root {
        all: initial;
      }
      .hud-wrapper {
        position: fixed;
        inset: 0;
        display: flex;
        flex-direction: column;
        font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, monospace;
        font-size: 12px;
        color: #e6edf3;
        background: rgba(13, 17, 23, 0.86);
        border: 1px solid #1f242d;
        border-radius: 0;
        box-shadow: none;
        z-index: 2147483647;
        overflow: hidden;
        pointer-events: none;
      }
      .hud-header {
        padding: 10px 12px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        border-bottom: 1px solid #1f242d;
        background: rgba(22, 27, 34, 0.95);
        pointer-events: auto;
      }
      .hud-title-row {
        display: flex;
        align-items: center;
        gap: 8px;
      }
      .hud-title {
        font-weight: 600;
        font-size: 14px;
        flex: 1;
      }
      .hud-marquee {
        font-size: 11px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        color: #8b949e;
        opacity: 0.85;
      }
      .hud-controls {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
      }
      .hud-btn {
        background: #21262d;
        border: 1px solid #30363d;
        color: inherit;
        padding: 4px 8px;
        border-radius: 6px;
        cursor: pointer;
      }
      .hud-btn.is-active {
        background: #1f6feb;
        border-color: #1f6feb;
      }
      .hud-body {
        flex: 1;
        display: grid;
        grid-template-columns: 0.32fr 0.68fr;
        gap: 0;
        overflow: hidden;
        pointer-events: auto;
      }
      .hud-timeline {
        border-right: 1px solid #1f242d;
        display: flex;
        flex-direction: column;
      }
      .hud-filter-bar {
        padding: 8px;
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        border-bottom: 1px solid #1f242d;
      }
      .hud-chip {
        background: #151b23;
        border: 1px solid #30363d;
        color: inherit;
        padding: 3px 8px;
        border-radius: 999px;
        font-size: 11px;
        cursor: pointer;
      }
      .hud-chip[data-active="false"] {
        opacity: 0.35;
      }
      .hud-search {
        padding: 6px 8px;
        border-bottom: 1px solid #1f242d;
      }
      .hud-search input {
        width: 100%;
        border: 1px solid #30363d;
        background: #0d1117;
        color: inherit;
        border-radius: 6px;
        padding: 4px 6px;
      }
      .hud-timeline-scroll {
        flex: 1;
        overflow: auto;
        display: flex;
        flex-direction: column;
        gap: 4px;
        padding: 8px;
      }
      .hud-timeline-item {
        background: #0d1117;
        border: 1px solid #1f242d;
        border-radius: 8px;
        padding: 6px;
        cursor: pointer;
        transition: border-color 0.15s ease;
      }
      .hud-timeline-item:hover {
        border-color: #1f6feb;
      }
      .hud-timeline-item.is-selected {
        border-color: #1f6feb;
        box-shadow: 0 0 0 1px rgba(31, 111, 235, 0.4);
      }
      .hud-timeline-item__title {
        font-weight: 500;
        margin-bottom: 2px;
      }
      .hud-timeline-item__meta {
        display: flex;
        gap: 8px;
        color: #6e7681;
        font-size: 11px;
        flex-wrap: wrap;
      }
      .hud-empty {
        margin: auto;
        padding: 16px;
        color: #6e7681;
        text-align: center;
      }
      .hud-inspector {
        display: flex;
        flex-direction: column;
        overflow: hidden;
      }
      .hud-inspector-header {
        padding: 8px 12px;
        border-bottom: 1px solid #1f242d;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
      }
      .hud-inspector-header span {
        font-weight: 500;
        color: #f0f6fc;
      }
      .hud-inspector-view {
        flex: 1;
        overflow: auto;
        padding: 8px 10px 12px;
        display: flex;
        flex-direction: column;
        gap: 8px;
      }
      .hud-section {
        border: 1px solid #1f242d;
        border-radius: 8px;
        background: #0d1117;
      }
      .hud-section__header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 6px 8px;
        border-bottom: 1px solid #1f242d;
      }
      .hud-section__header span {
        font-weight: 500;
      }
      .hud-section__actions {
        display: flex;
        gap: 4px;
      }
      .hud-section__actions button {
        background: #161b22;
        border: 1px solid #30363d;
        color: inherit;
        padding: 2px 6px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 11px;
      }
      .hud-section__body {
        max-height: 160px;
        overflow: auto;
        padding: 8px;
        margin: 0;
        white-space: pre-wrap;
        background: transparent;
      }
      .hud-section__body.is-collapsed {
        max-height: 0;
        padding: 0 8px;
        overflow: hidden;
      }
      .hud-footer {
        border-top: 1px solid #1f242d;
        padding: 6px 8px;
        display: flex;
        gap: 12px;
        background: rgba(22, 27, 34, 0.95);
        pointer-events: auto;
      }
      .hud-stat {
        display: flex;
        flex-direction: column;
        gap: 2px;
        font-size: 11px;
      }
      .hud-stat__label {
        color: #8b949e;
      }
      .hud-stat__value {
        font-weight: 600;
      }
      .hud-toast {
        position: fixed;
        right: 16px;
        bottom: 16px;
        background: rgba(31, 111, 235, 0.95);
        color: white;
        padding: 10px 12px;
        border-radius: 8px;
        opacity: 0;
        transform: translateY(8px);
        transition: opacity 0.2s ease, transform 0.2s ease;
        pointer-events: none;
      }
      .hud-toast[data-visible="true"] {
        opacity: 1;
        transform: translateY(0);
      }
      .hud-highlight-box {
        position: fixed;
        border: 2px solid #1f6feb;
        background: rgba(31, 111, 235, 0.15);
        pointer-events: none;
        z-index: 2147483646;
        display: none;
      }
      .hud-highlight-tooltip {
        position: fixed;
        background: rgba(13, 17, 23, 0.95);
        border: 1px solid #1f242d;
        border-radius: 6px;
        padding: 4px 6px;
        font-size: 11px;
        pointer-events: none;
        z-index: 2147483646;
        display: none;
      }
      .hud-wrapper[data-minimized="true"] {
        height: auto;
        min-height: 52px;
      }
      .hud-wrapper[data-minimized="true"] .hud-body,
      .hud-wrapper[data-minimized="true"] .hud-footer {
        display: none;
      }
    `;

    const wrapper = document.createElement("div");
    wrapper.className = "hud-wrapper";
    wrapper.innerHTML = `
      <header class="hud-header">
        <div class="hud-title-row">
          <div class="hud-title">MPSM Dev Overlay</div>
          <button type="button" class="hud-btn" data-action="minimize">Minimize</button>
          <button type="button" class="hud-btn" data-action="close">Hide</button>
        </div>
        <div class="hud-marquee">Initializing overlay...</div>
        <div class="hud-controls">
          <button type="button" class="hud-btn is-active" data-action="capture">Capture</button>
          <button type="button" class="hud-btn is-active" data-action="follow">Auto Follow</button>
          <button type="button" class="hud-btn" data-action="highlight">Highlight</button>
          <button type="button" class="hud-btn" data-action="clear">Clear</button>
          <button type="button" class="hud-btn" data-action="export">Export (Filters)</button>
          <button type="button" class="hud-btn" data-action="export-all">Export All</button>
        </div>
      </header>
      <div class="hud-body">
        <section class="hud-timeline">
          <div class="hud-filter-bar"></div>
          <div class="hud-search">
            <input type="search" placeholder="Filter events..." />
          </div>
          <div class="hud-timeline-scroll"></div>
        </section>
        <section class="hud-inspector">
          <header class="hud-inspector-header">
            <span>Inspector</span>
            <div class="hud-controls">
              <button type="button" class="hud-btn" data-action="copy-selection">Copy Selection</button>
              <button type="button" class="hud-btn" data-action="bookmark">Bookmark</button>
            </div>
          </header>
          <div class="hud-inspector-view"></div>
        </section>
      </div>
      <footer class="hud-footer"></footer>
    `;

    shadow.appendChild(style);
    shadow.appendChild(wrapper);

    ui.marquee = wrapper.querySelector(".hud-marquee");
    ui.timeline = wrapper.querySelector(".hud-timeline-scroll");
    ui.inspectorTitle = wrapper.querySelector(".hud-inspector-header span");
    ui.inspectorView = wrapper.querySelector(".hud-inspector-view");
    ui.stats = wrapper.querySelector(".hud-footer");
    ui.filterBar = wrapper.querySelector(".hud-filter-bar");
    ui.searchInput = wrapper.querySelector(".hud-search input");

    const highlightBox = document.createElement("div");
    highlightBox.className = "hud-highlight-box";
    shadow.appendChild(highlightBox);
    ui.highlightOverlay = highlightBox;

    const highlightTooltip = document.createElement("div");
    highlightTooltip.className = "hud-highlight-tooltip";
    shadow.appendChild(highlightTooltip);
    ui.highlightTooltip = highlightTooltip;

    ensureLiveLayer();

    wrapper.querySelectorAll(".hud-btn").forEach((button) => {
      button.addEventListener("click", handleControlAction);
    });
    ui.searchInput.addEventListener("input", (event) => {
      state.search = (event.target.value || "").toLowerCase();
      renderTimeline();
    });

    renderFilters();
    renderStats();
    renderMarquee();
  }

  function toggleHighlightMode(forceValue) {
    const next = typeof forceValue === "boolean" ? forceValue : !state.highlightMode;
    state.highlightMode = next;
    flashStatus(next ? "Highlight mode enabled" : "Highlight mode disabled");
    if (next) {
      document.addEventListener("mousemove", updateHighlightOverlay, true);
      document.addEventListener("click", pickHighlightedElement, true);
    } else {
      document.removeEventListener("mousemove", updateHighlightOverlay, true);
      document.removeEventListener("click", pickHighlightedElement, true);
      hideHighlightOverlay();
    }
  }

  function updateHighlightOverlay(event) {
    const target = event.target;
    if (!(target instanceof Element) || ui.highlightOverlay === null) return;
    const rect = target.getBoundingClientRect();

    ui.highlightOverlay.style.display = "block";
    ui.highlightOverlay.style.top = `${rect.top + window.scrollY}px`;
    ui.highlightOverlay.style.left = `${rect.left + window.scrollX}px`;
    ui.highlightOverlay.style.width = `${rect.width}px`;
    ui.highlightOverlay.style.height = `${rect.height}px`;

    if (ui.highlightTooltip) {
      ui.highlightTooltip.style.display = "block";
      ui.highlightTooltip.style.top = `${rect.top + window.scrollY - 28}px`;
      ui.highlightTooltip.style.left = `${rect.left + window.scrollX}px`;
      ui.highlightTooltip.textContent = buildSelector(target);
    }
  }

  function hideHighlightOverlay() {
    if (ui.highlightOverlay) {
      ui.highlightOverlay.style.display = "none";
    }
    if (ui.highlightTooltip) {
      ui.highlightTooltip.style.display = "none";
    }
  }

  function pickHighlightedElement(event) {
    const target = event.target;
    if (!(target instanceof Element)) return;
    event.preventDefault();
    event.stopPropagation();
    toggleHighlightMode(false);

    addEvent({
      type: "custom",
      category: "custom",
      summary: `Highlighted ${buildSelector(target)}`,
      detail: {
        html: safeSlice(target.outerHTML, 1500),
        text: safeSlice(target.textContent || "", 500),
        dimensions: target.getBoundingClientRect().toJSON()
      }
    });
  }

  function handleControlAction(event) {
    const button = event.currentTarget;
    if (!(button instanceof HTMLElement)) return;
    const action = button.dataset.action;
    if (!action || !ui.root) return;
    const wrapper = ui.shadow.querySelector(".hud-wrapper");

    switch (action) {
      case "minimize":
        if (wrapper) {
          const minimized = wrapper.dataset.minimized === "true";
          wrapper.dataset.minimized = minimized ? "false" : "true";
          button.textContent = minimized ? "Minimize" : "Restore";
        }
        break;
      case "close":
        ui.root.style.display = "none";
        flashStatus("Overlay hidden (use extension popup to reopen).");
        break;
      case "capture":
        state.capturing = !state.capturing;
        button.classList.toggle("is-active", state.capturing);
        flashStatus(state.capturing ? "Capture resumed" : "Capture paused");
        break;
      case "follow":
        state.autoFollow = !state.autoFollow;
        button.classList.toggle("is-active", state.autoFollow);
        break;
      case "highlight":
        toggleHighlightMode();
        button.classList.toggle("is-active", state.highlightMode);
        break;
      case "clear":
        state.events = [];
        state.metrics = { network: 0, errors: 0, dom: 0, ws: 0, beacon: 0, ui: 0 };
        state.selectedId = null;
        renderTimeline();
        renderInspector();
        renderStats();
        flashStatus("Events cleared");
        chrome.runtime.sendMessage({ type: "clear-events" }, () => {});
        break;
      case "export":
        exportEvents("filtered");
        break;
      case "export-all":
        exportEvents("all");
        break;
      case "copy-selection":
        copySelectedEvent();
        break;
      case "bookmark":
        bookmarkSelection();
        break;
      default:
        break;
    }
  }

  function copySelectedEvent() {
    const evt = state.events.find((item) => item.id === state.selectedId);
    if (!evt) {
      flashStatus("No event selected");
      return;
    }
    const text = JSON.stringify(evt, null, 2);
    navigator.clipboard?.writeText(text).then(() => {
      flashStatus("Selection copied");
    }).catch(() => {
      flashStatus("Copy failed");
    });
  }

  function bookmarkSelection() {
    const evt = state.events.find((item) => item.id === state.selectedId);
    if (!evt) {
      flashStatus("Nothing to bookmark");
      return;
    }
    addEvent({
      type: "custom",
      category: "custom",
      summary: `Bookmark: ${evt.summary || evt.type}`,
      detail: { anchoredId: evt.id }
    });
  }

  function exportEvents(scope = "filtered") {
    const filters = scope === "filtered" ? Array.from(state.filters) : null;
    const search = scope === "filtered" ? state.search : "";
    chrome.runtime.sendMessage({
      type: "export-events",
      filters,
      search
    }, (resp) => {
      if (!resp || !resp.dataUrl) {
        flashStatus("Export failed");
        return;
      }
      const link = document.createElement("a");
      link.href = resp.dataUrl;
      link.download = `mpsm-dev-overlay-${new Date().toISOString()}.ndjson`;
      document.body.appendChild(link);
      link.click();
      requestAnimationFrame(() => link.remove());
      const scopeLabel = scope === "filtered" ? "matching" : "total";
      flashStatus(`Exported ${resp.count ?? 0} ${scopeLabel} events`);
    });
  }

  function wrapFetch() {
    if (typeof window.fetch !== "function") return;
    const originalFetch = window.fetch.bind(window);
    window.fetch = async (...args) => {
      const [input, init] = args;
      const method = (init && init.method) || (input && input.method) || "GET";
      const url = typeof input === "string" ? input : input?.url || "";
      const started = performance.now();
      const context = captureInteractionContext();
      let requestBody = null;
      if (init && init.body) {
        requestBody = typeof init.body === "string" ? safeSlice(init.body) : "[non-string body]";
      }

      try {
        const response = await originalFetch(...args);
        const cloned = response.clone();
        cloned.text().then((body) => {
          const trimmed = safeSlice(body);
          addEvent({
            type: "network",
            category: "network",
            summary: `${method} ${url.split("?")[0]} (${response.status})`,
            detail: {
              transport: "fetch",
              method,
              url,
              status: response.status,
              durationMs: Math.round(performance.now() - started),
              headers: Object.fromEntries(response.headers.entries())
            },
            context,
            requestBody,
            responseBody: trimmed,
            stack: captureStack()
          });
          captureStateSnapshot("After fetch response");
        }).catch(() => {});
        return response;
      } catch (error) {
        addEvent({
          type: "network",
          category: "network",
          summary: `${method} ${url} failed`,
          detail: {
            transport: "fetch",
            method,
            url,
            error: String(error)
          },
          context,
          requestBody,
          stack: captureStack()
        });
        throw error;
      }
    };
  }

  function wrapXHR() {
    const NativeXHR = window.XMLHttpRequest;
    function WrappedXHR() {
      const xhr = new NativeXHR();
      let context = captureInteractionContext();
      let meta = { method: "GET", url: "", started: 0 };
      let requestBody = null;

      const open = xhr.open;
      xhr.open = function (method, url, ...rest) {
        meta = { method, url, started: performance.now() };
        context = captureInteractionContext();
        return open.call(this, method, url, ...rest);
      };

      const send = xhr.send;
      xhr.send = function (body) {
        requestBody = typeof body === "string" ? safeSlice(body) : body ? "[non-string body]" : null;
        this.addEventListener("loadend", function () {
          addEvent({
            type: "network",
            category: "network",
            summary: `${meta.method} ${meta.url.split("?")[0]} (${this.status})`,
            detail: {
              transport: "xhr",
              method: meta.method,
              url: meta.url,
              status: this.status,
              durationMs: Math.round(performance.now() - meta.started),
              responseSnippet: safeSlice(this.responseText || "")
            },
            context,
            requestBody,
            stack: captureStack()
          });
          captureStateSnapshot("After XHR response");
        });
        this.addEventListener("error", function () {
          addEvent({
            type: "network",
            category: "network",
            summary: `${meta.method} ${meta.url} failed`,
            detail: {
              transport: "xhr",
              method: meta.method,
              url: meta.url,
              error: "Network error"
            },
            context,
            requestBody,
            stack: captureStack()
          });
        });
        return send.call(this, body);
      };
      return xhr;
    }
    WrappedXHR.OPENED = NativeXHR.OPENED;
    WrappedXHR.HEADERS_RECEIVED = NativeXHR.HEADERS_RECEIVED;
    WrappedXHR.LOADING = NativeXHR.LOADING;
    WrappedXHR.DONE = NativeXHR.DONE;
    window.XMLHttpRequest = WrappedXHR;
  }

  function wrapBeacon() {
    if (typeof navigator.sendBeacon !== "function") return;
    const nativeBeacon = navigator.sendBeacon.bind(navigator);
    navigator.sendBeacon = function (url, data) {
      const context = captureInteractionContext();
      const result = nativeBeacon(url, data);
      addEvent({
        type: "beacon",
        category: "beacon",
        summary: `navigator.sendBeacon ${url}`,
        detail: {
          url,
          success: result,
          bodySnippet: typeof data === "string" ? safeSlice(data) : data ? "[binary]" : null
        },
        context,
        stack: captureStack()
      });
      return result;
    };
  }

  function wrapWebSocket() {
    if (typeof window.WebSocket !== "function") return;
    const NativeWebSocket = window.WebSocket;
    window.WebSocket = class OverlayWebSocket extends NativeWebSocket {
      constructor(url, protocols) {
        super(url, protocols);
        const context = captureInteractionContext();
        addEvent({
          type: "ws",
          category: "ws",
          summary: `WebSocket init ${url}`,
          detail: {
            stage: "init",
            url,
            protocols
          },
          context,
          stack: captureStack()
        });
        this.addEventListener("open", () => {
          addEvent({
            type: "ws",
            category: "ws",
            summary: `WebSocket open ${url}`,
            detail: { stage: "open", url },
            context,
            stack: captureStack()
          });
        });
        this.addEventListener("message", (event) => {
          addEvent({
            type: "ws",
            category: "ws",
            summary: `WebSocket message ${url}`,
            detail: {
              stage: "message",
              url,
              preview: typeof event.data === "string" ? safeSlice(event.data, 2000) : "[binary]"
            },
            stack: captureStack()
          });
        });
        this.addEventListener("error", () => {
          addEvent({
            type: "ws",
            category: "ws",
            summary: `WebSocket error ${url}`,
            detail: { stage: "error", url },
            stack: captureStack()
          });
        });
        this.addEventListener("close", (event) => {
          addEvent({
            type: "ws",
            category: "ws",
            summary: `WebSocket close ${url}`,
            detail: {
              stage: "close",
              url,
              code: event.code,
              reason: event.reason,
              clean: event.wasClean
            },
            stack: captureStack()
          });
        });
      }

      send(data) {
        addEvent({
          type: "ws",
          category: "ws",
          summary: "WebSocket send",
          detail: {
            stage: "send",
            preview: typeof data === "string" ? safeSlice(data, 2000) : "[binary]"
          },
          stack: captureStack()
        });
        return super.send(data);
      }
    };
  }

  function captureConsole() {
    ["log", "info", "warn", "error"].forEach((level) => {
      const original = console[level].bind(console);
      console[level] = (...args) => {
        try {
          addEvent({
            type: "console",
            category: "console",
            summary: `console.${level}`,
            detail: {
              level,
              arguments: args.map((item) => (typeof item === "string" ? item : toPlain(item)))
            },
            stack: captureStack()
          });
        } catch (error) {
          // ignored
        }
        return original(...args);
      };
    });
    window.addEventListener("error", (event) => {
      addEvent({
        type: "console",
        category: "console",
        summary: "Uncaught error",
        detail: {
          level: "error",
          message: event.message,
          location: `${event.filename}:${event.lineno}:${event.colno}`
        },
        stack: event.error?.stack || captureStack()
      });
    });
    window.addEventListener("unhandledrejection", (event) => {
      addEvent({
        type: "console",
        category: "console",
        summary: "Unhandled promise rejection",
        detail: {
          level: "error",
          reason: typeof event.reason === "string" ? event.reason : toPlain(event.reason)
        },
        stack: captureStack()
      });
    });
  }

  function captureDomMutations() {
    const observer = new MutationObserver((mutations) => {
      const interesting = mutations.slice(0, 20);
      if (!interesting.length) return;
      interesting.forEach((mutation) => {
        if (mutation.target instanceof Element && ui.root && ui.root.contains(mutation.target)) {
          return;
        }
        if (mutation.type === "attributes" && mutation.target instanceof Element) {
          addEvent({
            type: "dom",
            category: "dom",
            summary: `Attribute ${mutation.attributeName} changed`,
            detail: {
              selector: buildSelector(mutation.target),
              attribute: mutation.attributeName,
              value: mutation.target.getAttribute(mutation.attributeName)
            }
          });
        }
        if (mutation.type === "childList") {
          const added = Array.from(mutation.addedNodes).filter((node) => node instanceof Element && (!ui.root || !ui.root.contains(node))).length;
          const removed = Array.from(mutation.removedNodes).filter((node) => node instanceof Element && (!ui.root || !ui.root.contains(node))).length;
          if (added || removed) {
            addEvent({
              type: "dom",
              category: "dom",
              summary: `DOM update (added ${added}, removed ${removed})`,
              detail: {
                selector: mutation.target instanceof Element ? buildSelector(mutation.target) : "",
                added,
                removed
              }
            });
          }
        }
      });
    });

    const startObserver = () => {
      if (!document.body) return;
      observer.observe(document.body, {
        childList: true,
        attributes: true,
        subtree: true,
        attributeFilter: ["class", "style", "data-state", "aria-hidden"]
      });
    };

    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", startObserver, { once: true });
    } else {
      startObserver();
    }
  }

  function captureStorage() {
    ["setItem", "removeItem", "clear"].forEach((method) => {
      const original = Storage.prototype[method];
      Storage.prototype[method] = function (key, value) {
        const scope = this === window.sessionStorage ? "session" : "local";
        const previous = method === "clear" ? null : this.getItem(key);
        const result = original.apply(this, arguments);
        addEvent({
          type: "storage",
          category: "storage",
          summary: `${scope}Storage.${method}`,
          detail: {
            scope,
            method,
            key: method === "clear" ? null : key,
            previous,
            next: method === "clear" ? null : value
          }
        });
        return result;
      };
    });
  }

  function captureNavigation() {
    ["pushState", "replaceState"].forEach((method) => {
      const original = history[method];
      history[method] = function (...args) {
        const result = original.apply(this, args);
        addEvent({
          type: "navigation",
          category: "navigation",
          summary: `history.${method}`,
          detail: {
            url: document.location.href,
            arguments: args
          }
        });
        return result;
      };
    });

    window.addEventListener("popstate", () => {
      addEvent({
        type: "navigation",
        category: "navigation",
        summary: "popstate",
        detail: { url: document.location.href }
      });
    });

    window.addEventListener("hashchange", () => {
      addEvent({
        type: "navigation",
        category: "navigation",
        summary: "hashchange",
        detail: { url: document.location.href }
      });
    });
  }

  function captureInteractions() {
    document.addEventListener("click", (event) => {
      const target = event.target;
      if (!(target instanceof Element)) return;
      if ((ui.root && ui.root.contains(target)) || target.classList.contains("mpsm-live-badge")) return;
      addEvent({
        type: "ui",
        category: "ui",
        summary: `Click ${buildSelector(target)}`,
        detail: {
          button: event.button,
          altKey: event.altKey,
          ctrlKey: event.ctrlKey,
          shiftKey: event.shiftKey
        },
        context: captureInteractionContext({ target })
      });
    }, true);

    document.addEventListener("input", throttle((event) => {
      const target = event.target;
      if (!(target instanceof HTMLInputElement || target instanceof HTMLTextAreaElement)) return;
      if (ui.root && ui.root.contains(target)) return;
      addEvent({
        type: "ui",
        category: "ui",
        summary: `Input ${buildSelector(target)}`,
        detail: {
          value: safeSlice(target.value, 300)
        }
      });
    }, 500), true);
  }

  let lastInteraction = null;

  function captureInteractionContext(extra = {}) {
    const now = Date.now();
    if (extra.target && extra.target instanceof Element) {
      lastInteraction = {
        ts: now,
        selector: buildSelector(extra.target),
        text: extra.target.textContent?.trim().slice(0, 120)
      };
    }
    if (lastInteraction && now - lastInteraction.ts < 4000) {
      return { ...lastInteraction };
    }
    return extra.target && extra.target instanceof Element
      ? { selector: buildSelector(extra.target) }
      : {};
  }

  function captureStateSnapshot(label) {
    const snapshot = {};
    try {
      if (window.MPSM && typeof window.MPSM === "object") {
        const keys = ["state", "endpointCatalog", "devices", "alerts"];
        keys.forEach((key) => {
          if (key in window.MPSM) {
            snapshot[key] = toPlain(window.MPSM[key]);
          }
        });
      }
    } catch (error) {
      // ignored
    }
    if (Object.keys(snapshot).length) {
      addEvent({
        type: "state",
        category: "state",
        summary: label,
        detail: snapshot
      });
    }
  }

  function throttle(fn, delay) {
    let timer = null;
    return function throttled(...args) {
      if (timer) return;
      timer = setTimeout(() => {
        timer = null;
      }, delay);
      return fn.apply(this, args);
    };
  }

  function handleMessages() {
    chrome.runtime.onMessage.addListener((message, sender, sendResponse) => {
      if (!message || typeof message !== "object") return;
      if (message.type === "hud-toggle") {
        const wrapper = ui.shadow?.querySelector(".hud-wrapper");
        const visible = typeof message.visible === "boolean" ? message.visible : wrapper?.style.display !== "none";
        if (wrapper) {
          wrapper.style.display = visible ? "flex" : "none";
        }
        sendResponse({ visible });
        return true;
      }
      if (message.type === "hud-refresh") {
        chrome.runtime.sendMessage({ type: "fetch-events", filters: Array.from(state.filters), search: state.search }, (resp) => {
          if (resp?.events) {
            state.events = resp.events.map((evt) => ({ ...evt }));
            state.selectedId = state.events.length ? state.events[state.events.length - 1].id : null;
            renderTimeline();
            renderInspector();
            renderStats();
            renderMarquee();
            sendResponse({ count: state.events.length });
          }
        });
        return true;
      }
      if (message.type === "hud-clear") {
        state.events = [];
        state.metrics = { network: 0, errors: 0, dom: 0, ws: 0, beacon: 0, ui: 0 };
        state.selectedId = null;
        renderTimeline();
        renderInspector();
        renderStats();
        sendResponse({ count: 0 });
        return true;
      }
      if (message.type === "hud-export") {
        exportEvents();
        sendResponse({ ok: true });
        return true;
      }
      return false;
    });
  }

  function bootstrap() {
    createHUD();
    ensureLiveLayer();
    handleMessages();
    wrapFetch();
    wrapXHR();
    wrapBeacon();
    wrapWebSocket();
    captureConsole();
    captureDomMutations();
    captureStorage();
    captureNavigation();
    captureInteractions();
    captureStateSnapshot("Overlay started");
    addEvent({
      type: "custom",
      category: "custom",
      summary: "Overlay initialized",
      detail: {
        url: window.location.href,
        userAgent: navigator.userAgent
      }
    });
  }

  bootstrap();
})();
