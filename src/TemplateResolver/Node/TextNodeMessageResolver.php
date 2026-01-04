<?php

namespace ALI\TextTemplate\TemplateResolver\Node;

use ALI\TextTemplate\MessageFormat\MessageFormatsEnum;
use ALI\TextTemplate\TemplateResolver\Node\Exceptions\NodeParsingException;
use ALI\TextTemplate\TemplateResolver\TemplateMessageResolver;
use ALI\TextTemplate\TemplateResolver\TextTemplateMessageResolver;
use ALI\TextTemplate\TextTemplateItem;

class TextNodeMessageResolver implements TemplateMessageResolver
{
    public const OPTION_NODE = 'node';

    private TextTemplateMessageResolver $textTemplateMessageResolver;
    private NodeParser $nodeParser;
    private ConditionEvaluator $conditionEvaluator;
    private bool $silentMode;

    public function __construct(
        TextTemplateMessageResolver $textTemplateMessageResolver,
        NodeParser $nodeParser,
        ConditionEvaluator $conditionEvaluator,
        bool $silentMode = true
    )
    {
        $this->textTemplateMessageResolver = $textTemplateMessageResolver;
        $this->nodeParser = $nodeParser;
        $this->conditionEvaluator = $conditionEvaluator;
        $this->silentMode = $silentMode;
    }

    public function resolve(TextTemplateItem $templateItem): string
    {
        $node = $templateItem->getCustomOptions()[self::OPTION_NODE] ?? null;

        if (!$node instanceof NodeInterface) {
            try {
                $node = $this->nodeParser->parseNodeBlock($templateItem->getContent());
            } catch (NodeParsingException $exception) {
                if ($this->silentMode) {
                    return $templateItem->getContent();
                }

                throw $exception;
            }
        }

        $runtime = new NodeRuntime(
            $this->conditionEvaluator,
            $this->textTemplateMessageResolver,
            $templateItem->getChildTextTemplatesCollection()
        );

        return $node->resolve($runtime);
    }

    public function getFormatName(): string
    {
        return MessageFormatsEnum::TEXT_NODE;
    }
}
