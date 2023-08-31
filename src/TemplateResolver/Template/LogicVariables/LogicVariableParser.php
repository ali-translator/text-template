<?php

namespace ALI\TextTemplate\TemplateResolver\Template\LogicVariables;

class LogicVariableParser
{
    private string $operationDelimiter;

    public function __construct(string $operationDelimiter = '|')
    {
        $this->operationDelimiter = $operationDelimiter;
    }

    public function parse(string $logicVariable): LogicVariableData
    {
        $operations = explode($this->operationDelimiter, $logicVariable);
        $variableId = array_shift($operations);

        $handlerConfigsChain = new OperationConfigChain();

        foreach ($operations as $operation) {

            preg_match('/(?P<handler_alias>[-_a-zA-Z0-9]+)(\((?P<parameters>.*)\)$)?/', $operation, $matches);
            if (!$matches) {
                continue;
            }

            $operationName = $matches['handler_alias'];
            if (empty($matches['parameters'])) {
                $operationConfig = [];
            } else {
                if (!preg_match_all('/"([^"\\\\]*(\\\\.[^"\\\\]*)*)",?/', $matches['parameters'], $matches)) {
                    continue;
                }
                $operationConfig = $matches[1];
            }

            $handlerConfigsChain->addOperationConfig(
                new OperationConfig($operationName, $operationConfig)
            );
        }

        return new LogicVariableData($variableId, $handlerConfigsChain);
    }

    // If you only need check "is text is a LogicalVariable", this method will be the fastest, even from "getVariableId"
    public function isTextLogicalVariable(string $logicVariable): bool
    {
        return strpos($logicVariable, $this->operationDelimiter) !== false;
    }

    // If you only need the "variable id", this method will be the fastest
    public function getVariableId(string $logicVariable): string
    {
        return strtok($logicVariable, $this->operationDelimiter) ?: '';
    }
}
