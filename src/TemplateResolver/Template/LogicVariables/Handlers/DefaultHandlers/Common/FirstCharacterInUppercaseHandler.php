<?php

namespace ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Common;

use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\HandlerInterface;

class FirstCharacterInUppercaseHandler implements HandlerInterface
{
    public function run(string $inputText, array $config): string
    {
        $firstChar = mb_strtoupper(mb_substr($inputText, 0, 1));

        return $firstChar . mb_substr($inputText, 1);
    }

    public static function getAlias(): string
    {
        return 'makeFirstCharacterInUppercase';
    }
}
