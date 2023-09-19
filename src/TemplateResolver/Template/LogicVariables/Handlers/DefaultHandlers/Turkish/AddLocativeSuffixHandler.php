<?php

namespace ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Turkish;

use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\HandlerInterface;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Manual\ArgumentManualData;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Manual\HandlerManualData;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Manual\PipeManualData;

class AddLocativeSuffixHandler implements HandlerInterface
{
    public static function getAlias(): string
    {
        return 'tr_addLocativeSuffix';
    }

    public static function getAllowedLanguagesIso(): ?array
    {
        return ['tr'];
    }

    public function run(string $pipeInputText, array $config): string
    {
        $locative = $config[0] ?? $pipeInputText;
        if (!$locative) {
            return '';
        }
        $lastVowelType = TurkishFrontAndBackVowelsHelper::getLastVowelType($locative);
        if (!$lastVowelType) {
            return $locative;
        }

        if ($lastVowelType === TurkishFrontAndBackVowelsHelper::FRONT) {
            $locative .= "'de";
        } else {
            $locative .= "'da";
        }

        return $locative;
    }

    public static function generateManual(): HandlerManualData
    {
        $pipeManualData = new PipeManualData(
            true,
            false,
            'locative',
            'The base word to which the locative suffix should be added'
        );

        $argumentManualData = [
            new ArgumentManualData(0, false, 'locative', 'The base word to which the locative suffix should be added',[
                'İstanbul',
                'Yalova'
            ])
        ];

        return new HandlerManualData(
            static::getAlias(),
            static::getAllowedLanguagesIso(),
            'Adds the appropriate locative suffix ("\'de" or "\'da") to the given word based on vowel harmony. Specific to the Turkish language.',
            $pipeManualData,
            $argumentManualData
        );
    }
}
