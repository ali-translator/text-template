<?php

namespace ALI\TextTemplate;

use ALI\TextTemplate\MessageFormat\MessageFormatsEnum;

class TextTemplateItem
{
    protected string $content;

    protected ?TextTemplatesCollection $childTextTemplatesCollection = null;

    private string $messageFormat;

    // If you need custom notes on TextTemplateItem, you can use this property
    private array $customNotes = [];

    public function __construct(
        string                  $template,
        TextTemplatesCollection $childTextTemplatesCollection = null,
        ?string                 $messageFormat = null,
        array                   $customNotes = []
    )
    {
        $this->content = $template;
        $this->childTextTemplatesCollection = $childTextTemplatesCollection;
        $this->messageFormat = $messageFormat ?: MessageFormatsEnum::TEXT_TEMPLATE;
        $this->customNotes = $customNotes;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content)
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

    public function getCustomNotes(): array
    {
        return $this->customNotes;
    }

    public function setCustomNotes(array $customNotes): void
    {
        $this->customNotes = $customNotes;
    }

    private string $_idHash;

    public function getIdHash(): string
    {
        if (!isset($this->_idHash)) {
            $this->_idHash = $this->messageFormat . '#' . $this->content;
            foreach ($this->customNotes as $key => $value) {
                $this->_idHash .= '#' . $key . ':';
                switch (true) {
                    case is_bool($value):
                        $this->_idHash .= (int)$value;
                        break;
                    case is_string($value):
                        $this->_idHash .= $value;
                        break;
                    case is_object($value):
                        $this->_idHash .= spl_object_hash($value);
                        break;
                    default:
                        $this->_idHash .= serialize($value);
                        break;
                }
            }
        }

        return $this->_idHash;
    }

    public function getMessageFormat(): string
    {
        return $this->messageFormat;
    }
}
