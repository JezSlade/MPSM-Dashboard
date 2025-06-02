<?php
// core/tracking.php
// v1.0.0 [Event tracking → MPS API]

require_once __DIR__ . '/mps_api.php';
require_once __DIR__ . '/debug.php';

function track_event(string $type, array $details = []): void {
    try {
        fetch_mps_api('Tracking/LogEvent', [
            'eventType' => $type,
            'details'   => $details
        ]);
        debug_log("Tracked event: $type", $details, 'INFO');
    } catch (Exception $e) {
        debug_log('Failed to track event', ['event'=>$type,'error'=>$e->getMessage()], 'ERROR');
    }
}
