<?php

namespace ALI\TextTemplate\TemplateResolver\Template\PlainVariables;

class PlainVariablesUsageContext implements PlainVariablesUsageContextInterface
{
    private PlainVariablesUsageCollector $collector;
    private PlainVariablesTypeMap $typeMap;
    private array $aliases;

    /**
     * @param PlainVariablesTypeMap $typeMap
     * @param array<string, array<int, string>> $aliases
     */
    public function __construct(PlainVariablesUsageCollector $collector, PlainVariablesTypeMap $typeMap, array $aliases)
    {
        $this->collector = $collector;
        $this->typeMap = $typeMap;
        $this->aliases = $aliases;
    }

    public function collectContent(string $content): void
    {
        $this->collector->collectFromContent($this->typeMap, $content, $this->aliases);
    }

    public function collectCondition(string $expression): void
    {
        $this->collector->collectConditionExpression($this->typeMap, $expression, $this->aliases);
    }

    public function enterLoop(string $itemName, string $collectionName): PlainVariablesUsageContextInterface
    {
        return $this->collector->createLoopContext($this->typeMap, $itemName, $collectionName, $this->aliases);
    }

    public function addVariable(string $name, string $type): void
    {
        $this->collector->addVariableWithAliases($this->typeMap, $name, $type, $this->aliases);
    }
}
