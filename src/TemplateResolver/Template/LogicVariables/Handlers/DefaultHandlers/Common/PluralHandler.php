<?php

namespace ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Common;

use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Exceptions\HandlerProcessingException;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\HandlerInterface;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Manual\ArgumentManualData;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Manual\HandlerManualData;
use MessageFormatter;

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
        if ($numberValue === null) {
            throw new HandlerProcessingException(static::getAlias(), 'First argument "numberForPluralForm" is missing');
        }
        if (!is_numeric($numberValue)) {
            throw new HandlerProcessingException(static::getAlias(), 'The first argument named "numberForPluralForm" must be numeric. The value "' . $numberValue . '" is provided');
        }

        $templateOptions = $config[1] ?? null;
        if ($templateOptions === null) {
            throw new HandlerProcessingException(static::getAlias(), 'Second argument "messageFormat" is missing');
        }

        $locale = $config[2] ?? $this->locale;


        $templateOptions = str_replace(['[', ']'], ['{', '}'], $templateOptions);

        $template = '{numberValue, plural, ' . $templateOptions . '}';
        $parameters = ['numberValue' => $numberValue];

        $result = MessageFormatter::formatMessage($locale, $template, $parameters);
        if ($result === false) {
            throw new HandlerProcessingException(static::getAlias(), 'Incorrect syntax of "messageFormat" argument. See the manual for this handler');
        }

        return $result;
    }

    public static function generateManual(): HandlerManualData
    {
        $argumentManualData = [
            new ArgumentManualData(
                0,
                true,
                'numberForPluralForm',
                'The number value you want to use for plural formatting.',
                ['5', '1', '0']
            ),
            new ArgumentManualData(
                1,
                true,
                'messageFormat',
                'The ICU MessageFormat string defining the plural forms. But instead of "{" and "}" use "[" and "]"!',
                ["=0[no apples] =1[one apple] two[a couple of apples] few[a few apples] many[lots of apples] other[# apples]"]
            ),
            new ArgumentManualData(
                2,
                false,
                'locale',
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
