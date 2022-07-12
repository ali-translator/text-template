<?php

namespace ALI\TextTemplate;

use ALI\TextTemplate\KeyGenerators\KeyGenerator;
use ALI\TextTemplate\MessageFormat\MessageFormatsEnum;
use MessageFormatter;
use SebastianBergmann\Template\RuntimeException;

class TextTemplateResolver
{
    private ?string $locale;

    public function __construct(?string $locale = null)
    {
        $this->locale = $locale;
    }

    public function resolve(
        TextTemplateItem $textTemplate
    ): string
    {
        $contentString = $textTemplate->getContentString();

        $childContentCollection = $textTemplate->getChildTextTemplatesCollection();
        if (!$childContentCollection) {
            return $contentString;
        }

        switch ($textTemplate->getMessageFormat()) {
            case MessageFormatsEnum::MESSAGE_FORMATTER:
                $parameters = [];
                foreach ($childContentCollection->getArray() as $key => $value) {
                    $parameters[$key] = $this->resolve($value);
                }
                if (!$this->locale) {
                    throw new RuntimeException('You must define a "locale" in the constructor to use the "MessageFormatsEnum::MESSAGE_FORMATTER" format');
                }
                $contentString = MessageFormatter::formatMessage($this->locale, $contentString, $parameters);
                break;
            case MessageFormatsEnum::TEXT_TEMPLATE:
                $forReplacing = $this->prepareBufferReplacingArray($childContentCollection);
                $contentString = $this->resolveChildBuffers($contentString, $forReplacing, $childContentCollection->getKeyGenerator());
                break;
        }

        return $contentString;
    }

    protected function prepareBufferReplacingArray(
        TextTemplatesCollection $childContentCollection
    ): array
    {
        $forReplacing = [];
        foreach ($childContentCollection->getArray() as $bufferId => $childBufferContent) {
            $translatedChildBufferString = $this->resolve($childBufferContent);

            $bufferKey = $childContentCollection->generateKey($bufferId);
            $forReplacing[$bufferKey] = $translatedChildBufferString;
        }

        return $forReplacing;
    }

    protected function resolveChildBuffers(
        string $contentString,
        array $forReplacing,
        KeyGenerator $keyGenerator
    ): string
    {
        return preg_replace_callback(
            $keyGenerator->getRegularExpression(),
            function ($matches) use (&$forReplacing) {
                // $replacedIds[] = $matches['id'];
                if(!isset($forReplacing[$matches[0]])){
                    return $matches[0];
                }

                return $forReplacing[$matches[0]];
            },
            $contentString);
    }
}
