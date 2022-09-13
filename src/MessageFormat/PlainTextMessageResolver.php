<?php

namespace ALI\TextTemplate\MessageFormat;

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
