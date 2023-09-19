<?php

namespace ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Russian;

use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\HandlerInterface;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Manual\ArgumentManualData;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Manual\HandlerManualData;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Manual\PipeManualData;

class ChoosePrepositionBySonorityHandler implements HandlerInterface
{
    public static function getAlias(): string
    {
        return 'ru_choosePrepositionBySonority';
    }

    public static function getAllowedLanguagesIso(): ?array
    {
        return ['ru'];
    }

    public function run(string $pipeInputText, array $config): string
    {
        $originalPreposition = $config[0] ?? null;
        $wordAfter = $config[1] ?? null;

        if ($originalPreposition === null || $wordAfter === null) {
            return '';
        }

        $wordAfterUpperCase = mb_strtoupper($wordAfter);

        if (preg_match('/^ЛЬВ/', $wordAfterUpperCase)) {
            return 'во';
        }

        return 'в';
    }

    public static function generateManual(): HandlerManualData
    {
        $pipeManualData = new PipeManualData(
            false,
            false,
            null,
            null
        );

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
            $pipeManualData,
            $argumentManualData
        );
    }
}
