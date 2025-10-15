<?php

namespace ALI\TextTemplate\TemplateResolver\Template\LogicVariables;

use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Exceptions\UndefinedHandlerException;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\HandlersRepositoryInterface;
use ALI\TextTemplate\TextTemplatesCollection;

class HandlerOperationConfigChain
{
    /**
     * @var HandlerOperationConfig[]
     */
    private array $operationConfigs = [];

    public function addOperationConfig(HandlerOperationConfig $operationConfig)
    {
        $this->operationConfigs[] = $operationConfig;
    }

    public function run(
        TextTemplatesCollection     $variablesCollection,
        HandlersRepositoryInterface $handlersRepository
    ): string
    {
        $previousOperationResult = '';
        foreach ($this->operationConfigs as $operationConfig) {
            $previousOperationResult = $this->runOperation(
                $previousOperationResult,
                $handlersRepository,
                $operationConfig,
                $variablesCollection
            );
        }

        return $previousOperationResult;
    }

    protected function runOperation(
        string                      $previousOperationResult,
        HandlersRepositoryInterface $operatorRepository,
        HandlerOperationConfig      $operationConfig,
        TextTemplatesCollection     $variablesCollection
    ): string
    {
        $handler = $operatorRepository->find($operationConfig->getHandlerAlias());
        if (!$handler) {
            throw new UndefinedHandlerException($operationConfig->getHandlerAlias());
        }

        $config = $operationConfig->resolveConfig($variablesCollection);

        return $handler->run($previousOperationResult, $config);
    }

    /**
     * @return string[]
     */
    public function getAllPlainVariablesNames(): array
    {
        $plainVariablesNames = [];
        foreach ($this->operationConfigs as $operationConfig) {
            $plainVariablesNames[] = $operationConfig->getAllPlainVariablesNames();
        }
        if ($plainVariablesNames) {
            $plainVariablesNames = array_merge(...$plainVariablesNames);
            $plainVariablesNames = array_unique($plainVariablesNames);
        }

        return $plainVariablesNames;
    }

    /**
     * @return HandlerOperationConfig[]
     */
    public function getOperationConfigs(): array
    {
        return $this->operationConfigs;
    }
}
