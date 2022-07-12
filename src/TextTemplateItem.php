<?php

namespace ALI\TextTemplate;

use ALI\TextTemplate\MessageFormat\MessageFormatsEnum;

class TextTemplateItem
{
    protected string $content;

    protected ?TextTemplatesCollection $childTextTemplatesCollection = null;

    private string $messageFormat;

    public function __construct(string $content, TextTemplatesCollection $childTextTemplatesCollection = null, ?string $messageFormat = null)
    {
        $this->content = $content;
        $this->childTextTemplatesCollection = $childTextTemplatesCollection;
        $this->messageFormat = $messageFormat ?: MessageFormatsEnum::TEXT_TEMPLATE;
    }

    public function getContentString(): string
    {
        return $this->content;
    }

    public function setContentString(string $content)
    {
        $this->content = $content;
    }

    public function getChildTextTemplatesCollection(): ?TextTemplatesCollection
    {
        return $this->childTextTemplatesCollection;
    }

    public function setChildTextTemplatesCollection(?TextTemplatesCollection $childTextTemplatesCollection): void
    {
        $this->childTextTemplatesCollection = $childTextTemplatesCollection;
    }

    public function getIdHash(): string
    {
        return $this->messageFormat . '#' . $this->content;
    }

    public function getMessageFormat(): string
    {
        return $this->messageFormat;
    }
}
