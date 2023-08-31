<?php

namespace ALI\TextTemplate\TemplateResolver\Plain;

use ALI\TextTemplate\MessageFormat\MessageFormatsEnum;
use ALI\TextTemplate\TemplateResolver\TemplateMessageResolver;
use ALI\TextTemplate\TextTemplateItem;

class PlainTextMessageResolver implements TemplateMessageResolver
{
    public function resolve(TextTemplateItem $templateItem): string
    {
        return $templateItem->getContent();
    }

    public function getFormatName(): string
    {
        return MessageFormatsEnum::PLAIN_TEXT;
    }
}
