<?php

namespace ALI\TextTemplate\TemplateResolver\Template\LogicVariables;

class OperationConfig
{
    private string $handlerAlias;
    private array $config;

    public function __construct(string $handlerAlias, array $config = [])
    {
        $this->handlerAlias = $handlerAlias;
        $this->config = $config;
    }

    public function getHandlerAlias(): string
    {
        return $this->handlerAlias;
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}
