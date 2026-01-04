<?php

namespace ALI\TextTemplate\TemplateResolver;

use ALI\TextTemplate\MessageFormat\MessageFormatsEnum;
use ALI\TextTemplate\TemplateResolver\Node\ConditionEvaluator;
use ALI\TextTemplate\TemplateResolver\Node\Exceptions\NodeParsingException;
use ALI\TextTemplate\TemplateResolver\Node\NodeParser;
use ALI\TextTemplate\TemplateResolver\Node\TextNodeMessageResolver;
use ALI\TextTemplate\TemplateResolver\Template\Exceptions\VariableResolvingException;
use ALI\TextTemplate\TemplateResolver\Template\KeyGenerators\KeyGenerator;
use ALI\TextTemplate\TemplateResolver\Template\KeyGenerators\TextKeysHandler;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Exceptions\LogicVariableParsingExcepting;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\HandlersRepositoryInterface;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\LogicVariableData;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\LogicVariableParser;
use ALI\TextTemplate\TextTemplateItem;
use ALI\TextTemplate\TextTemplatesCollection;
use Exception;

class TextTemplateMessageResolver implements TemplateMessageResolver
{
    private KeyGenerator $keyGenerator;
    private TextKeysHandler $textKeysHandler;
    private ?LogicVariableParser $logicVariableParser;
    private ?HandlersRepositoryInterface $handlersRepository;
    private NodeParser $nodeParser;
    private ConditionEvaluator $conditionEvaluator;
    private ?TextNodeMessageResolver $textNodeMessageResolver = null;
    // "SilentMode" will catch all parser errors and not pass them to you
    private bool $silentMode;

    public function __construct(
        KeyGenerator                $keyGenerator,
        HandlersRepositoryInterface $logicVariableHandlersRepository,
        LogicVariableParser         $logicVariableParser,
        bool                        $silentMode = true
    )
    {
        $this->keyGenerator = $keyGenerator;
        $this->textKeysHandler = new TextKeysHandler();
        $this->handlersRepository = $logicVariableHandlersRepository;
        $this->logicVariableParser = $logicVariableParser;
        $this->conditionEvaluator = new ConditionEvaluator();
        $this->nodeParser = new NodeParser($keyGenerator);
        $this->silentMode = $silentMode;
    }

    public function getFormatName(): string
    {
        return MessageFormatsEnum::TEXT_TEMPLATE;
    }

    public function resolve(TextTemplateItem $templateItem): string
    {
        $content = $templateItem->getContent();
        $childTextTemplatesCollection = $templateItem->getChildTextTemplatesCollection();
        $workingTextTemplatesCollection = $childTextTemplatesCollection;

        $nodeParseResult = null;
        if (strpos($content, '{%') !== false) {
            try {
                $nodeParseResult = $this->nodeParser->parse($content);
            } catch (NodeParsingException $exception) {
                if (!$this->silentMode) {
                    throw $exception;
                }
            }
        }

        if ($nodeParseResult && $nodeParseResult->hasNodes()) {
            $content = $nodeParseResult->getContent();
            if ($workingTextTemplatesCollection) {
                $workingTextTemplatesCollection = clone $workingTextTemplatesCollection;
            } else {
                $workingTextTemplatesCollection = new TextTemplatesCollection();
            }

            foreach ($nodeParseResult->getNodes() as $nodeId => $node) {
                $nodeContent = $nodeParseResult->getNodeContent($nodeId) ?? '';
                $nodeTemplateItem = new TextTemplateItem(
                    $nodeContent,
                    $workingTextTemplatesCollection,
                    $this->getTextNodeMessageResolver(),
                    [
                        TextNodeMessageResolver::OPTION_NODE => $node
                    ]
                );
                $workingTextTemplatesCollection->add($nodeTemplateItem, $nodeId);
            }
        }

        // Without any variables
        if (!$workingTextTemplatesCollection) {
            return $content;
        }

        // Has variables/logic variables/nodes
        return $this->textKeysHandler->replaceKeys(
            $this->keyGenerator,
            $content,
            function (string $variableContent) use (
                $workingTextTemplatesCollection,
                $templateItem
            ) {
                // Plain template variable (including nodes)
                $variableId = $variableContent;
                $childValue = $workingTextTemplatesCollection->get($variableId);
                if ($childValue) {
                    return $childValue->resolve();
                }

                // Logic variable with additional handlers operations
                if ($this->logicVariableParser->isTextLogicalVariable($variableContent)) {
                    return $this->resolveLogicVariable($variableContent, $workingTextTemplatesCollection);
                }

                //dd('Undefined variable:' . $variableContent);

                if (!$this->silentMode) {
                    throw new VariableResolvingException($variableContent, 'Undefined variable');
                }

                return null;
            }
        );
    }

