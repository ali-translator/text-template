<?php

namespace ALI\TextTemplate;

use ALI\TextTemplate\TemplateResolver\Plain\PlainTextMessageResolver;
use ALI\TextTemplate\TemplateResolver\TemplateMessageResolver;

class TextTemplateItem
{
    protected string $content;

    protected ?TextTemplatesCollection $childTextTemplatesCollection = null;

    protected TemplateMessageResolver $templateMessageResolver;

    // If you need custom notes on TextTemplateItem, you can use this property
    private array $customOptions;

    public function __construct(
        string                  $template,
        ?TextTemplatesCollection $childTextTemplatesCollection = null,
        ?TemplateMessageResolver $templateMessageResolver = null,
        array $customOptions = []
    )
    {
        $this->content = $template;
        $this->childTextTemplatesCollection = $childTextTemplatesCollection;
        $this->templateMessageResolver = $templateMessageResolver ?? new PlainTextMessageResolver();
        $this->customOptions = $customOptions;
    }

    public function resolve(): string
    {
        return $this->templateMessageResolver->resolve($this);
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

    public function getCustomOptions(): array
    {
        return $this->customOptions;
    }

    public function setCustomOptions(array $customOptions): void
    {
        $this->customOptions = $customOptions;
    }

    private string $_idHash;

    public function getIdHash(): string
    {
        if (!isset($this->_idHash)) {
            $this->_idHash = $this->templateMessageResolver->getFormatName() . '#' . $this->content;
            foreach ($this->customOptions as $key => $value) {
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
        return $this->templateMessageResolver->getFormatName();
    }
}
