/**
 * Create an element with attributes and children safely
 * @param {string} tag
 * @param {Record<string, any>} [props]
 * @param {(Node|string)[]=} children
 * @returns {HTMLElement}
 */
export function el(tag, props = {}, children = []) {
  const node = document.createElement(tag);
  for (const [key, val] of Object.entries(props)) {
    if (key === "class") node.className = String(val);
    else if (key === "dataset" && val && typeof val === "object") {
      for (const [dKey, dVal] of Object.entries(val)) node.dataset[dKey] = String(dVal);
    } else if (key.startsWith("on") && typeof val === "function") {
      node.addEventListener(key.slice(2), val, false);
    } else if (key === "text") {
      node.textContent = String(val);
    } else if (val !== undefined && val !== null) {
      node.setAttribute(key, String(val));
    }
  }
  for (const child of children) {
    if (typeof child === "string") node.appendChild(document.createTextNode(child));
    else if (child instanceof Node) node.appendChild(child);
  }
  return node;
}