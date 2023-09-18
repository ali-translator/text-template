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
                $explodedParameters = explode(',', $matches['parameters']);

                $operationConfig = [];
                foreach ($explodedParameters as $parameter) {
                    $parameterWithoutSpaces = trim($parameter);
                    $parameterWithoutQuotes = trim($parameterWithoutSpaces,'\'"');
                    if ($parameterWithoutSpaces === $parameterWithoutQuotes) {
                        // Variable id
                        $operationConfig[] = [
                            'type' => 'variable',
                            'value' => $parameterWithoutQuotes,
                        ];
                    } else {
                        // Static data
                        $operationConfig[] = $parameterWithoutQuotes;
                    }
                }
            }

            $handlerConfigsChain->addOperationConfig(
                new OperationConfig($operationName, $operationConfig)
            );
        }

        return new LogicVariableData($handlerConfigsChain);
    }

    // If you only need check "is text is a LogicalVariable", this method will be the fastest, even from "getVariableId"
    public function isTextLogicalVariable(string $logicVariable): bool
    {
        return strpos($logicVariable, $this->operationDelimiter) !== false;
    }
}
