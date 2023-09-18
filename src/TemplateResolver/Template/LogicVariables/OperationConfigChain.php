<?php

namespace ALI\TextTemplate\TemplateResolver\Template\LogicVariables;

use ALI\TextTemplate\TextTemplatesCollection;

class OperationConfigChain
{
    /**
     * @var OperationConfig[]
     */
    private array $operationConfigs = [];

    public function addOperationConfig(OperationConfig $operationConfig)
    {
        $this->operationConfigs[] = $operationConfig;
    }

    public function run(
        TextTemplatesCollection     $variablesCollection,
        HandlersRepositoryInterface $handlersRepository
    ): ?string
    {
        $previousOperationResult = '';
        foreach ($this->operationConfigs as $operationConfig) {
            $previousOperationResult = $this->runOperation(
                $previousOperationResult,
                $handlersRepository,
                $operationConfig,
                $variablesCollection
            );
            if ($previousOperationResult === null) {
                return null;
            }
        }

        return $previousOperationResult;
    }

    protected function runOperation(
        string                      $previousOperationResult,
        HandlersRepositoryInterface $operatorRepository,
        OperationConfig             $operationConfig,
        TextTemplatesCollection     $variablesCollection
    ): ?string
    {
        $handler = $operatorRepository->find($operationConfig->getHandlerAlias());
        if (!$handler) {
            return null;
        }

        $config = $operationConfig->resolveConfig($variablesCollection);

        return $handler->run($previousOperationResult, $config);
    }

    // TODO add method "getAllVariablesConfigs"
}
