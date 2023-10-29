<?php

namespace ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Turkish\Services;

class LocativeSuffixChooser
{
    public function choose(string $locative): ?string
    {
        $lastVowelType = TurkishFrontAndBackVowelsHelper::getLastVowelType($locative);
        if (!$lastVowelType) {
            return null;
        }

        if ($lastVowelType === TurkishFrontAndBackVowelsHelper::FRONT) {
            return "'de";
        }

        return "'da";
    }
}
