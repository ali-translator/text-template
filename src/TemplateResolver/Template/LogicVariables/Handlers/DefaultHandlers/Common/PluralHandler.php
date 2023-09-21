<?php

namespace ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Common;

use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\HandlerInterface;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Manual\ArgumentManualData;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Manual\HandlerManualData;

class PluralHandler implements HandlerInterface
{
    protected string $locale;

    public function __construct(string $locale = 'en')
    {
        $this->locale = $locale;
    }

    public static function getAlias(): string
    {
        return 'plural';
    }

    public static function getAllowedLanguagesIso(): ?array
    {
        return null;
    }

    public function run(string $pipeInputText, array $config): string
    {
        $numberValue = $config[0] ?? null;
        $templateOptions = $config[1] ?? null;
        $locale = $config[2] ?? $this->locale;

        if ($numberValue === null || $templateOptions === null) {
            return '';
        }

        $templateOptions = str_replace(['[', ']'], ['{', '}'], $templateOptions);

        $template = '{numberValue, plural, ' . $templateOptions . '}';
        $parameters = ['numberValue' => $numberValue];

        return \MessageFormatter::formatMessage($locale, $template, $parameters);
    }

    public static function generateManual(): HandlerManualData
    {
        $argumentManualData = [
            new ArgumentManualData(
                0,
                true,
                'number',
                'The number value you want to use for plural formatting.',
                ['5', '1', '0']
            ),
            new ArgumentManualData(
                1,
                true,
                'text',
                'The ICU MessageFormat string defining the plural forms. But instead of "{" and "}" use "[" and "]"!',
                ["=0[no apples] =1[one apple] two[a couple of apples] few[a few apples] many[lots of apples] other[some apples]"]
            ),
            new ArgumentManualData(
                2,
                false,
                'text',
                'Locale for which the plural forms are defined. Do not override this value unless specifically required!',
                ['en', 'uk', 'tr', 'ru']
            )
        ];

        return new HandlerManualData(
            static::getAlias(),
            static::getAllowedLanguagesIso(),
            'Handles plural formatting of text using ICU MessageFormat conventions. Outputs the appropriate plural string based on the given number value and plural forms template.',
            $argumentManualData,
            null
        );
    }
}
