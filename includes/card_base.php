<?php
// includes/card_base.php
// -------------------------------------------------------------------
// Base bootstrap for all cards: load environment variables, define
// constants, set up error reporting, and include common helpers.
// -------------------------------------------------------------------

declare(strict_types=1);

// ──────────── ENV BOOTSTRAP ────────────
// Your env_parser.php already reads and defines all .env keys on include:
require_once __DIR__ . '/env_parser.php';        // now loads .env, defines CLIENT_ID, DEALER_CODE, etc.

// ────────── DEBUG & ERROR REPORTING ────────
error_reporting(E_ALL);
ini_set('display_errors', '1');

// ────────── COMMON HELPERS ──────────
require_once __DIR__ . '/auth.php';              // get_token(), etc.
require_once __DIR__ . '/api_client.php';        // api_request()
require_once __DIR__ . '/logger.php';            // log_request(), etc.
// … any other shared helpers …

// End of card_base.php
