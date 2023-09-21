<?php

namespace ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Ukrainian;

class UkrainianLettersHelper
{
    public static array $vowels = ['а', 'е', 'є', 'и', 'і', 'ї', 'о', 'у', 'ю', 'я'];

    public static array $vowelsWithVowelFirstSound = ['а', 'е', 'и', 'і', 'о', 'у'];
    public static array $vowelsWithConsonantFirstSound = ['є', 'ї', 'ю', 'я'];

    public static array $consonants = ['б', 'в', 'г', 'ґ', 'д', 'ж', 'з', 'к', 'л', 'м', 'н', 'п', 'р', 'с', 'т', 'ф', 'х', 'ц', 'ч', 'ш', 'щ'];

    public static function isVowels(string $letter): bool
    {
        return in_array($letter, static::$vowels);
    }

    public static function isVowelWithVowelFirstSound(string $letter): bool
    {
        return in_array($letter, static::$vowelsWithVowelFirstSound);
    }

    public static function isVowelWithConsonantFirstSound(string $letter): bool
    {
        return in_array($letter, static::$vowelsWithConsonantFirstSound);
    }

    public static function isConsonant(string $letter): bool
    {
        return in_array($letter, static::$consonants);
    }
}
