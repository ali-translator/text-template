<?php

namespace ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Russian;

use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Exceptions\HandlerProcessingException;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\HandlerInterface;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Manual\ArgumentManualData;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Manual\HandlerManualData;

class ChoosePrepositionBySonorityHandler implements HandlerInterface
{
    public static function getAlias(): string
    {
        return 'ru_choosePreposition';
    }

    public static function getAllowedLanguagesIso(): ?array
    {
        return ['ru'];
    }

    public function run(string $pipeInputText, array $config): string
    {
        $originalPreposition = $config[0] ?? null;
        if ($originalPreposition === null) {
            throw new HandlerProcessingException(static::getAlias(), 'First argument "originalPreposition" is missing');
        }
        if ($originalPreposition !== 'во/в' && $originalPreposition !== 'в/во') {
            throw new HandlerProcessingException(static::getAlias(), 'Only "в/во" as the first argument is supported');
        }

        $wordAfter = $config[1] ?? null;
        if ($wordAfter === null) {
            throw new HandlerProcessingException(static::getAlias(), 'Missing the 2nd argument "wordAfter"');
        }

        $wordAfterUpperCase = mb_strtoupper($wordAfter);

        if (preg_match('/^ЛЬВ/', $wordAfterUpperCase)) {
            return 'во';
        }

        return 'в';
    }

    public static function generateManual(): HandlerManualData
    {
        $argumentManualData = [
            new ArgumentManualData(0, true, 'originalPreposition', 'Prepositional phrase pair. Supported values: "в/во"', ['в/во']),
            new ArgumentManualData(1, true, 'wordAfter', 'The word that comes after the preposition',[
                'Львов',
                'Киев',
            ])
        ];

        return new HandlerManualData(
            static::getAlias(),
            static::getAllowedLanguagesIso(),
            'Chooses the correct preposition by the sonority of the following word. Specific to the Russian language.',
            $argumentManualData,
            null
        );
    }
}
