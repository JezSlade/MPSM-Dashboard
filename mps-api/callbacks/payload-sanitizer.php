<?php
declare(strict_types=1);

/**
 * Payload Sanitizer Helpers
 *
 * Normalizes incoming webhook payloads so that multi-line strings,
 * uncommon Unicode separators, and invalid UTF-8 bytes do not break
 * `json_decode()`. Also logs the sanitized snippet whenever an
 * `Invalid JSON` error is raised so we can inspect the cleaned payload.
 */

if (!function_exists('sanitizeRawPayload')) {
    /**
     * Normalize the raw payload before decoding.
     */
    function sanitizeRawPayload(string $rawBody): string
    {
        if ($rawBody === '') {
            return $rawBody;
        }

        // Normalize line endings and drop BOM if present
        $normalized = str_replace(["\r\n", "\r"], "\n", $rawBody);
        $normalized = preg_replace('/^\x{FEFF}/u', '', $normalized) ?? $normalized;

        // Force valid UTF-8 (silently drop invalid byte sequences)
        $normalized = @iconv('UTF-8', 'UTF-8//IGNORE', $normalized) ?: $normalized;

        // Escape control characters and known Unicode line separators
        $escaped = preg_replace_callback(
            '/[\x00-\x1F\x7F\x{0085}\x{2028}\x{2029}]/u',
            static function ($matches): string {
                $codePoint = getPayloadCodepoint($matches[0]);
                return match ($codePoint) {
                    0x09 => '\\t',
                    0x0A => '\\n',
                    0x0D => '\\r',
                    default => sprintf('\\u%04x', $codePoint),
                };
            },
            $normalized
        );

        return restoreWhitespaceOutsideStrings($escaped);
    }
}

if (!function_exists('getPayloadCodepoint')) {
    /**
     * Determine the Unicode codepoint for a UTF-8 character.
     */
    function getPayloadCodepoint(string $char): int
    {
        if (function_exists('mb_ord')) {
            $code = @mb_ord($char, 'UTF-8');
            if ($code !== false) {
                return $code;
            }
        }

        if (class_exists('IntlChar') && method_exists(\IntlChar::class, 'ord')) {
            $code = \IntlChar::ord($char);
            if ($code !== false) {
                return $code;
            }
        }

        // Fallback: convert to UCS-4BE and unpack
        $encoded = @iconv('UTF-8', 'UCS-4BE', $char);
        if ($encoded !== false) {
            $unpacked = unpack('N', $encoded);
            if (isset($unpacked[1])) {
                return $unpacked[1];
            }
        }

        $bytes = unpack('C*', $char);
        return array_reduce($bytes, static fn($carry, $byte) => ($carry << 8) + $byte, 0);
    }
}

if (!function_exists('logInvalidJsonPayloadSample')) {
    /**
     * Append sanitized payload metadata to a dedicated log for offline analysis.
     */
    function logInvalidJsonPayloadSample(int $debugLogId, string $rawBody, string $sanitizedBody, \JsonException $exception): void
    {
        static $logPath;

        if ($logPath === null) {
            $logDir = dirname(__DIR__) . '/logs';
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            $logPath = $logDir . '/panel-message-invalid-json.log';
        }

        $payloadSnippet = [
            'timestamp' => date('Y-m-d H:i:s'),
            'debug_id' => $debugLogId,
            'error' => $exception->getMessage(),
            'raw' => substr($rawBody, 0, 1024),
            'sanitized' => substr($sanitizedBody, 0, 1024),
        ];

        file_put_contents($logPath, json_encode($payloadSnippet, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}

if (!function_exists('restoreWhitespaceOutsideStrings')) {
    /**
     * Convert escaped whitespace back to literal whitespace outside JSON strings.
     */
    function restoreWhitespaceOutsideStrings(string $text): string
    {
        $out = '';
        $inString = false;
        $escaped = false;
        $length = strlen($text);

        for ($i = 0; $i < $length; $i++) {
            $char = $text[$i];

            if ($escaped) {
                $out .= $char;
                $escaped = false;
                continue;
            }

            if ($char === '\\') {
                if ($inString) {
                    $out .= $char;
                    $escaped = true;
                    continue;
                }

                $next = $text[$i + 1] ?? '';
                if ($next === 'n') {
                    $out .= "\n";
                    $i++;
                    continue;
                }
                if ($next === 'r') {
                    $out .= "\r";
                    $i++;
                    continue;
                }
                if ($next === 't') {
                    $out .= "\t";
                    $i++;
                    continue;
                }

                $out .= $char;
                continue;
            }

            if ($char === '"') {
                $out .= $char;
                $inString = !$inString;
                continue;
            }

            $out .= $char;
        }

        return $out;
    }
}

/*
CHANGELOG
2025-11-11 Codex
- Added a helper that converts escaped whitespace back to literal spaces outside JSON strings so the decoder stays compliant.
- Added a dedicated payload sanitizer that logs sanitized snippets for the remaining invalid JSON cases.
*/
