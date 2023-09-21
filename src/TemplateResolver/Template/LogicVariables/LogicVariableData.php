<?php

namespace ALI\TextTemplate\TemplateResolver\Template\LogicVariables;

use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\HandlersRepositoryInterface;
use ALI\TextTemplate\TextTemplatesCollection;

class LogicVariableData
{
    private HandlerOperationConfigChain $operationConfigChain;

    public function __construct(HandlerOperationConfigChain $operationConfigChain)
    {
        $this->operationConfigChain = $operationConfigChain;
    }

    public function run(
        TextTemplatesCollection     $variablesCollection,
        HandlersRepositoryInterface $handlersRepository
    ): ?string
    {
        return $this->operationConfigChain->run($variablesCollection, $handlersRepository);
    }

    public function getAllPlainVariablesNames(): array
    {
        return $this->operationConfigChain->getAllPlainVariablesNames();
    }
}
