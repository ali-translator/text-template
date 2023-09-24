<?php

namespace ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Common;

use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\HandlerInterface;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Manual\ArgumentManualData;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Manual\HandlerManualData;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Manual\PipeManualData;

class HideHandler implements HandlerInterface
{
    public static function getAlias(): string
    {
        return 'hide';
    }

    public static function getAllowedLanguagesIso(): ?array
    {
        return null;
    }

    public function run(string $pipeInputText, array $config): string
    {
        // This handler will always return an empty string.
        return '';
    }

    public static function generateManual(): HandlerManualData
    {
        $pipeManualData = new PipeManualData(
            false,
            'text',
            'The text to hide',
            ['hiddenText']
        );

        // Since this handler accepts an arbitrary number of arguments,
        // providing a general description for argumentManualData.
        $argumentManualData = [
            new ArgumentManualData(
                0,
                true,
                'text',
                'Variable you wish to acknowledge without displaying',
                ['hiddenText']
            )
        ];

        return new HandlerManualData(
            static::getAlias(),
            static::getAllowedLanguagesIso(),
            'Accepts variables without displaying them in the text.',
            $argumentManualData,
            $pipeManualData,
        );
    }
}
