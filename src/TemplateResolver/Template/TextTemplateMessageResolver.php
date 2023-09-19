<?php

namespace ALI\TextTemplate\TemplateResolver\Template;

use ALI\TextTemplate\MessageFormat\MessageFormatsEnum;
use ALI\TextTemplate\TemplateResolver\Template\KeyGenerators\KeyGenerator;
use ALI\TextTemplate\TemplateResolver\Template\KeyGenerators\TextKeysHandler;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\DefaultHandlersFacade;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\HandlersRepository;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\HandlersRepositoryInterface;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\LogicVariableData;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\LogicVariableParser;
use ALI\TextTemplate\TemplateResolver\TemplateMessageResolver;
use ALI\TextTemplate\TextTemplateItem;

class TextTemplateMessageResolver implements TemplateMessageResolver
{
    private KeyGenerator $keyGenerator;
    private TextKeysHandler $textKeysHandler;
    private ?LogicVariableParser $logicVariableParser;
    private ?HandlersRepositoryInterface $handlersRepository;

    public function __construct(
        KeyGenerator                $keyGenerator,
        HandlersRepositoryInterface $logicVariableHandlersRepository,
        LogicVariableParser         $logicVariableParser
    )
    {
        $this->keyGenerator = $keyGenerator;
        $this->textKeysHandler = new TextKeysHandler();

        if (!$logicVariableHandlersRepository) {
            $logicVariableHandlersRepository = (new DefaultHandlersFacade())->registerHandlers(
                new HandlersRepository(),
                null
            );
        }
        $this->handlersRepository = $logicVariableHandlersRepository;

        if (!$logicVariableParser) {
            $logicVariableParser = new LogicVariableParser('|');
        }
        $this->logicVariableParser = $logicVariableParser;
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
                    $logicVariableData = $this->logicVariableParser->parse($variableContent);
                    return $logicVariableData
                        ->run(
                            $childTextTemplatesCollection,
                            $this->handlersRepository
                        );
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
                $allVariables[] = $this->logicVariableParser->parse($templateKey);
                continue;
            }

            // Plain template variable
            $allVariables[] = $templateKey;
        }

        return $allVariables;
    }
}
