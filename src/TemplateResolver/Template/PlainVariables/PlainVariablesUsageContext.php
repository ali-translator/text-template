<?php

namespace ALI\TextTemplate\TemplateResolver\Template\PlainVariables;

class PlainVariablesUsageContext implements PlainVariablesUsageContextInterface
{
    private PlainVariablesUsageCollector $collector;
    private array $aliases;

    /**
     * @param array<string, array<int, string>> $aliases
     */
    public function __construct(PlainVariablesUsageCollector $collector, array $aliases)
    {
        $this->collector = $collector;
        $this->aliases = $aliases;
    }

    public function collectContent(string $content): void
    {
        $this->collector->collectFromContent($content, $this->aliases);
    }

    public function collectCondition(string $expression): void
    {
        $this->collector->collectConditionExpression($expression, $this->aliases);
    }

    public function enterLoop(string $itemName, string $collectionName): PlainVariablesUsageContextInterface
    {
        return $this->collector->createLoopContext($itemName, $collectionName, $this->aliases);
    }

    public function addVariable(string $name, string $type): void
    {
        $this->collector->addVariableWithAliases($name, $type, $this->aliases);
    }
}