    private function resolveLogicVariable(
        string $variableContent,
        TextTemplatesCollection $childTextTemplatesCollection
    ): ?string
    {
        try {
            $logicVariableData = $this->logicVariableParser->parse($variableContent);
        } catch (LogicVariableParsingExcepting $excepting) {
            if (!$this->silentMode) {
                throw new VariableResolvingException($variableContent, $excepting->getMessage());
            }
        }

        if (!empty($logicVariableData)) {
            try {
                $resolvedLogicalVariable = $logicVariableData
                    ->run(
                        $childTextTemplatesCollection,
                        $this->handlersRepository
                    );
            } catch (Exception $exception) {
            }
        }

        if (!isset($resolvedLogicalVariable) && !$this->silentMode) {
            throw new VariableResolvingException($variableContent, $exception->getMessage());
        }

        return $resolvedLogicalVariable ?? null;
    }

    public function getAllUsedPlainVariables(string $content): array
    {
        $allVariables = $this->parseAllVariables($content);

        $allPlainVariablesNames = [];
        foreach ($allVariables as $variable) {
            if (is_string($variable)) {
                $allPlainVariablesNames[$variable] = $variable;
            } elseif ($variable instanceof LogicVariableData) {
                foreach ($variable->getAllPlainVariablesNames() as $plainVariableName) {
                    $allPlainVariablesNames[$plainVariableName] = $plainVariableName;
                }
            }
        }

        $nodeConditions = $this->nodeParser->extractConditionExpressions($content);
        foreach ($nodeConditions as $nodeCondition) {
            foreach ($this->conditionEvaluator->getUsedVariables($nodeCondition) as $plainVariableName) {
                $allPlainVariablesNames[$plainVariableName] = $plainVariableName;
            }
        }

        $loopVariables = $this->nodeParser->extractLoopVariables($content);
        foreach ($loopVariables as $loopVariableName) {
            $allPlainVariablesNames[$loopVariableName] = $loopVariableName;
        }

        return $allPlainVariablesNames;
    }

    protected function parseAllVariables(string $content): array
    {
        $allKeys = $this->textKeysHandler->getAllKeys(
            $this->keyGenerator,
            $content
        );

        $allVariables = [];
        foreach ($allKeys as $templateKey) {
            if ($this->isNodeTagKey($templateKey)) {
                continue;
            }
            // Logic variable with additional handlers operations
            if ($this->logicVariableParser->isTextLogicalVariable($templateKey)) {
                try {
                    $allVariables[] = $this->logicVariableParser->parse($templateKey);
                } catch (LogicVariableParsingExcepting $excepting) {
                    if (!$this->silentMode) {
                        throw new VariableResolvingException($templateKey, $excepting->getMessage());
                    }
                }
                continue;
            }

            // Plain template variable
            $allVariables[] = $templateKey;
        }

        return $allVariables;
    }

    private function isNodeTagKey(string $templateKey): bool
    {
        $trimmedKey = trim($templateKey);
        if ($trimmedKey === '') {
            return false;
        }

        return $trimmedKey[0] === '%' && substr($trimmedKey, -1) === '%';
    }

    private function getTextNodeMessageResolver(): TextNodeMessageResolver
    {
        if (!$this->textNodeMessageResolver) {
            $this->textNodeMessageResolver = new TextNodeMessageResolver(
                $this,
                $this->nodeParser,
                $this->conditionEvaluator,
                $this->silentMode
            );
        }

        return $this->textNodeMessageResolver;
    }
}
