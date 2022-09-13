<?php

namespace ALI\TextTemplate\MessageFormat;

use ALI\TextTemplate\KeyGenerators\KeyGenerator;
use ALI\TextTemplate\KeyGenerators\TextKeysHandler;
use ALI\TextTemplate\TextTemplateItem;

class TextTemplateMessageResolver implements TemplateMessageResolver
{
    private KeyGenerator $keyGenerator;
    private TextKeysHandler $textKeysHandler;

    public function __construct(KeyGenerator $keyGenerator)
    {
        $this->keyGenerator = $keyGenerator;
        $this->textKeysHandler = new TextKeysHandler();
    }

    public function getFormatName(): string
    {
        return MessageFormatsEnum::TEXT_TEMPLATE;
    }

    public function resolve(TextTemplateItem $templateItem): string
    {
        $childTextTemplatesCollection = $templateItem->getChildTextTemplatesCollection();
        if (!$childTextTemplatesCollection) {
            return $templateItem->getContent();
        }

        return $this->textKeysHandler->replaceKeys(
            $this->keyGenerator,
            $templateItem->getContent(),
            function (string $variableName) use ($childTextTemplatesCollection) {
                $childValue = $childTextTemplatesCollection->get($variableName);
                if (!$childValue) {
                    return null;
                }

                return $childValue->resolve();
            }
        );
    }
}
