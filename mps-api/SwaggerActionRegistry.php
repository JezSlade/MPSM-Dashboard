<?php
/**
 * SwaggerActionRegistry
 *
 * Parses swagger/openapi documentation and exposes action metadata for the engine.
 * Supports Swagger 2.0 and OpenAPI 3.0 (subset used by the project).
 */

class SwaggerActionRegistry
{
    private static $instance = null;

    /** @var array<string,array> */
    private $operations = [];

    /** @var string */
    private $specPath;

    /** @var array<string,mixed> */
    private $spec = [];

    /** @var array<string,array> */
    private $resolvedSchemaCache = [];

    private function __construct(array $candidatePaths)
    {
        $this->specPath = $this->detectSpecPath($candidatePaths);
        $this->loadSpec();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            $candidates = [
                dirname(__DIR__) . '/.canonical/Swagger.json',
                dirname(__DIR__) . '/Swagger.json',
                __DIR__ . '/swagger.json',
                dirname(__DIR__, 1) . '/../Swagger.json',
                dirname(__DIR__, 2) . '/documentation/Endpoints/Swagger.json',
            ];
            self::$instance = new self($candidates);
        }
        return self::$instance;
    }

    public function getSpecPath(): string
    {
        return $this->specPath;
    }

    /**
     * Retrieve metadata for an action.
     *
     * @param string $actionName
     * @return array|null
     */
    public function getOperation(string $actionName): ?array
    {
        $normalized = $this->normalizeKey($actionName);
        return $this->operations[$normalized] ?? null;
    }

    /**
     * Return all operations keyed by their canonical action name.
     *
     * @return array<string,array>
     */
    public function listOperations(): array
    {
        $unique = [];
        foreach ($this->operations as $operation) {
            $unique[$operation['action']] = $operation;
        }
        ksort($unique);
        return array_values($unique);
    }

    private function loadSpec(): void
    {
        if (!file_exists($this->specPath)) {
            throw new RuntimeException('Swagger specification not found at ' . $this->specPath);
        }

        $raw = file_get_contents($this->specPath);
        if ($raw === false) {
            throw new RuntimeException('Unable to read swagger specification from ' . $this->specPath);
        }

        $json = json_decode($raw, true);
        if (!is_array($json)) {
            throw new RuntimeException('Unable to parse swagger specification: ' . json_last_error_msg());
        }

        $this->spec = $json;
        $this->parseSwagger($json);
    }

    private function parseSwagger(array $spec): void
    {
        $basePath = $spec['basePath'] ?? '';
        $globalConsumes = $spec['consumes'] ?? [];

        if (!isset($spec['paths']) || !is_array($spec['paths'])) {
            throw new RuntimeException('Swagger specification missing "paths" section.');
        }

        foreach ($spec['paths'] as $path => $methods) {
            if (!is_array($methods)) {
                continue;
            }

            $pathParameters = $this->normalizeParameterLayer($methods['parameters'] ?? []);

            foreach ($methods as $httpMethod => $definition) {
                if ($httpMethod === 'parameters') {
                    continue;
                }

                if (!is_array($definition)) {
                    continue;
                }

                $operationId = $definition['operationId'] ?? strtoupper($httpMethod) . ' ' . $path;
                $consumes = $definition['consumes'] ?? $globalConsumes;

                $operationParameters = $this->mergeParameterLayers(
                    $pathParameters,
                    $this->normalizeParameterLayer($definition['parameters'] ?? [])
                );

                $grouped = $this->groupParameters($operationParameters);

                $operation = [
                    'action' => $operationId,
                    'aliases' => $this->buildAliases($operationId),
                    'method' => strtoupper($httpMethod),
                    'path' => ltrim($basePath . $path, '/'),
                    'summary' => $definition['summary'] ?? ($definition['description'] ?? ''),
                    'pathParams' => $grouped['path'],
                    'queryParams' => $grouped['query'],
                    'headerParams' => $grouped['header'],
                    'formParams' => $grouped['formData'],
                    'cookieParams' => $grouped['cookie'],
                    'hasBody' => $grouped['body'] !== null || !empty($grouped['formData']),
                    'bodyParam' => $grouped['body']['name'] ?? null,
                    'bodySchema' => $grouped['body']['schema'] ?? null,
                    'bodyParameter' => $grouped['body'],
                    'consumes' => $consumes,
                    'produces' => $definition['produces'] ?? ($spec['produces'] ?? []),
                    'parameters' => $operationParameters,
                    'security' => $definition['security'] ?? ($methods['security'] ?? ($spec['security'] ?? [])),
                ];

                $normalizedKey = $this->normalizeKey($operationId);
                $this->operations[$normalizedKey] = $operation;

                foreach ($operation['aliases'] as $alias) {
                    $this->operations[$this->normalizeKey($alias)] = $operation;
                }
            }
        }
    }

    /**
     * Resolve and normalize a parameter layer such as a path-level or operation-level
     * "parameters" array. Handles $ref resolution and override semantics.
     *
     * @param mixed $parameters
     * @return array<int,array>
     */
    private function normalizeParameterLayer($parameters): array
    {
        $normalized = [];

        if (!is_array($parameters)) {
            return $normalized;
        }

        foreach ($parameters as $param) {
            if (!is_array($param)) {
                continue;
            }

            $resolved = $this->resolveParameter($param);
            if ($resolved === null) {
                continue;
            }

            $key = $resolved['name'] . '|' . $resolved['in'];
            $normalized[$key] = $resolved;
        }

        return array_values($normalized);
    }

    /**
     * Merge parameter layers according to swagger precedence rules:
     * path-level parameters override global definitions,
     * operation-level parameters override path-level definitions.
     *
     * @param array<int,array> ...$layers
     * @return array<int,array>
     */
    private function mergeParameterLayers(array ...$layers): array
    {
        $merged = [];
        foreach ($layers as $layer) {
            foreach ($layer as $param) {
                $key = $param['name'] . '|' . $param['in'];
                $merged[$key] = $param;
            }
        }
        return array_values($merged);
    }

    /**
     * Group parameters by location for quick lookups.
     *
     * @param array<int,array> $parameters
     * @return array{path:array,query:array,header:array,formData:array,cookie:array,body:?array}
     */
    private function groupParameters(array $parameters): array
    {
        $grouped = [
            'path' => [],
            'query' => [],
            'header' => [],
            'formData' => [],
            'cookie' => [],
            'body' => null,
        ];

        foreach ($parameters as $param) {
            switch ($param['in']) {
                case 'path':
                    $grouped['path'][$param['name']] = $param;
                    break;
                case 'query':
                    $grouped['query'][$param['name']] = $param;
                    break;
                case 'header':
                    $grouped['header'][$param['name']] = $param;
                    break;
                case 'formData':
                    $grouped['formData'][$param['name']] = $param;
                    break;
                case 'cookie':
                    $grouped['cookie'][$param['name']] = $param;
                    break;
                case 'body':
                    $grouped['body'] = $param;
                    break;
            }
        }

        return $grouped;
    }

    /**
     * Resolve a single parameter definition, handling $ref references and nested schemas.
     *
     * @param array $param
     * @param array<string,bool> $visited
     * @return array|null
     */
    private function resolveParameter(array $param, array $visited = []): ?array
    {
        if (isset($param['$ref'])) {
            $resolved = $this->resolveRef($param['$ref']);
            if (!is_array($resolved)) {
                throw new RuntimeException('Referenced parameter is not an object: ' . $param['$ref']);
            }

            $override = $param;
            unset($override['$ref']);
            $param = array_merge($resolved, $override);
        }

        if (!isset($param['name'], $param['in'])) {
            return null;
        }

        $param['required'] = (bool)($param['required'] ?? false);
        if (isset($param['x-nullable']) && !isset($param['nullable'])) {
            $param['nullable'] = (bool)$param['x-nullable'];
        }

        if ($param['in'] === 'body' && isset($param['schema']) && is_array($param['schema'])) {
            $param['schema'] = $this->resolveSchema($param['schema'], $visited);
        } elseif (isset($param['schema']) && is_array($param['schema'])) {
            $param['schema'] = $this->resolveSchema($param['schema'], $visited);
        }

        if (isset($param['items']) && is_array($param['items'])) {
            $param['items'] = $this->resolveSchema($param['items'], $visited);
        }

        return $param;
    }

    /**
     * Resolve a schema object, merging $ref targets and handling composition keywords.
     *
     * @param array $schema
     * @param array<string,bool> $visited
     * @return array
     */
    private function resolveSchema(array $schema, array $visited = []): array
    {
        if (isset($schema['$ref'])) {
            $ref = $schema['$ref'];

            if (isset($visited[$ref])) {
                throw new RuntimeException('Circular schema reference detected: ' . $ref);
            }

            if (isset($this->resolvedSchemaCache[$ref])) {
                $base = $this->resolvedSchemaCache[$ref];
            } else {
                $visited[$ref] = true;
                $resolved = $this->resolveRef($ref);
                if (!is_array($resolved)) {
                    throw new RuntimeException('Referenced schema is not an object: ' . $ref);
                }
                $base = $this->resolveSchema($resolved, $visited);
                $this->resolvedSchemaCache[$ref] = $base;
                unset($visited[$ref]);
            }

            $overrides = $schema;
            unset($overrides['$ref']);

            if ($overrides === []) {
                return is_array($base) ? $base : (array)$base;
            }

            return $this->mergeSchemas($base, $overrides, $visited);
        }

        if (isset($schema['allOf']) && is_array($schema['allOf'])) {
            $merged = [];
            foreach ($schema['allOf'] as $component) {
                if (!is_array($component)) {
                    continue;
                }
                $merged = $this->mergeSchemas($merged, $this->resolveSchema($component, $visited), $visited);
            }
            $schema = $this->mergeSchemas($merged, array_diff_key($schema, ['allOf' => true]), $visited);
        } else {
            $schema = $this->mergeSchemas([], $schema, $visited);
        }

        return $schema;
    }

    /**
     * Merge two schema objects, recursively resolving child schemas.
     *
     * @param array $base
     * @param array $overlay
     * @param array<string,bool> $visited
     * @return array
     */
    private function mergeSchemas(array $base, array $overlay, array &$visited = []): array
    {
        foreach ($overlay as $key => $value) {
            if ($key === 'required' && is_array($value)) {
                $existing = $base['required'] ?? [];
                $base['required'] = array_values(array_unique(array_merge($existing, $value)));
                continue;
            }

            if ($key === 'properties' && is_array($value)) {
                $base['properties'] = $base['properties'] ?? [];
                foreach ($value as $propName => $propSchema) {
                    if (!is_array($propSchema)) {
                        $base['properties'][$propName] = $propSchema;
                        continue;
                    }

                    $resolvedProp = $this->resolveSchema($propSchema, $visited);
                    if (isset($base['properties'][$propName]) && is_array($base['properties'][$propName])) {
                        $base['properties'][$propName] = $this->mergeSchemas($base['properties'][$propName], $resolvedProp, $visited);
                    } else {
                        $base['properties'][$propName] = $resolvedProp;
                    }
                }
                continue;
            }

            if (($key === 'items' || $key === 'additionalProperties') && is_array($value)) {
                $base[$key] = $this->resolveSchema($value, $visited);
                continue;
            }

            if (($key === 'oneOf' || $key === 'anyOf' || $key === 'allOf') && is_array($value)) {
                $base[$key] = [];
                foreach ($value as $entry) {
                    $base[$key][] = is_array($entry) ? $this->resolveSchema($entry, $visited) : $entry;
                }
                continue;
            }

            if ($key === 'not' && is_array($value)) {
                $base[$key] = $this->resolveSchema($value, $visited);
                continue;
            }

            if (is_array($value) && $this->isAssocArray($value)) {
                if (!isset($base[$key]) || !is_array($base[$key]) || !$this->isAssocArray($base[$key])) {
                    $base[$key] = [];
                }
                $base[$key] = $this->mergeSchemas($base[$key], $value, $visited);
                continue;
            }

            $base[$key] = $value;
        }

        return $base;
    }

    /**
     * Resolve a JSON pointer within the current specification.
     *
     * @param string $ref
     * @return mixed
     */
    private function resolveRef(string $ref)
    {
        if (strpos($ref, '#/') !== 0) {
            throw new RuntimeException('External references are not supported: ' . $ref);
        }

        $path = explode('/', substr($ref, 2));
        $node = $this->spec;
        foreach ($path as $segment) {
            $segment = str_replace('~1', '/', str_replace('~0', '~', $segment));
            if (!is_array($node) || !array_key_exists($segment, $node)) {
                throw new RuntimeException('Unable to resolve reference: ' . $ref);
            }
            $node = $node[$segment];
        }
        return $node;
    }

    /**
     * Determine if an array uses sequential integer keys.
     *
     * @param array $value
     * @return bool
     */
    private function isAssocArray(array $value): bool
    {
        if ($value === []) {
            return false;
        }
        return array_keys($value) !== range(0, count($value) - 1);
    }

    private function detectSpecPath(array $candidatePaths): string
    {
        foreach ($candidatePaths as $path) {
            if (!$path) {
                continue;
            }
            $real = realpath($path);
            if ($real && is_readable($real)) {
                return $real;
            }
        }

        throw new RuntimeException('Unable to locate swagger specification file.');
    }

    private function normalizeKey(string $value): string
    {
        $value = strtolower($value);
        $value = str_replace(['\\', '/', '.', '-', ' '], '_', $value);
        return preg_replace('/[^a-z0-9_]+/', '', $value);
    }

    private function buildAliases(string $operationId): array
    {
        $aliases = [];
        $aliases[] = str_replace('/', '_', $operationId);
        $aliases[] = str_replace('/', '.', $operationId);
        $aliases[] = str_replace('/', '-', $operationId);
        return array_unique($aliases);
    }
}

