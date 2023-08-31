<?php

namespace ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers;

use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\HandlerInterface;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\HandlersRepositoryInterface;

class HandlersRepository implements HandlersRepositoryInterface
{
    /**
     * @var HandlerInterface[]
     */
    private array $handlers = [];

    public function addHandler(HandlerInterface $handler): void
    {
        $this->handlers[$handler->getAlias()] = $handler;
    }

    public function getAllAliases(): array
    {
        return array_keys($this->handlers);
    }

    public function find(string $alias): ?HandlerInterface
    {
        return $this->handlers[$alias] ?? null;
    }
}
