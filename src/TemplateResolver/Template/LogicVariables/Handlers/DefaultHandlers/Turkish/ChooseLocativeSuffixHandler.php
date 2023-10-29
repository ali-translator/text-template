<?php

namespace ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Turkish;

use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Turkish\Services\LocativeSuffixChooser;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Exceptions\HandlerProcessingException;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\HandlerInterface;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Manual\ArgumentManualData;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Manual\HandlerManualData;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Manual\PipeManualData;

class ChooseLocativeSuffixHandler implements HandlerInterface
{
    protected LocativeSuffixChooser $locativeSuffixChooser;

    public function __construct(LocativeSuffixChooser $locativeSuffixChooser)
    {
        $this->locativeSuffixChooser = $locativeSuffixChooser;
    }

    public static function getAlias(): string
    {
        return 'tr_chooseLocativeSuffix';
    }

    public static function getAllowedLanguagesIso(): ?array
    {
        return ['tr'];
    }

    public function run(string $pipeInputText, array $config): string
    {
        $locative = $config[0] ?? $pipeInputText;
        if ($locative === null) {
            throw new HandlerProcessingException(static::getAlias(), 'First argument "locative" is missing (the main word for which you should choose a locative suffix)');
        }

        $suffix = $this->locativeSuffixChooser->choose($locative);

        return $suffix ?? '';
    }

    public static function generateManual(): HandlerManualData
    {
        $pipeManualData = new PipeManualData(
            false,
            'locative',
            'The main word for which you should choose a locative suffix'
        );

        $argumentManualData = [
            new ArgumentManualData(0, false, 'locative', 'The main word for which you should choose a locative suffix',[
                'İstanbul',
                'Yalova'
            ])
        ];

        return new HandlerManualData(
            static::getAlias(),
            static::getAllowedLanguagesIso(),
            'Selects the appropriate locative suffix ("\'de" or "\'da") to the given word based on vowel harmony. Specific to the Turkish language.',
            $argumentManualData,
            $pipeManualData
        );
    }
}
