<?php

namespace ALI\TextTemplate\TemplateResolver\Node;

use ALI\TextTemplate\TemplateResolver\Node\Definition\ConditionExpressionProviderInterface;
use ALI\TextTemplate\TemplateResolver\Node\Definition\LoopVariableProviderInterface;
use ALI\TextTemplate\TemplateResolver\Node\Definition\NodeDefinitionInterface;
use ALI\TextTemplate\TemplateResolver\Node\Definition\NodeParsingContext;
use ALI\TextTemplate\TemplateResolver\Node\Exceptions\NodeParsingException;
use ALI\TextTemplate\TemplateResolver\Node\ForNode\ForNodeDefinition;
use ALI\TextTemplate\TemplateResolver\Node\IfNode\IfNodeDefinition;
use ALI\TextTemplate\TemplateResolver\Template\KeyGenerators\KeyGenerator;

class NodeParser
{
    private const TAG_PATTERN = '/{%\s*(?<content>.+?)\s*%}/s';

    private const TAG_TYPE_START = 'start';
    private const TAG_TYPE_END = 'end';
    private const TAG_TYPE_MIDDLE = 'middle';

    private KeyGenerator $keyGenerator;

    /**
     * @var NodeDefinitionInterface[]
     */
    private array $definitions;

    /**
     * @param NodeDefinitionInterface[]|null $definitions
     */
    public function __construct(KeyGenerator $keyGenerator, ?array $definitions = null)
    {
        $this->keyGenerator = $keyGenerator;
        $this->definitions = $definitions ?? [
            new IfNodeDefinition(),
            new ForNodeDefinition(),
        ];
    }

    public function parse(string $content): NodeParseResult
    {
        if (strpos($content, '{%') === false) {
            return new NodeParseResult($content, [], []);
        }

        $tags = $this->collectTags($content);
        if (!$tags) {
            return new NodeParseResult($content, [], []);
        }

        $nodeKeyGenerator = new NodeKeyGenerator();
        $nodes = [];
        $nodeContents = [];
        $ranges = [];
        $stack = [];

        foreach ($tags as $tag) {
            $startDefinition = $this->findDefinitionByType($tag, self::TAG_TYPE_START);
            if ($startDefinition) {
                $stack[] = [
                    'definition' => $startDefinition,
                    'tag' => $tag,
                ];
                continue;
            }

            $endDefinition = $this->findDefinitionByType($tag, self::TAG_TYPE_END);
            if (!$endDefinition) {
                continue;
            }

            if (empty($stack)) {
                throw new NodeParsingException('Unexpected "' . $tag->getName() . '" tag.');
            }

            $openedItem = array_pop($stack);
            $startTag = $openedItem['tag'];
            $openedDefinition = $openedItem['definition'];
            if ($openedDefinition !== $endDefinition) {
                throw new NodeParsingException('Unexpected "' . $tag->getName() . '" tag.');
            }

            if (!empty($stack)) {
                continue;
            }

            $blockStart = $startTag->getStart();
            $blockEnd = $tag->getEnd();
            $blockContent = substr($content, $blockStart, $blockEnd - $blockStart);

            $node = $this->parseNodeBlock($blockContent);
            $nodeId = $nodeKeyGenerator->nextId();

            $nodes[$nodeId] = $node;
            $nodeContents[$nodeId] = $blockContent;
            $ranges[] = [
                self::TAG_TYPE_START => $blockStart,
                self::TAG_TYPE_END => $blockEnd,
                'nodeId' => $nodeId,
            ];
        }

        if (!empty($stack)) {
            $openedItem = end($stack);
            if (is_array($openedItem) && isset($openedItem['definition'])) {
                $openedDefinition = $openedItem['definition'];
                if ($openedDefinition instanceof NodeDefinitionInterface) {
                    throw new NodeParsingException('Missing "' . $openedDefinition->getEndTagName() . '" tag.');
                }
            }
        }

        if (!$nodes) {
            return new NodeParseResult($content, [], []);
        }

        $contentWithPlaceholders = $this->replaceRanges($content, $ranges);

        return new NodeParseResult($contentWithPlaceholders, $nodes, $nodeContents);
    }

    public function parseNodeBlock(string $content): NodeInterface
    {
        $tags = $this->collectTags($content);
        if (!$tags) {
            throw new NodeParsingException('Node tags not found.');
        }

        $startTag = $tags[0];
        if ($startTag->getStart() !== 0) {
            throw new NodeParsingException('Node should start with a node start tag.');
        }

        $definition = $this->findDefinitionByType($startTag, self::TAG_TYPE_START);
        if (!$definition) {
            throw new NodeParsingException('Unsupported node tag "' . $startTag->getName() . '".');
        }

        $endTag = $this->findMatchingEndTag($tags);
        $context = new NodeParsingContext($content, $tags, $startTag, $endTag, $this->definitions);

        return $definition->parse($context);
    }

