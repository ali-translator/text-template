<?php

namespace ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Common;

use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\HandlerInterface;

class PrintHandler implements HandlerInterface
{
    public static function getAlias(): string
    {
        return 'print';
    }

    public function run(string $inputText, array $config): string
    {
        return $config[0] ?? '';
    }
}
