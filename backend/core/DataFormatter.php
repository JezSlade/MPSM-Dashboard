<?php
/**
 * DataFormatter class
 * Used to transform raw API data into CMS-ready format.
 */

class DataFormatter
{
    /**
     * Flattens and simplifies response for CMS tables.
     * @param array $raw
     * @return array
     */
    public static function format(array $raw): array
    {
        // Sample transformation logic, to be customized per endpoint
        return array_map(function($item) {
            return [
                'id' => $item['id'] ?? null,
                'name' => $item['name'] ?? 'Unnamed',
                'status' => $item['status'] ?? 'Unknown'
            ];
        }, $raw);
    }
}
