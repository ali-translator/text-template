<?php

namespace ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Common;

use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\HandlerInterface;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Manual\HandlerManualData;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Manual\PipeManualData;

class FirstCharacterInUppercaseHandler implements HandlerInterface
{
    public static function getAlias(): string
    {
        return 'makeFirstCharacterInUppercase';
    }

    public static function getAllowedLanguagesIso(): ?array
    {
        return null;
    }

    public function run(string $pipeInputText, array $config): string
    {
        $firstChar = mb_strtoupper(mb_substr($pipeInputText, 0, 1));

        return $firstChar . mb_substr($pipeInputText, 1);
    }

    public static function generateManual(): HandlerManualData
    {
        $pipeManualData = new PipeManualData(
            true,
            'text',
            'The text that needs its first character to be in uppercase',
            ['hello']
        );

        $argumentManualData = [];

        return new HandlerManualData(
            static::getAlias(),
            static::getAllowedLanguagesIso(),
            'Transforms the first character of the given text to uppercase',
            $argumentManualData,
            $pipeManualData
        );
    }
}
