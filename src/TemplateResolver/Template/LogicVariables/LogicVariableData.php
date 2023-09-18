<?php

namespace ALI\TextTemplate\TemplateResolver\Template\LogicVariables;

use ALI\TextTemplate\TextTemplatesCollection;

class LogicVariableData
{
    private OperationConfigChain $operationConfigChain;

    public function __construct(OperationConfigChain $operationConfigChain)
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
}
