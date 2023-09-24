<?php

namespace ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Exceptions;

use RuntimeException;

class UndefinedHandlerException extends RuntimeException
{
    protected string $handlerAlias;

    public function __construct(string $handlerAlias, \Throwable $previous = null)
    {
        $this->handlerAlias = $handlerAlias;
        parent::__construct('Unknown handler "' . $handlerAlias . '". Perhaps it is not registered or does not support selected language.', 0, $previous);
    }

    public function getHandlerAlias(): string
    {
        return $this->handlerAlias;
    }
}
