<?php

namespace ALI\TextTemplate\TemplateResolver\Node\IfNode;

use ALI\TextTemplate\TemplateResolver\Node\Definition\ConditionExpressionProviderInterface;
use ALI\TextTemplate\TemplateResolver\Node\Definition\NodeDefinitionInterface;
use ALI\TextTemplate\TemplateResolver\Node\Definition\NodeParsingContext;
use ALI\TextTemplate\TemplateResolver\Node\Exceptions\NodeParsingException;
use ALI\TextTemplate\TemplateResolver\Node\NodeInterface;
use ALI\TextTemplate\TemplateResolver\Node\NodeTag;

class IfNodeDefinition implements NodeDefinitionInterface, ConditionExpressionProviderInterface
{
    private const TAG_IF = 'if';
    private const TAG_ELSEIF = 'elseif';
    private const TAG_ELSE = 'else';
    private const TAG_ENDIF = 'endif';

    public function isStartTag(NodeTag $tag): bool
    {
        return $tag->getName() === self::TAG_IF;
    }

    public function isMiddleTag(NodeTag $tag): bool
    {
        return in_array($tag->getName(), [self::TAG_ELSEIF, self::TAG_ELSE], true);
    }

    public function isEndTag(NodeTag $tag): bool
    {
        return $tag->getName() === self::TAG_ENDIF;
    }

    public function getEndTagName(): string
    {
        return self::TAG_ENDIF;
    }

    public function parse(NodeParsingContext $context): NodeInterface
    {
        $condition = trim($context->getStartTag()->getArguments() ?? '');
        if ($condition === '') {
            throw new NodeParsingException('"if" condition is missing.');
        }

        if ($context->hasTrailingContent()) {
            throw new NodeParsingException('Unexpected content after "endif" tag.');
        }

        $branches = $this->parseBranches($context, $condition);

        return new IfNode($branches);
    }

    /**
     * @param NodeTag[] $tags
     * @return string[]
     */
    public function getConditionExpressions(array $tags): array
    {
        $conditions = [];
        foreach ($tags as $tag) {
            if ($this->isStartTag($tag) || $this->isElseIfTag($tag)) {
                $condition = trim($tag->getArguments() ?? '');
                if ($condition !== '') {
                    $conditions[] = $condition;
                }
            }
        }

        return $conditions;
    }

    /**
     * @return IfNodeBranch[]
     */
    private function parseBranches(NodeParsingContext $context, string $ifCondition): array
    {
        $branches = [];
        $cursor = 0;
        $currentCondition = $ifCondition;
        $depth = 0;
        $elseSeen = false;
        $innerContent = $context->getInnerContent();
        $innerStart = $context->getInnerContentStart();

        foreach ($context->getInnerTags() as $tag) {
            if ($context->isStartTag($tag)) {
                $depth++;
                continue;
            }

            if ($context->isEndTag($tag)) {
                if ($depth > 0) {
                    $depth--;
                }
                continue;
            }

            if ($depth !== 0) {
                continue;
            }

            if (!$this->isMiddleTag($tag)) {
                continue;
            }

            $branchContent = substr($innerContent, $cursor, ($tag->getStart() - $innerStart) - $cursor);
            $branches[] = new IfNodeBranch($currentCondition, $branchContent);

            if ($this->isElseTag($tag)) {
                if ($elseSeen) {
                    throw new NodeParsingException('Multiple "else" tags are not allowed.');
                }

                $currentCondition = null;
                $elseSeen = true;
            } else {
                if ($elseSeen) {
                    throw new NodeParsingException('"elseif" tag cannot appear after "else".');
                }

                $currentCondition = trim($tag->getArguments() ?? '');
                if ($currentCondition === '') {
                    throw new NodeParsingException('"elseif" condition is missing.');
                }
            }

            $cursor = $tag->getEnd() - $innerStart;
        }

        $branches[] = new IfNodeBranch($currentCondition, substr($innerContent, $cursor));

        return $branches;
    }

    private function isElseIfTag(NodeTag $tag): bool
    {
        return $tag->getName() === self::TAG_ELSEIF;
    }

    private function isElseTag(NodeTag $tag): bool
    {
        return $tag->getName() === self::TAG_ELSE;
    }
}
