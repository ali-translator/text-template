<?php

namespace ALI\TextTemplate\TemplateResolver\Template\PlainVariables;

interface PlainVariablesUsageResultInterface
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;

    /**
     * @return array<string, PlainVariableUsageDto>
     */
    public function toDtoMap(): array;
}
