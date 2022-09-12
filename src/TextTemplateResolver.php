<?php

namespace ALI\TextTemplate;

use ALI\TextTemplate\KeyGenerators\TextKeysHandler;
use ALI\TextTemplate\MessageFormat\MessageFormatsEnum;
use MessageFormatter;
use SebastianBergmann\Template\RuntimeException;

class TextTemplateResolver
{
    private ?string $locale;
    private TextKeysHandler $textKeysHandler;

    public function __construct(?string $locale = null)
    {
        $this->locale = $locale;
        $this->textKeysHandler = new TextKeysHandler();
    }

    public function resolve(TextTemplateItem $textTemplate): string
    {
        $contentString = $textTemplate->getContent();

        $childContentCollection = $textTemplate->getChildTextTemplatesCollection();
        if (!$childContentCollection) {
            return $contentString;
        }

        switch ($textTemplate->getMessageFormat()) {
            case MessageFormatsEnum::MESSAGE_FORMATTER:
                $contentString = $this->resolveMessageFormatter($childContentCollection, $contentString);
                break;
            case MessageFormatsEnum::TEXT_TEMPLATE:
                $contentString = $this->resolveTextTemplate($childContentCollection, $contentString);
                break;
        }

        return $contentString;
    }

    protected function resolveMessageFormatter(TextTemplatesCollection $childContentCollection, string $contentString): ?string
    {
        $parameters = [];
        foreach ($childContentCollection->getArray() as $key => $value) {
            $parameters[$key] = $this->resolve($value);
        }
        if (!$this->locale) {
            throw new RuntimeException('You must define a "locale" in the constructor to use the "MessageFormatsEnum::MESSAGE_FORMATTER" format');
        }

        return MessageFormatter::formatMessage($this->locale, $contentString, $parameters);
    }

    protected function resolveTextTemplate(TextTemplatesCollection $childContentCollection, string $contentString): ?string
    {
        return $this->textKeysHandler->replaceKeys(
            $childContentCollection->getKeyGenerator(),
            $contentString,
            function (string $variableName) use ($childContentCollection) {
                $childValue = $childContentCollection->get($variableName);
                if (!$childValue) {
                    return null;
                }

                return $this->resolve($childValue);
            }
        );
    }
}
