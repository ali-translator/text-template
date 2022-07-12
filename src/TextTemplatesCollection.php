<?php

namespace ALI\TextTemplate;

use ALI\TextTemplate\KeyGenerators\KeyGenerator;
use ArrayIterator;
use IteratorAggregate;

class TextTemplatesCollection implements IteratorAggregate
{
    protected KeyGenerator $keyGenerator;

    /**
     * @var TextTemplateItem[]
     */
    protected array $textTemplates = [];

    /**
     * @var string[]
     */
    protected array $indexedSimplyTexts = [];

    /**
     * @var bool[]
     */
    protected array $existBufferKeys = [];

    protected int $idIncrementValue = 0;

    public function __construct(KeyGenerator $keyGenerator)
    {
        $this->keyGenerator = $keyGenerator;
    }

    /**
     * Add a textTemplate to collection and get its key to insert into the text
     * (after translate we replace this key two content)
     */
    public function add(
        TextTemplateItem $textTemplate,
        ?string          $templateId = null
    ): string
    {
        if (isset($this->existBufferKeys[$textTemplate->getContent()])) {
            // Prevent adding the same key few times
            return $textTemplate->getContent();
        }

        $isSimpleText = !$textTemplate->getChildTextTemplatesCollection();

        $templateIdHash = $textTemplate->getIdHash();
        if (empty($templateId) && $isSimpleText && isset($this->indexedSimplyTexts[$templateIdHash])) {
            // If this text already exist and its without parameters - return old key
            $templateId = $this->indexedSimplyTexts[$templateIdHash];
        } else {
            // Adding new unique bufferContent
            $templateId = $templateId ?: (string)$this->idIncrementValue++;
            $this->textTemplates[$templateId] = $textTemplate;
            if ($isSimpleText) {
                $this->indexedSimplyTexts[$textTemplate->getIdHash()] = $templateId;
            }
        }

        $bufferKey = $this->generateKey($templateId);
        $this->existBufferKeys[$bufferKey] = true;

        return $bufferKey;
    }

    public function getBufferContent(string $templateId): ?TextTemplateItem
    {
        return !empty($this->textTemplates[$templateId]) ? $this->textTemplates[$templateId] : null;
    }

    /**
     * @return TextTemplateItem[]
     */
    public function getArray(): array
    {
        return $this->textTemplates;
    }

    public function remove(string $templateId)
    {
        if (isset($this->textTemplates[$templateId])) {
            $buffersContent = $this->textTemplates[$templateId];
            unset($this->textTemplates[$templateId]);
            unset($this->indexedSimplyTexts[$buffersContent->getIdHash()]);
        }
    }

    public function clear(): void
    {
        $this->textTemplates = [];
    }

    public function generateKey(string $templateId): string
    {
        return $this->keyGenerator->generateKey($templateId);
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->textTemplates);
    }

    public function getKeyGenerator(): KeyGenerator
    {
        return $this->keyGenerator;
    }
}
