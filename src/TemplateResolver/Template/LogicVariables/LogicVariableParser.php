<?php

namespace ALI\TextTemplate\TemplateResolver\Template\LogicVariables;

use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Exceptions\LogicVariableParsingExcepting;

/**
 * Parser for templates with "Logic variables" like this:
 * 'Розваги {|uk_choosePreposition("Розваги", "в/у", city_name)} {city_name}'
 */
class LogicVariableParser
{
    private string $operationDelimiter;

    public function __construct(
        string $operationDelimiter = '|'
    )
    {
        $this->operationDelimiter = $operationDelimiter;
    }

    /**
     * @throws LogicVariableParsingExcepting
     */
    public function parse(string $logicVariable): LogicVariableData
    {
        $operations = explode($this->operationDelimiter, $logicVariable);
        $handlerConfigsChain = new HandlerOperationConfigChain();

        foreach ($operations as $operation) {
            if (!$operation) {
                continue;
            }
            if (!preg_match('/(?P<handler_alias>[-_a-zA-Z0-9]+)(\((?P<parameters>.*)\)$)?/', $operation, $matches)) {
                throw new LogicVariableParsingExcepting('Invalid syntax in "'.$operation.'" part');
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
                new HandlerOperationConfig($operationName, $operationConfig)
            );
        }

        return new LogicVariableData($handlerConfigsChain);
    }

    public function isTextLogicalVariable(string $logicVariable): bool
    {
        // "strpos" is 4 times faster than "preg_match" for this case
        return strpos($logicVariable, $this->operationDelimiter) === 0;
    }

    public function getOperationDelimiter(): string
    {
        return $this->operationDelimiter;
    }
}
