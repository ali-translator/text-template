<?php

namespace ALI\TextTemplate\TemplateResolver\Template\PlainVariables;

interface PlainVariablesUsageContextInterface
{
    public function collectContent(string $content): void;

    public function collectCondition(string $expression): void;

    public function enterLoop(string $itemName, string $collectionName): PlainVariablesUsageContextInterface;

    public function addVariable(string $name, string $type): void;
}
