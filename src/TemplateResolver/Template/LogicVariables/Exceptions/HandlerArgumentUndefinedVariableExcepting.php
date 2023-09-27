<?php

namespace ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Exceptions;

use RuntimeException;
use Throwable;

class HandlerArgumentUndefinedVariableExcepting extends RuntimeException
{
    protected string $undefinedVariableName;
    protected int $argumentNumber;

    public function __construct(string $undefinedVariableName, int $argumentNumber, Throwable $previous = null)
    {
        $this->undefinedVariableName = $undefinedVariableName;
        $this->argumentNumber = $argumentNumber;

        parent::__construct('The non-existent variable "'.$undefinedVariableName.'" is specified as argument number "'.$argumentNumber.'"', 0, $previous);
    }

    public function getUndefinedVariableName(): string
    {
        return $this->undefinedVariableName;
    }

    public function getArgumentNumber(): int
    {
        return $this->argumentNumber;
    }
}
