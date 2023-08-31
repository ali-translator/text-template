<?php

namespace ALI\TextTemplate\TemplateResolver\Template\LogicVariables;

class LogicVariableData
{
    private string $variableName;
    private OperationConfigChain $operationConfigChain;

    public function __construct(string $variableName, OperationConfigChain $operationConfigChain)
    {
        $this->variableName = $variableName;
        $this->operationConfigChain = $operationConfigChain;
    }

    public function getVariableName(): string
    {
        return $this->variableName;
    }

    public function getOperationConfigChain(): OperationConfigChain
    {
        return $this->operationConfigChain;
    }
}
