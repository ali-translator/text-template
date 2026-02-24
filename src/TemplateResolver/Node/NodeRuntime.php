<?php

namespace ALI\TextTemplate\TemplateResolver\Node;

use ALI\TextTemplate\TemplateResolver\TextTemplateMessageResolver;
use ALI\TextTemplate\TemplateResolver\Template\VariableResolver\CollectionVariableResolver;
use ALI\TextTemplate\TextTemplateItem;
use ALI\TextTemplate\TextTemplatesCollection;

class NodeRuntime
{
    private ConditionEvaluator $conditionEvaluator;
    private TextTemplateMessageResolver $textTemplateMessageResolver;
    private ?TextTemplatesCollection $textTemplatesCollection;
    private CollectionVariableResolver $collectionVariableResolver;

    public function __construct(
        ConditionEvaluator $conditionEvaluator,
        TextTemplateMessageResolver $textTemplateMessageResolver,
        ?TextTemplatesCollection $textTemplatesCollection,
        ?CollectionVariableResolver $collectionVariableResolver = null
    )
    {
        $this->conditionEvaluator = $conditionEvaluator;
        $this->textTemplateMessageResolver = $textTemplateMessageResolver;
        $this->textTemplatesCollection = $textTemplatesCollection;
        $this->collectionVariableResolver = $collectionVariableResolver ?? new CollectionVariableResolver();
    }

    public function evaluateCondition(string $expression): bool
    {
        return $this->conditionEvaluator->evaluate($expression, $this->textTemplatesCollection);
    }

    public function resolveContent(string $content, ?TextTemplatesCollection $textTemplatesCollection = null): string
    {
        $templateItem = new TextTemplateItem(
            $content,
            $textTemplatesCollection ?? $this->textTemplatesCollection,
            $this->textTemplateMessageResolver
        );

        return $this->textTemplateMessageResolver->resolve($templateItem);
    }

    public function getIterable(string $collectionName): ?iterable
    {
        $templateItem = $this->collectionVariableResolver->find($this->textTemplatesCollection, $collectionName);
        if (!$templateItem) {
            return null;
        }

        if ($templateItem->hasRawValue()) {
            $rawValue = $templateItem->getRawValue();
            if (is_iterable($rawValue)) {
                return $rawValue;
            }
        }

        return null;
    }

    public function createIterationCollection(string $itemName, $itemValue): TextTemplatesCollection
    {
        $iterationCollection = $this->textTemplatesCollection ? clone $this->textTemplatesCollection : new TextTemplatesCollection();

        $this->addValueToCollection($iterationCollection, $itemName, $itemValue);

        if (is_array($itemValue)) {
            foreach ($itemValue as $key => $value) {
                $this->addValueToCollection($iterationCollection, $itemName . '.' . $key, $value);
            }
        } elseif (is_object($itemValue)) {
            foreach (get_object_vars($itemValue) as $key => $value) {
                $this->addValueToCollection($iterationCollection, $itemName . '.' . $key, $value);
            }
        }

        return $iterationCollection;
    }

    private function addValueToCollection(TextTemplatesCollection $collection, string $name, $value): void
    {
        $this->collectionVariableResolver->addOrReplace($collection, $name, $value);
    }
}
