<?php

namespace ALI\TextTemplate;

use ArrayIterator;
use IteratorAggregate;

class TextTemplatesCollection implements IteratorAggregate
{
    /**
     * @var TextTemplateItem[]
     */
    protected array $textTemplates = [];

    /**
     * @var string[]
     */
    protected array $indexedSimplyTexts = [];

    protected int $idIncrementValue = 0;

    /**
     * Add a textTemplate to collection and get its key to insert into the text
     * (after translate we replace this key to content)
     */
    public function add(
        TextTemplateItem $textTemplate,
        ?string          $templateId = null
    ): string
    {
        $isSimpleText = !$textTemplate->getChildTextTemplatesCollection();

        $templateIdHash = $textTemplate->getIdHash();
        if (empty($templateId) && $isSimpleText && isset($this->indexedSimplyTexts[$templateIdHash])) {
            // If this text already exists and its without parameters - return old key
            $templateId = $this->indexedSimplyTexts[$templateIdHash];
        } else {
            // Adding new unique content
            $templateId = $templateId ?: (string)$this->idIncrementValue++;
            $this->textTemplates[$templateId] = $textTemplate;
            if ($isSimpleText) {
                $this->indexedSimplyTexts[$textTemplate->getIdHash()] = $templateId;
            }
        }

        return $templateId;
    }

    public function get(string $templateId): ?TextTemplateItem
    {
        return $this->textTemplates[$templateId] ?? null;
    }

    public function remove(string $templateId)
    {
        if (isset($this->textTemplates[$templateId])) {
            $content = $this->textTemplates[$templateId];
            unset($this->textTemplates[$templateId]);
            unset($this->indexedSimplyTexts[$content->getIdHash()]);
        }
    }

    /**
     * @return TextTemplateItem[]
     */
    public function getArray(): array
    {
        return $this->textTemplates;
    }

    public function clear(): void
    {
        $this->idIncrementValue = 0;
        $this->textTemplates = [];
        $this->indexedSimplyTexts = [];
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->textTemplates);
    }

    /**
     * @param string[] $keys
     */
    public function sliceByKeys(array $keys): TextTemplatesCollection
    {
        $partOfTextTemplatesCollection = clone $this;
        $partOfTextTemplatesCollection->clear();
        foreach ($keys as $keyId) {
            $textTemplateItem = $this->get($keyId);
            if ($textTemplateItem) {
                $partOfTextTemplatesCollection->add($textTemplateItem, $keyId);
            }
        }

        return $partOfTextTemplatesCollection;
    }
}
