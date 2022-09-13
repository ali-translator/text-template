<?php

namespace ALI\TextTemplate\MessageFormat;

use ALI\TextTemplate\TextTemplateItem;

interface TemplateMessageResolver
{
    public function resolve(TextTemplateItem $templateItem): string;

    public function getFormatName(): string;
}
