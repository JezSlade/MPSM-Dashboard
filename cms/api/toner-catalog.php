<?php
/**
 * Exact OEM toner catalog lookup for the Toner Alerts device drill-down.
 */

if (!function_exists('tonerCatalogNormalizeToken')) {
    function tonerCatalogNormalizeToken(string $value): string
    {
        $normalized = preg_replace('/[\s\-\/]+/u', '', strtoupper(trim($value)));
        return is_string($normalized) ? $normalized : '';
    }
}

if (!function_exists('tonerCatalogBrandKey')) {
    function tonerCatalogBrandKey(string $brand): string
    {
        $brands = [
            'HP' => 'HP',
            'HEWLETTPACKARD' => 'HP',
            'KONICA' => 'Konica Minolta',
            'MINOLTA' => 'Konica Minolta',
            'KONICAMINOLTA' => 'Konica Minolta',
            'RICOH' => 'Ricoh',
        ];

        return $brands[tonerCatalogNormalizeToken($brand)] ?? '';
    }
}

if (!function_exists('tonerCatalogModelCandidates')) {
    function tonerCatalogModelCandidates(string $brand, string $model): array
    {
        $modelToken = tonerCatalogNormalizeToken($model);
        if ($modelToken === '') {
            return [];
        }

        $candidates = [$modelToken];
        $prefixes = [
            'HP' => ['HP', 'HEWLETTPACKARD'],
            'Konica Minolta' => ['KONICAMINOLTA', 'KONICA', 'MINOLTA'],
            'Ricoh' => ['RICOH'],
        ];

        foreach ($prefixes[$brand] ?? [] as $prefix) {
            if (strpos($modelToken, $prefix) === 0 && strlen($modelToken) > strlen($prefix)) {
                $candidates[] = substr($modelToken, strlen($prefix));
            }
        }

        if (preg_match_all('/[A-Z]*\d+[A-Z0-9]*/i', $model, $matches)) {
            foreach ($matches[0] as $modelCode) {
                $candidates[] = tonerCatalogNormalizeToken($modelCode);
            }
        }

        return array_values(array_unique(array_filter($candidates)));
    }
}

if (!function_exists('loadTonerCatalog')) {
    function loadTonerCatalog(): ?array
    {
        static $catalogLoaded = false;
        static $catalog = null;

        if ($catalogLoaded) {
            return $catalog;
        }
        $catalogLoaded = true;

        $path = dirname(__DIR__) . '/catalog/oem-toner-catalog.json';
        try {
            $json = @file_get_contents($path);
            if ($json === false) {
                throw new RuntimeException('Catalog file is unavailable');
            }
            $decoded = json_decode($json, true);
            if (!is_array($decoded) || !isset($decoded['brands']) || !is_array($decoded['brands'])) {
                throw new RuntimeException('Catalog file is invalid');
            }
            $catalog = $decoded;
        } catch (Throwable $e) {
            error_log('OEM toner catalog unavailable: ' . $e->getMessage());
        }

        return $catalog;
    }
}

if (!function_exists('resolveTonerPartNumbers')) {
    function resolveTonerPartNumbers(string $brand, string $model): array
    {
        $brandKey = tonerCatalogBrandKey($brand);
        $candidates = tonerCatalogModelCandidates($brandKey, $model);
        $result = [
            'matched' => false,
            'brand' => $brandKey ?: trim($brand),
            'model' => trim($model),
            'matchedAliases' => [],
            'colors' => [],
        ];

        $catalog = loadTonerCatalog();
        $brandData = $catalog['brands'][$brandKey] ?? null;
        if (!$brandData || empty($brandData['aliases']) || empty($brandData['options'])) {
            return $result;
        }

        $optionIds = [];
        foreach ($candidates as $candidate) {
            if (!isset($brandData['aliases'][$candidate])) {
                continue;
            }
            $result['matchedAliases'][] = $candidate;
            foreach ($brandData['aliases'][$candidate] as $optionId) {
                $optionIds[(int)$optionId] = true;
            }
        }

        $seen = [];
        foreach (array_keys($optionIds) as $optionId) {
            $option = $brandData['options'][$optionId] ?? null;
            if (!is_array($option) || empty($option['color']) || empty($option['partNumber'])) {
                continue;
            }
            $color = strtolower(trim((string)$option['color']));
            $dedupeKey = strtoupper((string)$option['partNumber']) . '|'
                . strtolower((string)($option['yield'] ?? '')) . '|'
                . strtolower((string)($option['region'] ?? ''));
            if (isset($seen[$color][$dedupeKey])) {
                continue;
            }
            $seen[$color][$dedupeKey] = true;
            $result['colors'][$color][] = $option;
        }

        $result['matched'] = !empty($result['colors']);
        return $result;
    }
}

/*
CHANGELOG
2026-11-25 Codex
- Added exact brand/model alias matching for all compatible OEM toner part numbers.
- Added safe catalog loading, option deduplication, and explicit no-match results.
*/
