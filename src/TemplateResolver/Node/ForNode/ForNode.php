<?php

namespace ALI\TextTemplate\TemplateResolver\Node\ForNode;

use ALI\TextTemplate\TemplateResolver\Node\NodeInterface;
use ALI\TextTemplate\TemplateResolver\Node\NodeRuntime;

class ForNode implements NodeInterface
{
    private string $itemName;
    private string $collectionName;
    private string $content;

    public function __construct(string $itemName, string $collectionName, string $content)
    {
        $this->itemName = $itemName;
        $this->collectionName = $collectionName;
        $this->content = $content;
    }

    public function resolve(NodeRuntime $runtime): string
    {
        $items = $runtime->getIterable($this->collectionName);
        if (!$items) {
            return '';
        }

        $resolved = '';
        foreach ($items as $item) {
            $iterationCollection = $runtime->createIterationCollection($this->itemName, $item);
            $resolved .= $runtime->resolveContent($this->content, $iterationCollection);
        }

        return $resolved;
    }

    public function getItemName(): string
    {
        return $this->itemName;
    }

    public function getCollectionName(): string
    {
        return $this->collectionName;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
