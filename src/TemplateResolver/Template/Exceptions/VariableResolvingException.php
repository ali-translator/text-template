<?php

namespace ALI\TextTemplate\TemplateResolver\Template\Exceptions;

use RuntimeException;
use Throwable;

class VariableResolvingException extends RuntimeException
{
    protected string $variableContent;

    public function __construct(
        string    $variableContent,
                  $message,
        Throwable $previous = null
    )
    {
        $this->variableContent = $variableContent;

        parent::__construct($this->variableContent . ': ' . $$message, 0, $previous);
    }

    public function getVariableContent(): string
    {
        return $this->variableContent;
    }
}
