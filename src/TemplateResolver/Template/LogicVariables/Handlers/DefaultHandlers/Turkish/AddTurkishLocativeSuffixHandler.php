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
        $lastVowelType = TurkishFrontAndBackVowelsHelper::getLastVowelType($inputText);
        if (!$lastVowelType) {
            return $inputText;
        }

        if ($lastVowelType === TurkishFrontAndBackVowelsHelper::FRONT) {
            $inputText .= "'de";
        } else {
            $inputText .= "'da";
        }

        return $inputText;
    }
}