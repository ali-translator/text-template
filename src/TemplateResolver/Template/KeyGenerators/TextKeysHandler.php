<?php

namespace ALI\TextTemplate\TemplateResolver\Template\KeyGenerators;

class TextKeysHandler
{
    public function getAllKeys(
        KeyGenerator $keyGenerator,
        ?string      $text
    ): array
    {
        if (!$text) {
            return [];
        }

        if (!preg_match_all($keyGenerator->getRegularExpression(), $text, $textParameterNames)) {
            return [];
        }

        return $textParameterNames['content_id'];
    }

    public function replaceKeys(
        KeyGenerator $keyGenerator,
        ?string      $text,
        callable     $callback
    ): ?string
    {
        if (!$text) {
            return $text;
        }

        return preg_replace_callback(
            $keyGenerator->getRegularExpression(),
            function ($matches) use ($callback) {
                return $callback($matches['content_id']) ?? $matches[0];
            },
            $text
        );
    }
}
