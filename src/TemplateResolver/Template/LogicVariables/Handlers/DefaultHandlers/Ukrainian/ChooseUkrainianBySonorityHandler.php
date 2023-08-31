<?php

namespace ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Ukrainian;

use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\HandlerInterface;

class ChooseUkrainianBySonorityHandler implements HandlerInterface
{
    public static function getAlias(): string
    {
        return 'chooseUkrainianBySonority';
    }

    protected static array $prepositionCouplesForFirstVowelChar = [
        'в' => 'у',
    ];

    protected static array $prepositionCouplesForFirstConsonantChar = [
        'у' => 'в',
    ];

    public function run(string $inputText, array $config): string
    {
        $originalPreposition = $config[0] ?? null;
        $firstLetterOfPreviousWord = $config[1] ?? '';
        if (!$originalPreposition) {
            return $originalPreposition;
        }

        $alternativePreposition = static::$prepositionCouplesForFirstVowelChar[$originalPreposition] ?? null;
        if ($alternativePreposition) {
            $forVowel = $originalPreposition;
            $forConsonant = $alternativePreposition;
        } else {
            $alternativePreposition = static::$prepositionCouplesForFirstConsonantChar[$originalPreposition] ?? null;
            if ($alternativePreposition) {
                $forVowel = $alternativePreposition;
                $forConsonant = $originalPreposition;
            }
        }
        if (!isset($forVowel) || !isset($forConsonant)) {
            return $originalPreposition;
        }

        if ($firstLetterOfPreviousWord && UkrainianLettersHelper::isVowels($firstLetterOfPreviousWord)) {
            // Якщо остання буква перед "у"/"в" - голосна

            if (preg_match('/^(в|ф|льв|св|тв|хв)/iu', $inputText)) {
                // пишемо "у" якщо "слово після" починається з "в","ф", "льв", "св", "тв", "хв"
                return $forConsonant;
            } else {
                // пишемо "в" у всіх інших випадках
                return $forVowel;
            }
        } else {
            // Все інше (містить: "приголосні букви","символ" або "це на початку речення")

            $firstLetterOfNextWord = mb_strtolower(mb_substr($inputText, 0, 1));
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
