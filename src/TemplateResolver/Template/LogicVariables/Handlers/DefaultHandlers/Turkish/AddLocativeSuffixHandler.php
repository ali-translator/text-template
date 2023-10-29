<?php

namespace ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Turkish;

use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Turkish\Services\LocativeSuffixChooser;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Exceptions\HandlerProcessingException;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\HandlerInterface;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Manual\ArgumentManualData;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Manual\HandlerManualData;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Manual\PipeManualData;

class AddLocativeSuffixHandler implements HandlerInterface
{
    protected LocativeSuffixChooser $locativeSuffixChooser;

    public function __construct(LocativeSuffixChooser $locativeSuffixChooser)
    {
        $this->locativeSuffixChooser = $locativeSuffixChooser;
    }

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
        if ($locative === null) {
            throw new HandlerProcessingException(static::getAlias(), 'First argument "locative" is missing (the base word to which the locative suffix should be added)');
        }

        $suffix = $this->locativeSuffixChooser->choose($locative);

        if ($suffix) {
            $separator = $config[1] ?? "'";
            $locative .= $separator . $suffix;
        }

        return $locative;
    }

    public static function generateManual(): HandlerManualData
    {
        $pipeManualData = new PipeManualData(
            false,
            'locative',
            'The base word to which the locative suffix should be added'
        );

        $argumentManualData = [
            new ArgumentManualData(0, false, 'locative', 'The base word to which the locative suffix should be added',
                null,
                ['İstanbul', 'Yalova']
            ),
            new ArgumentManualData(1, false, 'separator', 'If you need a separator (apostrophe) between the word and the suffix, specify it in this parameter',
                "'",
                ["'", '']
            )
        ];

        return new HandlerManualData(
            static::getAlias(),
            static::getAllowedLanguagesIso(),
            'Adds the appropriate locative suffix ("\'de" or "\'da") to the given word based on vowel harmony. Specific to the Turkish language.',
            $argumentManualData,
            $pipeManualData
        );
    }
}
