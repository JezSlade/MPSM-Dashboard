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
        $json = json_decode($raw, true);

        if (!is_array($json)) {
            throw new RuntimeException('Unable to parse swagger specification: ' . json_last_error_msg());
        }

        $this->parseSwagger($json);
    }

    private function parseSwagger(array $spec): void
    {
        $basePath = $spec['basePath'] ?? '';
        $globalParams = $this->indexParameters($spec['parameters'] ?? []);
        $globalConsumes = $spec['consumes'] ?? [];

        if (!isset($spec['paths']) || !is_array($spec['paths'])) {
            throw new RuntimeException('Swagger specification missing "paths" section.');
        }

        foreach ($spec['paths'] as $path => $methods) {
            if (!is_array($methods)) {
                continue;
            }

            $pathLevelParams = $this->indexParameters($methods['parameters'] ?? []);

            foreach ($methods as $httpMethod => $definition) {
                if ($httpMethod === 'parameters') {
                    continue;
                }

                if (!is_array($definition)) {
                    continue;
                }

                $operationId = $definition['operationId'] ?? null;
                if (empty($operationId)) {
                    $operationId = strtoupper($httpMethod) . ' ' . $path;
                }

                $consumes = $definition['consumes'] ?? $globalConsumes;
                $parameters = $this->collectParameters(
                    $globalParams,
                    $pathLevelParams,
                    $this->indexParameters($definition['parameters'] ?? [])
                );

                $operation = [
                    'action' => $operationId,
                    'aliases' => $this->buildAliases($operationId),
                    'method' => strtoupper($httpMethod),
                    'path' => ltrim($basePath . $path, '/'),
                    'summary' => $definition['summary'] ?? ($definition['description'] ?? ''),
                    'pathParams' => [],
                    'queryParams' => [],
                    'headerParams' => [],
                    'formParams' => [],
                    'hasBody' => false,
                    'bodyParam' => null,
                    'consumes' => $consumes,
                ];

                foreach ($parameters as $param) {
                    $name = $param['name'];
                    $required = $param['required'] ?? false;
                    $location = $param['in'];

                    switch ($location) {
                        case 'path':
                            $operation['pathParams'][$name] = ['required' => true];
                            break;
                        case 'query':
                            $operation['queryParams'][$name] = ['required' => $required];
                            break;
                        case 'header':
                            $operation['headerParams'][$name] = ['required' => $required];
                            break;
                        case 'formData':
                            $operation['formParams'][$name] = ['required' => $required];
                            break;
                        case 'body':
                            $operation['hasBody'] = true;
                            $operation['bodyParam'] = $name;
                            $operation['bodySchema'] = $param['schema'] ?? null;
                            break;
                    }
                }

                if ($operation['hasBody'] === false && !empty($operation['formParams'])) {
                    $operation['hasBody'] = true;
                }

                $normalizedKey = $this->normalizeKey($operationId);
                $this->operations[$normalizedKey] = $operation;

                foreach ($operation['aliases'] as $alias) {
                    $this->operations[$this->normalizeKey($alias)] = $operation;
                }
            }
        }
    }

    private function indexParameters(array $parameters): array
    {
        $indexed = [];
        foreach ($parameters as $param) {
            if (!isset($param['name'])) {
                continue;
            }
            $indexed[$param['name']] = $param;
        }
        return $indexed;
    }

    /**
     * Merge parameter definitions by precedence (global < path < operation).
     */
    private function collectParameters(array ...$layers): array
    {
        $result = [];
        foreach ($layers as $layer) {
            foreach ($layer as $name => $definition) {
                $result[$name] = $definition;
            }
        }
        return array_values($result);
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