    /**
     * @return string[]
     */
    public function extractConditionExpressions(string $content): array
    {
        if (strpos($content, '{%') === false) {
            return [];
        }

        $tags = $this->collectTags($content);
        if (!$tags) {
            return [];
        }

        $conditions = [];
        foreach ($this->definitions as $definition) {
            if (!$definition instanceof ConditionExpressionProviderInterface) {
                continue;
            }

            foreach ($definition->getConditionExpressions($tags) as $condition) {
                $conditions[] = $condition;
            }
        }

        return $conditions;
    }

    /**
     * @return string[]
     */
    public function extractLoopVariables(string $content): array
    {
        if (strpos($content, '{%') === false) {
            return [];
        }

        $tags = $this->collectTags($content);
        if (!$tags) {
            return [];
        }

        $variables = [];
        foreach ($this->definitions as $definition) {
            if (!$definition instanceof LoopVariableProviderInterface) {
                continue;
            }

            foreach ($definition->getLoopVariables($tags) as $variable) {
                $variables[] = $variable;
            }
        }

        return $variables;
    }

    /**
     * @return NodeTag[]
     */
    private function collectTags(string $content): array
    {
        if (!preg_match_all(self::TAG_PATTERN, $content, $matches, PREG_OFFSET_CAPTURE)) {
            return [];
        }

        $tags = [];
        foreach ($matches['content'] as $index => $match) {
            $rawContent = $match[0];
            $start = (int)$matches[0][$index][1];
            $fullMatch = $matches[0][$index][0];
            $end = $start + strlen($fullMatch);

            $parsed = $this->parseTagContent($rawContent);
            if (!$parsed) {
                continue;
            }

            $tags[] = new NodeTag(
                $parsed['name'],
                $parsed['arguments'],
                $start,
                $end
            );
        }

        return $tags;
    }

    private function parseTagContent(string $rawContent): ?array
    {
        if (!preg_match('/^\s*(?P<name>[a-zA-Z_][a-zA-Z0-9_-]*)(?:\s+(?P<arguments>.*))?$/s', $rawContent, $matches)) {
            return null;
        }

        return [
            'name' => strtolower($matches['name']),
            'arguments' => $matches['arguments'] ?? null,
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $ranges
     */
    private function replaceRanges(string $content, array $ranges): string
    {
        usort($ranges, function (array $a, array $b) {
            return $a[self::TAG_TYPE_START] <=> $b[self::TAG_TYPE_START];
        });

        $output = '';
        $cursor = 0;

        foreach ($ranges as $range) {
            $output .= substr($content, $cursor, $range[self::TAG_TYPE_START] - $cursor);
            $output .= $this->keyGenerator->generateKey($range['nodeId']);
            $cursor = $range[self::TAG_TYPE_END];
        }

        $output .= substr($content, $cursor);

        return $output;
    }

    private function findDefinitionByType(NodeTag $tag, string $type): ?NodeDefinitionInterface
    {
        $matched = null;
        foreach ($this->definitions as $definition) {
            $isMatch = false;
            if ($type === self::TAG_TYPE_START) {
                $isMatch = $definition->isStartTag($tag);
            } elseif ($type === self::TAG_TYPE_END) {
                $isMatch = $definition->isEndTag($tag);
            } elseif ($type === self::TAG_TYPE_MIDDLE) {
                $isMatch = $definition->isMiddleTag($tag);
            }

            if (!$isMatch) {
                continue;
            }

            if ($matched) {
                throw new NodeParsingException('Tag "' . $tag->getName() . '" is handled by multiple node definitions.');
            }

            $matched = $definition;
        }

        return $matched;
    }

    /**
     * @param NodeTag[] $tags
     */
    private function findMatchingEndTag(array $tags): NodeTag
    {
        $stack = [];
        foreach ($tags as $tag) {
            $startDefinition = $this->findDefinitionByType($tag, self::TAG_TYPE_START);
            if ($startDefinition) {
                $stack[] = $startDefinition;
                continue;
            }

            $endDefinition = $this->findDefinitionByType($tag, self::TAG_TYPE_END);
            if (!$endDefinition) {
                continue;
            }

            if (empty($stack)) {
                throw new NodeParsingException('Unexpected "' . $tag->getName() . '" tag.');
            }

            $openedDefinition = array_pop($stack);
            if ($openedDefinition !== $endDefinition) {
                throw new NodeParsingException('Unexpected "' . $tag->getName() . '" tag.');
            }

            if (empty($stack)) {
                return $tag;
            }
        }

        $missingTagName = self::TAG_TYPE_END;
        if ($stack) {
            $openedDefinition = end($stack);
            if ($openedDefinition instanceof NodeDefinitionInterface) {
                $missingTagName = $openedDefinition->getEndTagName();
            }
        }

        throw new NodeParsingException('Missing "' . $missingTagName . '" tag.');
    }
}
