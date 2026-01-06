<?php

namespace ALI\TextTemplate\TemplateResolver\Node;

class NodeParseResult
{
    private string $content;

    /**
     * @var NodeInterface[]
     */
    private array $nodes;

    /**
     * @var string[]
     */
    private array $nodeContents;

    /**
     * @param NodeInterface[] $nodes
     * @param string[] $nodeContents
     */
    public function __construct(string $content, array $nodes, array $nodeContents)
    {
        $this->content = $content;
        $this->nodes = $nodes;
        $this->nodeContents = $nodeContents;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @return NodeInterface[]
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }

    public function hasNodes(): bool
    {
        return !empty($this->nodes);
    }

    public function getNodeContent(string $nodeId): ?string
    {
        return $this->nodeContents[$nodeId] ?? null;
    }

    /**
     * @return string[]
     */
    public function getNodeContents(): array
    {
        return $this->nodeContents;
    }
}
