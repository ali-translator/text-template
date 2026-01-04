<?php

namespace ALI\TextTemplate\TemplateResolver\Node;

use ALI\TextTemplate\TemplateResolver\Node\Exceptions\NodeParsingException;
use ALI\TextTemplate\TemplateResolver\Node\ForNode\ForNode;
use ALI\TextTemplate\TemplateResolver\Node\IfNode\IfNode;
use ALI\TextTemplate\TemplateResolver\Node\IfNode\IfNodeBranch;
use ALI\TextTemplate\TemplateResolver\Template\KeyGenerators\KeyGenerator;

class NodeParser
{
    private const TAG_PATTERN = '/{%\s*(?<content>.+?)\s*%}/s';

    private KeyGenerator $keyGenerator;

    public function __construct(KeyGenerator $keyGenerator)
    {
        $this->keyGenerator = $keyGenerator;
    }

    public function parse(string $content): NodeParseResult
    {
        // Quick check for no nodes
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
            if ($this->isStartTag($tag)) {
                $stack[] = $tag;
                continue;
            }

            if (!$this->isEndTag($tag)) {
                continue;
            }

            if (empty($stack)) {
                throw new NodeParsingException('Unexpected "' . $tag['name'] . '" tag.');
            }

            $startTag = array_pop($stack);
            if (!$this->isMatchingTag($startTag['name'], $tag['name'])) {
                throw new NodeParsingException('Unexpected "' . $tag['name'] . '" tag.');
            }
            if (!empty($stack)) {
                continue;
            }

            $blockStart = $startTag['start'];
            $blockEnd = $tag['end'];
            $blockContent = substr($content, $blockStart, $blockEnd - $blockStart);

            $node = $this->parseNodeBlock($blockContent);
            $nodeId = $nodeKeyGenerator->nextId();

            $nodes[$nodeId] = $node;
            $nodeContents[$nodeId] = $blockContent;
            $ranges[] = [
                'start' => $blockStart,
                'end' => $blockEnd,
                'nodeId' => $nodeId,
            ];
        }

