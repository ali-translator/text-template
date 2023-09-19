<?php

namespace ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Common;

use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\HandlerInterface;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Manual\ArgumentManualData;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Manual\HandlerManualData;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Manual\PipeManualData;

class PrintHandler implements HandlerInterface
{
    public static function getAlias(): string
    {
        return 'print';
    }

    public static function getAllowedLanguagesIso(): ?array
    {
        return null;
    }

    public function run(string $pipeInputText, array $config): string
    {
        return $config[0] ?? $pipeInputText;
    }

    public static function generateManual(): HandlerManualData
    {
        $pipeManualData = new PipeManualData(
            true,
            false,
            'text',
            'The text you want to show',
            ['Hello']
        );

        $argumentManualData = [
            new ArgumentManualData(
                0,
                false,
                'text',
                'The text you want to show',
                ['Hello']
            )
        ];

        return new HandlerManualData(
            static::getAlias(),
            static::getAllowedLanguagesIso(),
            'Print the value of "static"/"plain variable". Can be used as input to another handler function.',
            $pipeManualData,
            $argumentManualData
        );
    }
}
