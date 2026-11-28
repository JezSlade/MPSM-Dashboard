<?php
/**
 * Device toner level history for the Toner Alerts drill-down.
 */
require '../config.php';
require '../functions.php';

requireAuth();

$deviceId = $_GET['deviceId'] ?? '';
$dealerCode = $_GET['dealerCode'] ?? DEFAULT_DEALER_CODE;
$range = $_GET['range'] ?? '90';
$ranges = ['30' => 30, '90' => 90, '365' => 365, 'all' => null];

if (!is_string($deviceId) || trim($deviceId) === '' || strlen($deviceId) > 200
    || !is_string($dealerCode) || trim($dealerCode) === '' || strlen($dealerCode) > 200
    || !is_string($range) || !array_key_exists($range, $ranges)) {
    jsonError('Invalid device, dealership, or history range.', 400);
}

$userId = $_SESSION['user_id'];
session_write_close();
$utc = new DateTimeZone('UTC');
$to = new DateTimeImmutable('now', $utc);
$from = $range === 'all'
    ? new DateTimeImmutable('2000-01-01', $utc)
    : $to->modify('-' . $ranges[$range] . ' days');

try {
    $response = queryMpsApi('Device/ListLevelsDataHistory', [
        'DeviceId' => trim($deviceId),
        'FromDate' => $from->format(DATE_ATOM),
        'ToDate' => $to->format(DATE_ATOM),
        'ShowPhotoconductor' => false,
    ], $userId, trim($dealerCode));
    $data = $response['data'] ?? null;
    $rows = is_array($data) && array_key_exists('Result', $data) ? ($data['Result'] ?? []) : $data;
    if (!is_array($rows) || !array_is_list($rows)) {
        throw new UnexpectedValueException('Unexpected toner history response');
    }

    $points = [];
    foreach ($rows as $row) {
        $date = $row['DateUTC'] ?? '';
        if (!is_string($date) || !preg_match('/^\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|[+-]\d{2}:?\d{2})?$/i', $date)) {
            continue;
        }
        try {
            $timestamp = new DateTimeImmutable($date, $utc);
        } catch (Exception $invalidDate) {
            continue;
        }
        if (DateTimeImmutable::getLastErrors() || $timestamp < $from || $timestamp > $to) {
            continue;
        }

        $at = $timestamp->setTimezone($utc)->format('Y-m-d\TH:i:s.v\Z');
        $point = $points[$at] ?? ['at' => $at, 'black' => null, 'cyan' => null, 'magenta' => null, 'yellow' => null];
        foreach (['black', 'cyan', 'magenta', 'yellow'] as $color) {
            $level = $row[ucfirst($color)] ?? null;
            if (is_numeric($level) && $level >= 0 && $level <= 100) {
                $point[$color] = (float)$level;
            }
        }
        $points[$at] = $point;
    }
    ksort($points, SORT_STRING);
    jsonSuccess(['readings' => array_values($points)]);
} catch (Throwable $error) {
    error_log('Toner history request failed: ' . $error->getMessage());
    jsonError('MPS Monitor could not provide toner history for this device.', 502);
}

/*
CHANGELOG
2026-11-28 Codex
- Proxy date-range toner readings through the selected dealer profile and preserve missing levels.
*/
