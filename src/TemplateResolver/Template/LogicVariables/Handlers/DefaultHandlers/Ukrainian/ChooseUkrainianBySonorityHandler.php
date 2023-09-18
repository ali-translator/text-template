<?php

namespace ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Ukrainian;

use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\HandlerInterface;

class ChooseUkrainianBySonorityHandler implements HandlerInterface
{
    public static function getAlias(): string
    {
        return 'UK_chooseBySonority';
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

    public function run(string $inputText, array $config): string
    {
        $wordAfter = $inputText;
        $lastLetterOfPreviousWord = $config[0] ?? '';
        $originalPreposition = $config[1] ?? null;
        if (!$originalPreposition) {
            return '';
        }

        $prepositionCouple = static::$prepositionCouples[$originalPreposition] ?? null;
        if (!$prepositionCouple) {
            return $originalPreposition;
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
}
