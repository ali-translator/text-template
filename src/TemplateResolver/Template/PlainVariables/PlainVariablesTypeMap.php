<?php

namespace ALI\TextTemplate\TemplateResolver\Template\PlainVariables;

class PlainVariablesTypeMap implements PlainVariablesUsageResultInterface
{
    public const TYPE_STRING = 'string';
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_NUMBER = 'number';
    public const TYPE_ARRAY = 'array';

    private const TYPE_PRIORITY = [
        self::TYPE_STRING => 1,
        self::TYPE_BOOLEAN => 2,
        self::TYPE_NUMBER => 3,
    ];

    /**
     * @var array<string, PlainVariableUsageDto>
     */
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
        $node = $this->getOrCreateArrayNode($arrayPath);
        $this->mergeScalarIntoItemType($node, $type);
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
        $node = $this->getOrCreateArrayNode($arrayPath);
        $items = &$node->getItemsRef();
        $this->addScalarToMap($items, $itemPath, $type);
    }

    public function toArray(): array
    {
        $this->normalizeItems();

        $result = [];
        foreach ($this->variables as $name => $variable) {
            $result[$name] = $variable->toArray();
        }

        return $result;
    }

    public function toDtoMap(): array
    {
        $this->normalizeItems();

        return $this->variables;
    }

    public function toSimplifiedVariableNames(): array
    {
        $names = [];
        foreach ($this->variables as $variableUsageDto) {
            $names = $names + $variableUsageDto->toSimplifiedVariableNames();
        }

        return array_values($names);
    }

    public function isEmpty(): bool
    {
        return empty($this->variables);
    }

    /**
     * @param array<string, PlainVariableUsageDto> $map
     */
    private function addScalarToMap(array &$map, array $path, string $type): void
    {
        $segment = array_shift($path);
        if ($segment === null || $segment === '') {
            return;
        }

        if (!$path) {
            if (isset($map[$segment]) && $map[$segment]->getType() === self::TYPE_ARRAY) {
                return;
            }

            $existingType = isset($map[$segment]) ? $map[$segment]->getType() : null;
            $map[$segment] = new PlainVariableUsageDto($segment, $this->mergeScalarType($existingType, $type));
            return;
        }

        $node = $this->getOrCreateArraySegment($map, $segment);
        $items = &$node->getItemsRef();
        $this->addScalarToMap($items, $path, $type);
    }

    private function getOrCreateArrayNode(array $path): PlainVariableUsageDto
    {
        $current = &$this->variables;
        $lastSegment = array_pop($path);

        foreach ($path as $segment) {
            if ($segment === '') {
                continue;
            }

            $node = $this->getOrCreateArraySegment($current, $segment);
            $current = &$node->getItemsRef();
        }

        if ($lastSegment === null || $lastSegment === '') {
            return new PlainVariableUsageDto('', self::TYPE_ARRAY);
        }

        return $this->getOrCreateArraySegment($current, $lastSegment);
    }

    /**
     * @param array<string, PlainVariableUsageDto> $map
     */
    private function getOrCreateArraySegment(array &$map, string $segment): PlainVariableUsageDto
    {
        if (!isset($map[$segment]) || $map[$segment]->getType() !== self::TYPE_ARRAY) {
            $map[$segment] = new PlainVariableUsageDto($segment, self::TYPE_ARRAY);
        }

        return $map[$segment];
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

    private function mergeScalarIntoItemType(PlainVariableUsageDto $node, string $type): void
    {
        $merged = $this->mergeScalarType($node->getItemScalarType(), $type);
        $node->setItemScalarType($merged);
    }

    private function normalizeItems(): void
    {
        foreach ($this->variables as $variable) {
            $this->normalizeDto($variable);
        }
    }

    private function normalizeDto(PlainVariableUsageDto $variable): void
    {
        if ($variable->getType() !== self::TYPE_ARRAY) {
            return;
        }

        $items = &$variable->getItemsRef();
        if ($items === [] && $variable->getItemScalarType() === null) {
            $variable->setItemScalarType(self::TYPE_STRING);
        }

        foreach ($items as $item) {
            $this->normalizeDto($item);
        }
    }
}
