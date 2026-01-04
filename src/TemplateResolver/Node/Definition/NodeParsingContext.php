<?php

namespace ALI\TextTemplate\TemplateResolver\Node\Definition;

use ALI\TextTemplate\TemplateResolver\Node\NodeTag;

class NodeParsingContext
{
    private string $content;

    /**
     * @var NodeTag[]
     */
    private array $tags;

    private NodeTag $startTag;
    private NodeTag $endTag;

    /**
     * @var NodeDefinitionInterface[]
     */
    private array $definitions;

    /**
     * @param NodeTag[] $tags
     * @param NodeDefinitionInterface[] $definitions
     */
    public function __construct(
        string $content,
        array $tags,
        NodeTag $startTag,
        NodeTag $endTag,
        array $definitions
    )
    {
        $this->content = $content;
        $this->tags = $tags;
        $this->startTag = $startTag;
        $this->endTag = $endTag;
        $this->definitions = $definitions;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getStartTag(): NodeTag
    {
        return $this->startTag;
    }

    public function getEndTag(): NodeTag
    {
        return $this->endTag;
    }

    public function getInnerContent(): string
    {
        return substr(
            $this->content,
            $this->getInnerContentStart(),
            $this->getInnerContentLength()
        );
    }

    public function getInnerContentStart(): int
    {
        return $this->startTag->getEnd();
    }

    public function getInnerContentLength(): int
    {
        return $this->endTag->getStart() - $this->startTag->getEnd();
    }

    /**
     * @return NodeTag[]
     */
    public function getInnerTags(): array
    {
        $innerTags = [];
        $start = $this->startTag->getEnd();
        $end = $this->endTag->getStart();

        foreach ($this->tags as $tag) {
            if ($tag->getStart() < $start) {
                continue;
            }
            if ($tag->getEnd() > $end) {
                continue;
            }

            $innerTags[] = $tag;
        }

        return $innerTags;
    }

    public function hasTrailingContent(): bool
    {
        $afterEnd = substr($this->content, $this->endTag->getEnd());
        return trim($afterEnd) !== '';
    }

    public function isStartTag(NodeTag $tag): bool
    {
        foreach ($this->definitions as $definition) {
            if ($definition->isStartTag($tag)) {
                return true;
            }
        }

        return false;
    }

    public function isMiddleTag(NodeTag $tag): bool
    {
        foreach ($this->definitions as $definition) {
            if ($definition->isMiddleTag($tag)) {
                return true;
            }
        }

        return false;
    }

    public function isEndTag(NodeTag $tag): bool
    {
        foreach ($this->definitions as $definition) {
            if ($definition->isEndTag($tag)) {
                return true;
            }
        }

        return false;
    }
}
