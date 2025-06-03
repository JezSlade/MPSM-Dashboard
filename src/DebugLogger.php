<?php
// src/DebugLogger.php

class DebugLogger {
    const LOGFILE = __DIR__ . '/../storage/debug.log';
    public static function log(string $message) {
        $time = date('Y-m-d H:i:s');
        error_log("[$time] $message\n", 3, self::LOGFILE);
    }
}
