<?php

namespace ALI\TextTemplate\TemplateResolver\Node\ForNode;

use ALI\TextTemplate\TemplateResolver\Node\Definition\LoopVariableProviderInterface;
use ALI\TextTemplate\TemplateResolver\Node\Definition\NodeDefinitionInterface;
use ALI\TextTemplate\TemplateResolver\Node\Definition\NodeParsingContext;
use ALI\TextTemplate\TemplateResolver\Node\Exceptions\NodeParsingException;
use ALI\TextTemplate\TemplateResolver\Node\NodeInterface;
use ALI\TextTemplate\TemplateResolver\Node\NodeTag;

class ForNodeDefinition implements NodeDefinitionInterface, LoopVariableProviderInterface
{
    private const TAG_FOR = 'for';
    private const TAG_ENDFOR = 'endfor';

    public function isStartTag(NodeTag $tag): bool
    {
        return $tag->getName() === self::TAG_FOR;
    }

    public function isMiddleTag(NodeTag $tag): bool
    {
        return false;
    }

    public function isEndTag(NodeTag $tag): bool
    {
        return $tag->getName() === self::TAG_ENDFOR;
    }

    public function getEndTagName(): string
    {
        return self::TAG_ENDFOR;
    }

    public function parse(NodeParsingContext $context): NodeInterface
    {
        $expression = trim($context->getStartTag()->getArguments() ?? '');
        if ($expression === '') {
            throw new NodeParsingException('"for" expression is missing.');
        }

        $parsed = $this->parseForExpression($expression);
        if (!$parsed) {
            throw new NodeParsingException('Invalid "for" expression. Expected "{% for item in items %}".');
        }

        if ($context->hasTrailingContent()) {
            throw new NodeParsingException('Unexpected content after "endfor" tag.');
        }

        return new ForNode($parsed['item'], $parsed['collection'], $context->getInnerContent());
    }

    /**
     * @param NodeTag[] $tags
     * @return string[]
     */
    public function getLoopVariables(array $tags): array
    {
        $variables = [];
        foreach ($tags as $tag) {
            if (!$this->isStartTag($tag)) {
                continue;
            }

            $expression = trim($tag->getArguments() ?? '');
            if ($expression === '') {
                continue;
            }

            $parsed = $this->parseForExpression($expression);
            if ($parsed) {
                $variables[] = $parsed['collection'];
            }
        }

        return $variables;
    }

    private function parseForExpression(string $expression): ?array
    {
        if (!preg_match('/^(?P<item>\S+)\s+in\s+(?P<collection>\S+)$/', $expression, $matches)) {
            return null;
        }

        return [
            'item' => trim($matches['item']),
            'collection' => trim($matches['collection']),
        ];
    }
}
