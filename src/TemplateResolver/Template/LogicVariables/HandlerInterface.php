<?php

namespace ALI\TextTemplate\TemplateResolver\Template\LogicVariables;

interface HandlerInterface
{
    public static function getAlias(): string;

    public function run(string $inputText, array $config): string;
}
