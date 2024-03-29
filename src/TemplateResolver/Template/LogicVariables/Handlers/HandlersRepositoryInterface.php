<?php

namespace ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers;

interface HandlersRepositoryInterface
{
    public function find(string $alias): ?HandlerInterface;

    /**
     * @return string[]
     */
    public function getAllAliases(): array;

    public function addHandler(HandlerInterface $handler): void;
}
