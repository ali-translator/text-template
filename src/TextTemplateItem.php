<?php

namespace ALI\TextTemplate;

use ALI\TextTemplate\MessageFormat\MessageFormatsEnum;

class TextTemplateItem
{
    protected string $template;

    protected ?TextTemplatesCollection $childTextTemplatesCollection = null;

    private string $messageFormat;

    // If you need custom notes on TextTemplateItem, you can use this property
    private array $customNotes;

    public function __construct(
        string                  $template,
        TextTemplatesCollection $childTextTemplatesCollection = null,
        ?string                 $messageFormat = null,
        array                   $customNotes = []
    )
    {
        $this->template = $template;
        $this->childTextTemplatesCollection = $childTextTemplatesCollection;
        $this->messageFormat = $messageFormat ?: MessageFormatsEnum::TEXT_TEMPLATE;
        $this->customNotes = $customNotes;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function setTemplate(string $content)
    {
        $this->template = $content;
    }

    public function getChildTextTemplatesCollection(): ?TextTemplatesCollection
    {
        return $this->childTextTemplatesCollection;
    }

    public function setChildTextTemplatesCollection(?TextTemplatesCollection $childTextTemplatesCollection): void
    {
        $this->childTextTemplatesCollection = $childTextTemplatesCollection;
    }

    public function getCustomNotes(): array
    {
        return $this->customNotes;
    }

    public function setCustomNotes(array $customNotes): void
    {
        $this->customNotes = $customNotes;
    }

    public function getIdHash(): string
    {
        return $this->messageFormat . '#' . $this->template;
    }

    public function getMessageFormat(): string
    {
        return $this->messageFormat;
    }
}