        if (!empty($stack)) {
            $lastTag = end($stack);
            $missingTagName = $lastTag['name'] === 'for' ? 'endfor' : 'endif';
            throw new NodeParsingException('Missing "' . $missingTagName . '" tag.');
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

        $firstTag = $tags[0];
        if ($firstTag['start'] !== 0) {
            throw new NodeParsingException('Node should start with "{% if %}" or "{% for %}" tag.');
        }

        if ($firstTag['name'] === 'if') {
            return $this->parseIfNodeBlock($content, $firstTag, $tags);
        }

        if ($firstTag['name'] === 'for') {
            return $this->parseForNodeBlock($content, $firstTag, $tags);
        }

        throw new NodeParsingException('Unsupported node tag "' . $firstTag['name'] . '".');
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
        foreach ($tags as $tag) {
            if ($tag['name'] !== 'if' && $tag['name'] !== 'elseif') {
                continue;
            }

            $condition = trim($tag['arguments'] ?? '');
            if ($condition !== '') {
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
        foreach ($tags as $tag) {
            if ($tag['name'] !== 'for') {
                continue;
            }

            $expression = trim($tag['arguments'] ?? '');
            if ($expression === '') {
                continue;
            }

            $parsed = $this->tryParseForExpression($expression);
            if (!$parsed) {
                continue;
            }

            $variables[] = $parsed['collection'];
        }

        return $variables;
    }

    /**
     * @param string $innerContent
     * @return IfNodeBranch[]
     */
    private function parseBranches(string $innerContent, string $ifCondition): array
    {
        $branches = [];
        $cursor = 0;
        $currentCondition = $ifCondition;
        $depth = 0;
        $elseSeen = false;

        $tags = $this->collectTags($innerContent);
        foreach ($tags as $tag) {
            if ($this->isStartTag($tag)) {
                $depth++;
                continue;
            }

            if ($this->isEndTag($tag)) {
                if ($depth > 0) {
                    $depth--;
                }
                continue;
            }

            if ($depth !== 0) {
                continue;
            }

            if ($tag['name'] !== 'elseif' && $tag['name'] !== 'else') {
                continue;
            }

            $branchContent = substr($innerContent, $cursor, $tag['start'] - $cursor);
            $branches[] = new IfNodeBranch($currentCondition, $branchContent);

            if ($tag['name'] === 'else') {
                if ($elseSeen) {
                    throw new NodeParsingException('Multiple "else" tags are not allowed.');
                }

                $currentCondition = null;
                $elseSeen = true;
            } else {
                if ($elseSeen) {
                    throw new NodeParsingException('"elseif" tag cannot appear after "else".');
                }

                $currentCondition = trim($tag['arguments'] ?? '');
                if ($currentCondition === '') {
                    throw new NodeParsingException('"elseif" condition is missing.');
                }
            }

            $cursor = $tag['end'];
        }

        $branches[] = new IfNodeBranch($currentCondition, substr($innerContent, $cursor));

        return $branches;
    }

    private function parseIfNodeBlock(string $content, array $firstTag, array $tags): IfNode
    {
        $ifCondition = trim($firstTag['arguments'] ?? '');
        if ($ifCondition === '') {
            throw new NodeParsingException('"if" condition is missing.');
        }

        $endTag = $this->findEndTag($tags);
        if ($endTag['name'] !== 'endif') {
            throw new NodeParsingException('Missing "endif" tag.');
        }

        $this->ensureNoTrailingContent($content, $endTag, 'endif');

        $innerContent = substr($content, $firstTag['end'], $endTag['start'] - $firstTag['end']);
        $branches = $this->parseBranches($innerContent, $ifCondition);

        return new IfNode($branches);
    }

    private function parseForNodeBlock(string $content, array $firstTag, array $tags): ForNode
    {
        $expression = trim($firstTag['arguments'] ?? '');
        if ($expression === '') {
            throw new NodeParsingException('"for" expression is missing.');
        }

        $parsed = $this->tryParseForExpression($expression);
        if (!$parsed) {
            throw new NodeParsingException('Invalid "for" expression. Expected "{% for item in items %}".');
        }

        $endTag = $this->findEndTag($tags);
        if ($endTag['name'] !== 'endfor') {
            throw new NodeParsingException('Missing "endfor" tag.');
        }

        $this->ensureNoTrailingContent($content, $endTag, 'endfor');

        $innerContent = substr($content, $firstTag['end'], $endTag['start'] - $firstTag['end']);

        return new ForNode($parsed['item'], $parsed['collection'], $innerContent);
    }

    /**
     * @return array<int, array<string, mixed>>
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

            $tags[] = [
                'name' => $parsed['name'],
                'arguments' => $parsed['arguments'],
                'start' => $start,
                'end' => $end,
            ];
        }

        return $tags;
    }

    private function parseTagContent(string $rawContent): ?array
    {
        if (!preg_match('/^\s*(?P<name>[a-zA-Z_][a-zA-Z0-9_-]*)(?:\s+(?P<arguments>.*))?$/s', $rawContent, $matches)) {
            return null;
        }

        $name = strtolower($matches['name']);
        // TODO move to constants to the Specific Node
        if (!in_array($name, ['if', 'elseif', 'else', 'endif', 'for', 'endfor'], true)) {
            return null;
        }

        return [
            'name' => $name,
            'arguments' => $matches['arguments'] ?? null,
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $ranges
     */
    private function replaceRanges(string $content, array $ranges): string
    {
        usort($ranges, function (array $a, array $b) {
            return $a['start'] <=> $b['start'];
        });

        $output = '';
        $cursor = 0;

        foreach ($ranges as $range) {
            $output .= substr($content, $cursor, $range['start'] - $cursor);
            $output .= $this->keyGenerator->generateKey($range['nodeId']);
            $cursor = $range['end'];
        }

        $output .= substr($content, $cursor);

        return $output;
    }

    private function isStartTag(array $tag): bool
    {
        return in_array($tag['name'], ['if', 'for'], true);
    }

    private function isEndTag(array $tag): bool
    {
        return in_array($tag['name'], ['endif', 'endfor'], true);
    }

    private function isMatchingTag(string $startName, string $endName): bool
    {
        return ($startName === 'if' && $endName === 'endif')
            || ($startName === 'for' && $endName === 'endfor');
    }

    /**
     * @param array<int, array<string, mixed>> $tags
     */
    private function findEndTag(array $tags): array
    {
        $stack = [];
        foreach ($tags as $tag) {
            if ($this->isStartTag($tag)) {
                $stack[] = $tag['name'];
                continue;
            }

            if (!$this->isEndTag($tag)) {
                continue;
            }

            if (empty($stack)) {
                throw new NodeParsingException('Unexpected "' . $tag['name'] . '" tag.');
            }

            $startName = array_pop($stack);
            if (!$this->isMatchingTag($startName, $tag['name'])) {
                throw new NodeParsingException('Unexpected "' . $tag['name'] . '" tag.');
            }

            if (empty($stack)) {
                return $tag;
            }
        }

        $missingTagName = 'endif';
        if ($stack) {
            $last = end($stack);
            $missingTagName = $last === 'for' ? 'endfor' : 'endif';
        }

        throw new NodeParsingException('Missing "' . $missingTagName . '" tag.');
    }

    private function ensureNoTrailingContent(string $content, array $endTag, string $tagName): void
    {
        $afterEnd = substr($content, $endTag['end']);
        if (trim($afterEnd) !== '') {
            throw new NodeParsingException('Unexpected content after "' . $tagName . '" tag.');
        }
    }

    private function tryParseForExpression(string $expression): ?array
    {
        if (!preg_match('/^(?P<item>\\S+)\\s+in\\s+(?P<collection>\\S+)$/', $expression, $matches)) {
            return null;
        }

        return [
            'item' => trim($matches['item']),
            'collection' => trim($matches['collection']),
        ];
    }
}
