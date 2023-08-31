<?php

namespace ALI\TextTemplate\TemplateResolver\Plural;

use ALI\TextTemplate\MessageFormat\MessageFormatsEnum;
use ALI\TextTemplate\TemplateResolver\TemplateMessageResolver;
use ALI\TextTemplate\TextTemplateItem;
use MessageFormatter;

class PluralTemplateMessageResolver implements TemplateMessageResolver
{
    private string $locale;

    public function __construct(?string $locale = 'en')
    {
        $this->locale = $locale;
    }

    public function getFormatName(): string
    {
        return MessageFormatsEnum::PLURAL_TEMPLATE;
    }

    public function resolve(TextTemplateItem $templateItem): string
    {
        $parameters = [];
        foreach ($templateItem->getChildTextTemplatesCollection()->getArray() as $key => $childTextItem) {
            $parameters[$key] = $childTextItem->resolve();
        }

        return MessageFormatter::formatMessage($this->locale, $templateItem->getContent(), $parameters);
    }
}
