<?php

namespace ALI\TextTemplate\TemplateResolver\Template;

use ALI\TextTemplate\MessageFormat\MessageFormatsEnum;
use ALI\TextTemplate\TemplateResolver\Template\Exceptions\VariableResolvingException;
use ALI\TextTemplate\TemplateResolver\Template\KeyGenerators\KeyGenerator;
use ALI\TextTemplate\TemplateResolver\Template\KeyGenerators\TextKeysHandler;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Exceptions\LogicVariableParsingExcepting;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\HandlersRepositoryInterface;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\LogicVariableData;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\LogicVariableParser;
use ALI\TextTemplate\TemplateResolver\TemplateMessageResolver;
use ALI\TextTemplate\TextTemplateItem;
use Exception;

class TextTemplateMessageResolver implements TemplateMessageResolver
{
    private KeyGenerator $keyGenerator;
    private TextKeysHandler $textKeysHandler;
    private ?LogicVariableParser $logicVariableParser;
    private ?HandlersRepositoryInterface $handlersRepository;
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
        $this->silentMode = $silentMode;
    }

    public function getFormatName(): string
    {
        return MessageFormatsEnum::TEXT_TEMPLATE;
    }

    public function resolve(TextTemplateItem $templateItem): string
    {
        $childTextTemplatesCollection = $templateItem->getChildTextTemplatesCollection();
        if (!$childTextTemplatesCollection) {
            return $templateItem->getContent();
        }
        return $this->textKeysHandler->replaceKeys(
            $this->keyGenerator,
            $templateItem->getContent(),
            function (string $variableContent) use (
                $childTextTemplatesCollection,
                $templateItem
            ) {
                // Plain template variable
                $variableId = $variableContent;
                $childValue = $childTextTemplatesCollection->get($variableId);
                if ($childValue) {
                    return $childValue->resolve();
                }

                // Logic variable with additional handlers operations
                if ($this->logicVariableParser->isTextLogicalVariable($variableContent)) {
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

                if (!$this->silentMode) {
                    throw new VariableResolvingException($variableContent, 'Undefined variable');
                }

                return null;
            }
        );
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
}
