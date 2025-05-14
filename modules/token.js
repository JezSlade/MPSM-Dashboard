// v1.0.0 [Init: Token Fetch + Store + Event Emit]
import { eventBus } from '../core/event-bus.js';
import { store } from '../core/store.js';

export async function loadToken() {
  try {
    const res = await fetch('./get_token.php');
    const data = await res.json();

    if (data.access_token) {
      store.set("token", data.access_token);
      eventBus.emit("token:loaded", { token: data.access_token });
    } else {
      throw new Error(data.error || "No token returned");
    }
  } catch (err) {
    console.error("Token fetch error", err);
    window.DebugPanel?.logError("Token fetch failed", err);
  }
}
