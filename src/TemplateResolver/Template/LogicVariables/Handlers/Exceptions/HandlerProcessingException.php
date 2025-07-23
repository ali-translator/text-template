<?php

namespace ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Exceptions;

use RuntimeException;
use Throwable;

class HandlerProcessingException extends RuntimeException
{
    protected string $handlerAlias;

    public function __construct(string $handlerAlias, string $message, ?Throwable $previous = null)
    {
        $this->handlerAlias = $handlerAlias;
        parent::__construct('Handler "' . $handlerAlias . '": ' . $message, 0, $previous);
    }

    public function getHandlerAlias(): string
    {
        return $this->handlerAlias;
    }
}
