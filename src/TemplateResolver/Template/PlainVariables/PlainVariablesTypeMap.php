<?php

namespace ALI\TextTemplate\TemplateResolver\Template\PlainVariables;

class PlainVariablesTypeMap
{
    public const TYPE_STRING = 'string';
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_NUMBER = 'number';
    public const TYPE_ARRAY = 'array';

    private const SELF_KEY = '_self';

    private const TYPE_PRIORITY = [
        self::TYPE_STRING => 1,
        self::TYPE_BOOLEAN => 2,
        self::TYPE_NUMBER => 3,
    ];

    private array $variables = [];

    public function addScalar(array $path, string $type): void
    {
        $path = $this->normalizePath($path);
        if (!$path) {
            return;
        }

        $type = $this->normalizeScalarType($type);
        $this->addScalarToMap($this->variables, $path, $type);
    }

    public function addArray(array $path): void
    {
        $path = $this->normalizePath($path);
        if (!$path) {
            return;
        }

        $this->getOrCreateArrayNode($path);
    }

    public function addArrayItemScalar(array $arrayPath, string $type): void
    {
        $arrayPath = $this->normalizePath($arrayPath);
        if (!$arrayPath) {
            return;
        }

        $type = $this->normalizeScalarType($type);
        $node = &$this->getOrCreateArrayNode($arrayPath);
        $this->mergeScalarIntoItems($node['items'], $type);
    }

    public function addArrayItemPropertyScalar(array $arrayPath, array $itemPath, string $type): void
    {
        $arrayPath = $this->normalizePath($arrayPath);
        $itemPath = $this->normalizePath($itemPath);
        if (!$arrayPath || !$itemPath) {
            if ($arrayPath) {
                $this->addArrayItemScalar($arrayPath, $type);
            }
            return;
        }

        $type = $this->normalizeScalarType($type);
        $node = &$this->getOrCreateArrayNode($arrayPath);
        $this->ensureItemsContainerArray($node);
        $this->addScalarToMap($node['items'], $itemPath, $type);
    }

    public function toArray(): array
    {
        return $this->variables;
    }

    private function addScalarToMap(array &$map, array $path, string $type): void
    {
        $segment = array_shift($path);
        if ($segment === null || $segment === '') {
            return;
        }

        if (!$path) {
            if (isset($map[$segment]) && is_array($map[$segment]) && ($map[$segment]['type'] ?? null) === self::TYPE_ARRAY) {
                return;
            }

            $map[$segment] = $this->mergeScalarType($map[$segment] ?? null, $type);
            return;
        }

        $map = $this->prepareSegment($map, $segment);
        $this->ensureItemsContainerArray($map[$segment]);

        $this->addScalarToMap($map[$segment]['items'], $path, $type);
    }

    private function &getOrCreateArrayNode(array $path): array
    {
        $current = &$this->variables;
        $lastSegment = array_pop($path);

        foreach ($path as $segment) {
            if ($segment === '') {
                continue;
            }

            $current = $this->prepareSegment($current, $segment);
            $this->ensureItemsContainerArray($current[$segment]);
            $current = &$current[$segment]['items'];
        }

        if ($lastSegment === null || $lastSegment === '') {
            return $current;
        }

        $current = $this->prepareSegment($current, $lastSegment);

        return $current[$lastSegment];
    }

    private function normalizePath(array $path): array
    {
        $normalized = [];
        foreach ($path as $segment) {
            $segment = trim((string)$segment);
            if ($segment !== '') {
                $normalized[] = $segment;
            }
        }

        return $normalized;
    }

    private function normalizeScalarType(string $type): string
    {
        if (isset(self::TYPE_PRIORITY[$type])) {
            return $type;
        }

        return self::TYPE_STRING;
    }

    private function mergeScalarType($existing, string $incoming): string
    {
        $incoming = $this->normalizeScalarType($incoming);
        if (!is_string($existing) || !isset(self::TYPE_PRIORITY[$existing])) {
            return $incoming;
        }

        if (self::TYPE_PRIORITY[$incoming] > self::TYPE_PRIORITY[$existing]) {
            return $incoming;
        }

        return $existing;
    }

    private function ensureItemsContainerArray(array &$node): void
    {
        if (!isset($node['items'])) {
            $node['items'] = [];
            return;
        }

        if (is_string($node['items'])) {
            $node['items'] = [
                self::SELF_KEY => $node['items'],
            ];
            return;
        }

        if (!is_array($node['items'])) {
            $node['items'] = [];
        }
    }

    private function mergeScalarIntoItems(&$items, string $type): void
    {
        if (!isset($items)) {
            $items = $type;
            return;
        }

        if (is_string($items)) {
            $items = $this->mergeScalarType($items, $type);
            return;
        }

        if (!is_array($items)) {
            $items = $type;
            return;
        }

        if ($items === []) {
            $items = $type;
            return;
        }

        $items[self::SELF_KEY] = $this->mergeScalarType($items[self::SELF_KEY] ?? null, $type);
    }

    private function prepareSegment($current, $segment)
    {
        if (
            (!isset($current[$segment]) || is_string($current[$segment]))
            ||
            (is_array($current[$segment]) && ($current[$segment]['type'] ?? null) !== self::TYPE_ARRAY)
        ) {
            $current[$segment] = [
                'type' => self::TYPE_ARRAY,
                'items' => [],
            ];
        }

        return $current;
    }
}
