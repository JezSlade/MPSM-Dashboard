<?php
// includes/card_base.php
// -------------------------------------------------------------------
// Base bootstrap for all cards: load environment variables, define
// constants, set up error reporting, and include common helpers.
// -------------------------------------------------------------------

declare(strict_types=1);

// ──────────── ENV BOOTSTRAP ────────────
require_once __DIR__ . '/env_parser.php';        // brings in parse_env_file()
parse_env_file(__DIR__ . '/../.env');            // now DEALER_CODE, API_BASE_URL, etc. are defined

// ────────── DEBUG & ERROR REPORTING ────────
error_reporting(E_ALL);
ini_set('display_errors', '1');

// ────────── COMMON HELPERS ──────────
// These are available to every card via this base include:
require_once __DIR__ . '/auth.php';              // get_token(), etc.
require_once __DIR__ . '/api_client.php';        // api_request()
require_once __DIR__ . '/logger.php';            // log_request(), etc.
// … any other shared helpers …

// End of card_base.php
