<?php declare(strict_types=1);
/**
 * Lightweight debug helper.
 * When DEBUG_MODE=true in .env, messages go to error_log().
 * Otherwise the function is a silent no-op.
 */
function appendDebug(string $msg): void
{
    if (getenv('DEBUG_MODE') === 'true') {
        error_log('[DBG] ' . $msg);
    }
}
