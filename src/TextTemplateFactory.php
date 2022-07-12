<?php

namespace ALI\TextTemplate;

use ALI\TextTemplate\KeyGenerators\KeyGenerator;
use ALI\TextTemplate\KeyGenerators\StaticKeyGenerator;

class TextTemplateFactory
{
    protected KeyGenerator $keyGenerator;

    public function __construct(?KeyGenerator $keyGenerator = null)
    {
        $this->keyGenerator = $keyGenerator ?: new StaticKeyGenerator('{', '}');
    }

    public function create(string $content, array $parameters = [], string $messageFormat = null): TextTemplateItem
    {
        $textTemplatesCollection = null;
        if ($parameters) {
            $textTemplatesCollection = new TextTemplatesCollection($this->keyGenerator);
            foreach ($parameters as $childContentId => $childData) {
                if (!is_array($childData)) {
                    $textTemplateItem = $this->create((string)$childData);
                } else {
                    $childContentSting = $childData['content'];
                    $childParameters = $childData['parameters'] ?? $childData['params'] ?? [];
                    $childMessageFormat = $childData['format'] ?? null;
                    $textTemplateItem = $this->create($childContentSting, $childParameters, $childMessageFormat);
                    if (isset($childData['options'])) {
                        $textTemplateItem->setCustomNotes($childData['options']);
                    }
                }

                $textTemplatesCollection->add($textTemplateItem, $childContentId);
            }
        }

        return new TextTemplateItem($content, $textTemplatesCollection, $messageFormat);
    }
}
