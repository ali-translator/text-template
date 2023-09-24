<?php

namespace ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Ukrainian;

use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Exceptions\HandlerProcessingException;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\HandlerInterface;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Manual\ArgumentManualData;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Manual\HandlerManualData;

class ChoosePrepositionBySonorityHandler implements HandlerInterface
{
    public static function getAlias(): string
    {
        return 'uk_choosePrepositionBySonority';
    }

    public static function getAllowedLanguagesIso(): ?array
    {
        return ['uk'];
    }

    protected static array $prepositionCouples = [
        'в/у' => [
            'forVowel' => 'в',
            'forConsonant' => 'у'
        ],
        'у/в' => [
            'forVowel' => 'в',
            'forConsonant' => 'у'
        ],
    ];

    public function run(string $pipeInputText, array $config): string
    {
        $lastLetterOfPreviousWord = $config[0] ?? null;
        if ($lastLetterOfPreviousWord === null) {
            throw new HandlerProcessingException(static::getAlias(), 'First argument "lastWordOrItLastLetter" is missing');
        }

        $originalPreposition = $config[1] ?? null;
        if ($originalPreposition === null) {
            throw new HandlerProcessingException(static::getAlias(), 'Second argument "originalPreposition" is missing');
        }

        $wordAfter = $config[2] ?? null;
        if ($wordAfter === null) {
            throw new HandlerProcessingException(static::getAlias(), 'Third argument "wordAfter" is missing');
        }

        // Also accepts "full word" for better syntax reading
        $lastLetterOfPreviousWord = mb_substr($lastLetterOfPreviousWord, -1, 1);

        $prepositionCouple = static::$prepositionCouples[$originalPreposition] ?? null;
        if (!$prepositionCouple) {
            throw new HandlerProcessingException(static::getAlias(), 'Argument "originalPreposition" is specified as "'.$originalPreposition.'" which is not supported .Supported:' .implode(array_keys(static::$prepositionCouples)) );
        }
        $forVowel = $prepositionCouple['forVowel'];
        $forConsonant = $prepositionCouple['forConsonant'];

        // Якщо остання буква перед "у"/"в" - голосна
        if ($lastLetterOfPreviousWord && UkrainianLettersHelper::isVowels($lastLetterOfPreviousWord)) {

            if (preg_match('/^(в|ф|льв|св|тв|хв)/iu', $wordAfter)) {
                // пишемо "у" якщо "слово після" починається з "в","ф", "льв", "св", "тв", "хв"
                return $forConsonant;
            } else {
                // пишемо "в" у всіх інших випадках
                return $forVowel;
            }
        } else {
            // Все інше (містить: "приголосні букви","символ" або "це на початку речення")

            $firstLetterOfNextWord = mb_strtolower(mb_substr($wordAfter, 0, 1));
            if (!$firstLetterOfNextWord) {
                return $originalPreposition;
            }

            if (UkrainianLettersHelper::isVowelWithVowelFirstSound($firstLetterOfNextWord)) {
                // якщо перша буква "слова яке іде після" це: "а", "е", "и", "і", "о", "у" - пишемо "в"
                return $forVowel;
            }

            // все інше - пишемо "у"
            return $forConsonant;
        }
    }

    public static function generateManual(): HandlerManualData
    {
        $argumentManualData = [
            new ArgumentManualData(0, true,
                'lastWordOrItLastLetter', 'Last letter of the word preceding the preposition. Also accepts full word for better syntax readability.',
                [
                    'Розваги',
                    'Марш',
                ]),
            new ArgumentManualData(1, true,
                'originalPreposition', 'The original preposition to be chosen based on sonority.',
                [
                    'в/у'
                ]),
            new ArgumentManualData(2, true,
                'wordAfter', 'The word immediately following the preposition.',
                [
                    'Києві',
                    'Одесі',
                    'Львові',
                ])
        ];

        return new HandlerManualData(
            static::getAlias(),
            static::getAllowedLanguagesIso(),
            'Chooses the appropriate preposition ("у" or "в") based on the sonority of the preceding and following words. Specific to the Ukrainian language.',
            $argumentManualData,
            null
        );
    }
}
