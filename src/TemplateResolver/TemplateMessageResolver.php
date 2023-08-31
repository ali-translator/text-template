<?php

namespace ALI\TextTemplate\TemplateResolver;

use ALI\TextTemplate\TextTemplateItem;

interface TemplateMessageResolver
{
    public function resolve(TextTemplateItem $templateItem): string;

    public function getFormatName(): string;
}
