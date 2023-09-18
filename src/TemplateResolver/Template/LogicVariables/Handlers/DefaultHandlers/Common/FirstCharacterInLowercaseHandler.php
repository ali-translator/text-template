<?php

namespace ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Common;

use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\HandlerInterface;

class FirstCharacterInLowercaseHandler implements HandlerInterface
{
    public static function getAlias(): string
    {
        return 'makeFirstCharacterInLowercase';
    }

    public function run(string $inputText, array $config): string
    {
        $firstChar = mb_strtolower(mb_substr($inputText, 0, 1));

        return $firstChar . mb_substr($inputText, 1);
    }
}
