<?php

namespace ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Turkish\Services;

class DirectionalSuffixChooser
{
    public function choose(string $directional): ?string
    {
        $lastVowelType = TurkishFrontAndBackVowelsHelper::getLastVowelType($directional);
        if (!$lastVowelType) {
            return null;
        }

        // Applying Turkish vowel harmony rules for directional suffixes
        switch ($lastVowelType) {
            case TurkishFrontAndBackVowelsHelper::FRONT:
                $suffix = mb_substr($directional, -1) === 'e' ? 'ye' : 'e';
                break;
            default:
                $suffix = mb_substr($directional, -1) === 'a' ? 'ya' : 'a';
                break;
        }

        return $suffix;
    }
}
