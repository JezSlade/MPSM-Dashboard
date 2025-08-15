/**
 * Minimal sanitizer placeholder.
 * For now we avoid innerHTML and prefer text nodes.
 * Keep here to allow future HTML-capable widgets through a vetted sanitizer.
 */
export function sanitizeText(value) {
  return String(value ?? "");
}