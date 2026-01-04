<?php

namespace ALI\TextTemplate\TemplateResolver\Node\Definition;

use ALI\TextTemplate\TemplateResolver\Node\NodeInterface;
use ALI\TextTemplate\TemplateResolver\Node\NodeTag;

interface NodeDefinitionInterface
{
    public function isStartTag(NodeTag $tag): bool;

    public function isMiddleTag(NodeTag $tag): bool;

    public function isEndTag(NodeTag $tag): bool;

    public function getEndTagName(): string;

    public function parse(NodeParsingContext $context): NodeInterface;
}
