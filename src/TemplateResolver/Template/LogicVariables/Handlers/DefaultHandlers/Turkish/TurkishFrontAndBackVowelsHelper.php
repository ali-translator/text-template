<?php

namespace ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Turkish;

class TurkishFrontAndBackVowelsHelper
{
    private static array $allVowels = ['e', 'ö', 'ü', 'i', 'a', 'ı', 'o', 'u'];
    private static array $frontVowels = ['e', 'ö', 'ü', 'i'];
    private static array $backVowels = ['a', 'ı', 'o', 'u'];

    const FRONT = 'front';
    const BACK = 'back';

    public static function getLastVowelType(string $word): ?string
    {
        $lastVowel = static::getLastVowel($word);
        if (!$lastVowel) {
            return null;
        }

        if (in_array($lastVowel, static::$frontVowels)) {
            return self::FRONT;
        } elseif (in_array($lastVowel, static::$backVowels)) {
            return self::BACK;
        }

        return null;
    }

    public static function getLastVowel(string $word): ?string
    {
        foreach (array_reverse(mb_str_split($word)) as $character) {
            if (static::isCharacterIsVowel($character)) {
                return $character;
            }
        }

        return null;
    }

    public static function isCharacterIsVowel(string $character): bool
    {
        return in_array($character, self::$allVowels);
    }
}
