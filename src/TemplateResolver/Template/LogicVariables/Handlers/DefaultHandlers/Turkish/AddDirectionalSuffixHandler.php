<?php

namespace ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Turkish;

use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Turkish\Services\DirectionalSuffixChooser;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Exceptions\HandlerProcessingException;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\HandlerInterface;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Manual\ArgumentManualData;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Manual\HandlerManualData;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Manual\PipeManualData;

class AddDirectionalSuffixHandler implements HandlerInterface
{
    protected DirectionalSuffixChooser $directionalSuffixChooser;

    public function __construct(DirectionalSuffixChooser $directionalSuffixChooser)
    {
        $this->directionalSuffixChooser = $directionalSuffixChooser;
    }

    public static function getAlias(): string
    {
        return 'tr_addDirectionalSuffix';
    }

    public static function getAllowedLanguagesIso(): ?array
    {
        return ['tr'];
    }

    public function run(string $pipeInputText, array $config): string
    {
        $directional = $config[0] ?? $pipeInputText;
        if ($directional === null) {
            throw new HandlerProcessingException(static::getAlias(), 'First argument "directional" is missing (the base word to which the directional suffix should be added)');
        }

        $suffix = $this->directionalSuffixChooser->choose($directional);
        if ($suffix) {
            $directional .= "'" . $suffix;
        }

        return $directional;
    }

    public static function generateManual(): HandlerManualData
    {
        $pipeManualData = new PipeManualData(
            false,
            'directional',
            'The base word to which the directional suffix should be added'
        );

        $argumentManualData = [
            new ArgumentManualData(0, false, 'directional', 'The base word to which the directional suffix should be added', [
                'Ev',
                'Okul'
            ])
        ];

        return new HandlerManualData(
            static::getAlias(),
            static::getAllowedLanguagesIso(),
            'Adds the appropriate directional suffix ("\'a", "\'e", "\'ya", "\'ye") to the given word based on vowel harmony. Specific to the Turkish language.',
            $argumentManualData,
            $pipeManualData
        );
    }
}
