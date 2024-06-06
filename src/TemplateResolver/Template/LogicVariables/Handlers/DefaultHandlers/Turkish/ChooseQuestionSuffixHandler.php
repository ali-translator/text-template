<?php

namespace ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Turkish;

use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Turkish\Services\TurkishFrontAndBackVowelsHelper;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Exceptions\HandlerProcessingException;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\HandlerInterface;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Manual\ArgumentManualData;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Manual\HandlerManualData;

class ChooseQuestionSuffixHandler implements HandlerInterface
{
    public static function getAlias(): string
    {
        return 'tr_chooseQuestionSuffix';
    }

    public static function getAllowedLanguagesIso(): ?array
    {
        return ['tr'];
    }

    public function run(string $pipeInputText, array $config): string
    {
        $baseWord = $config[0] ?? null;

        if ($baseWord === null) {
            throw new HandlerProcessingException(static::getAlias(), 'First argument "baseWord" is missing (the word for which the question suffix should be chosen)');
        }

        return $this->chooseSuffix($baseWord);
    }

    protected function chooseSuffix(string $word): string
    {
        $lastVowel = TurkishFrontAndBackVowelsHelper::getLastVowel($word);

        switch ($lastVowel) {
            case 'a':
            case 'ı':
                return 'mı';
            case 'e':
            case 'i':
                return 'mi';
            case 'o':
            case 'u':
                return 'mu';
            case 'ö':
            case 'ü':
                return 'mü';
            default:
                throw new HandlerProcessingException(static::getAlias(), 'Unable to determine the correct suffix for the given word');
        }
    }

    public static function generateManual(): HandlerManualData
    {
        $argumentManualData = [
            new ArgumentManualData(0, true, 'baseWord', 'The word for which the question suffix should be chosen',
                null,
                ['İstanbul', 'Ankara', 'Ev', 'Kapı']
            )
        ];

        return new HandlerManualData(
            static::getAlias(),
            static::getAllowedLanguagesIso(),
            'Chooses the appropriate question suffix ("mı", "mi", "mu", "mü") for the given word based on vowel harmony. Specific to the Turkish language.',
            $argumentManualData,
            null
        );
    }
}
