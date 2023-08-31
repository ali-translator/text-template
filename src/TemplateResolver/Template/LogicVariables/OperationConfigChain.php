<?php

namespace ALI\TextTemplate\TemplateResolver\Template\LogicVariables;

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
        string                      $variableValue,
        HandlersRepositoryInterface $handlersRepository
    ): ?string
    {
        foreach ($this->operationConfigs as $operationConfig) {
            $variableValue = $this->runOperation($handlersRepository, $operationConfig, $variableValue);
            if ($variableValue === null) {
                return null;
            }
        }

        return $variableValue;
    }

    protected function runOperation(
        HandlersRepositoryInterface $operatorRepository,
        OperationConfig             $operationConfig,
        string                      $variableValue
    ): ?string
    {
        $handler = $operatorRepository->find($operationConfig->getHandlerAlias());
        if (!$handler) {
            return null;
        }

        return $handler->run($variableValue, $operationConfig->getConfig());
    }
}
