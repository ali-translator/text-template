<?php

namespace ALI\TextTemplate\TemplateResolver\Template\VariableResolver;

use ALI\TextTemplate\TextTemplateItem;
use ALI\TextTemplate\TextTemplatesCollection;
use ArrayAccess;

class CollectionVariableResolver
{
    public function find(?TextTemplatesCollection $collection, string $variableId): ?TextTemplateItem
    {
        if (!$collection) {
            return null;
        }

        $templateItem = $collection->get($variableId);
        if ($templateItem) {
            return $templateItem;
        }

        $path = $this->splitPath($variableId);
        if (count($path) < 2) {
            return null;
        }

        $rootVariableId = array_shift($path);
        if ($rootVariableId === null || $rootVariableId === '') {
            return null;
        }

        $rootVariable = $collection->get($rootVariableId);
        if (!$rootVariable || !$rootVariable->hasRawValue()) {
            return null;
        }

        $valueExists = false;
        $resolvedValue = $this->resolvePathValue($rootVariable->getRawValue(), $path, $valueExists);
        if (!$valueExists) {
            return null;
        }

        return $this->normalizeToTextTemplateItem($resolvedValue);
    }

    public function addOrReplace(TextTemplatesCollection $collection, string $variableId, $value): void
    {
        if ($collection->get($variableId)) {
            $collection->remove($variableId);
        }

        $collection->add($this->normalizeToTextTemplateItem($value), $variableId);
    }

    /**
     * @return string[]
     */
    private function splitPath(string $variableId): array
    {
        if (strpos($variableId, '.') === false) {
            return [];
        }

        $segments = array_map('trim', explode('.', $variableId));
        $segments = array_filter($segments, static function (string $segment): bool {
            return $segment !== '';
        });

        return array_values($segments);
    }

    /**
     * @param string[] $path
     */
    private function resolvePathValue($value, array $path, bool &$valueExists)
    {
        $currentValue = $value;

        foreach ($path as $segment) {
            if (is_array($currentValue)) {
                if (!array_key_exists($segment, $currentValue)) {
                    $valueExists = false;
                    return null;
                }

                $currentValue = $currentValue[$segment];
                continue;
            }

            if ($currentValue instanceof ArrayAccess) {
                if (!$currentValue->offsetExists($segment)) {
                    $valueExists = false;
                    return null;
                }

                $currentValue = $currentValue[$segment];
                continue;
            }

            if (is_object($currentValue)) {
                $objectProperties = get_object_vars($currentValue);
                if (!array_key_exists($segment, $objectProperties)) {
                    $valueExists = false;
                    return null;
                }

                $currentValue = $objectProperties[$segment];
                continue;
            }

            $valueExists = false;
            return null;
        }

        $valueExists = true;
        return $currentValue;
    }

    private function normalizeToTextTemplateItem($value): TextTemplateItem
    {
        if ($value instanceof TextTemplateItem) {
            return $value;
        }

        $stringValue = '';
        if (is_scalar($value)) {
            $stringValue = (string)$value;
        } elseif (is_object($value) && method_exists($value, '__toString')) {
            $stringValue = (string)$value;
        }

        $textTemplateItem = new TextTemplateItem($stringValue);
        if (is_array($value) || is_object($value)) {
            $textTemplateItem->setRawValue($value);
        }

        return $textTemplateItem;
    }
}
