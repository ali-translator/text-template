<?php

namespace ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Turkish;

use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\HandlerInterface;

class AddTurkishLocativeSuffixHandler implements HandlerInterface
{
    public static function getAlias(): string
    {
        return 'TR_addLocativeSuffix';
    }

    public function run(string $inputText, array $config): string
    {
        $locative = $config[0] ?? $inputText;
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
}
