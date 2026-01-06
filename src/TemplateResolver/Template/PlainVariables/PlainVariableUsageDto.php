<?php

namespace ALI\TextTemplate\TemplateResolver\Template\PlainVariables;

class PlainVariableUsageDto
{
    private string $name;
    private string $type;
    private ?string $itemScalarType = null;
    /**
     * @var array<string, PlainVariableUsageDto>
     */
    private array $items = [];

    public function __construct(string $name, string $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getItemScalarType(): ?string
    {
        return $this->itemScalarType;
    }

    public function setItemScalarType(?string $itemScalarType): void
    {
        $this->itemScalarType = $itemScalarType;
    }

    /**
     * @return array<string, PlainVariableUsageDto>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @return array<string, PlainVariableUsageDto>
     */
    public function &getItemsRef(): array
    {
        return $this->items;
    }

    /**
     * @param array<string, PlainVariableUsageDto> $items
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    /**
     * @return array<string, mixed>|string
     */
    public function toArray()
    {
        // Scalar type
        if ($this->type !== PlainVariablesTypeMap::TYPE_ARRAY) {
            return $this->type;
        }

        // Array with Scalar items
        if ($this->itemScalarType !== null) {
            return [
                'type' => PlainVariablesTypeMap::TYPE_ARRAY,
                'itemScalarType' => $this->itemScalarType,
                'items' => [],
            ];
        }

        // Array with Array items
        return [
            'type' => PlainVariablesTypeMap::TYPE_ARRAY,
            'items' => array_map(fn($item) => $item->toArray(), $this->items),
        ];
    }

    public function toSimplifiedVariableNames(): array
    {
        return $this->toSimplifiedVariableNamesInternal('');
    }

    private function toSimplifiedVariableNamesInternal(string $prefix): array
    {
        $newVariables = [];

        $name = ($prefix ? $prefix . '.' : '') . $this->getName();
        if ($name === '') {
            return $newVariables;
        }

        $newVariables[$name] = $name;

        if ($this->getType() === PlainVariablesTypeMap::TYPE_ARRAY) {
            foreach ($this->getItems() as $item) {
                $newVariables = $newVariables + $item->toSimplifiedVariableNamesInternal($name);
            }
        }

        return $newVariables;
    }
}
