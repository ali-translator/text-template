<?php

namespace ALI\TextTemplate;

use ALI\TextTemplate\MessageFormat\MessageFormatsEnum;
use ALI\TextTemplate\TemplateResolver\TemplateMessageResolver;
use ALI\TextTemplate\TemplateResolver\TemplateMessageResolverFactory;

class TextTemplateFactory
{
    protected TemplateMessageResolverFactory $templateMessageResolverFactory;

    public function __construct(TemplateMessageResolverFactory $templateMessageResolverFactory)
    {
        $this->templateMessageResolverFactory = $templateMessageResolverFactory;
    }

    /**
     * @param string|TemplateMessageResolver $messageFormat
     */
    public function create(
        string $content,
        array $parameters = [],
               $messageFormat = null,
        array $customTextItemOptions = []
    ): TextTemplateItem
    {
        $textTemplatesCollection = $this->generateTextTemplateCollection($parameters);
        $templateMessageResolver = $this->generateTemplateMessageResolver($messageFormat, $textTemplatesCollection);

        return new TextTemplateItem(
            $content,
            $textTemplatesCollection,
            $templateMessageResolver,
            $customTextItemOptions
        );
    }

    protected function generateTextTemplateCollection(array $parameters): ?TextTemplatesCollection
    {
        if (!$parameters) {
            return null;
        }

        $textTemplatesCollection = new TextTemplatesCollection();
        foreach ($parameters as $childContentId => $childData) {
            if (!is_array($childData)) {
                if ($childData instanceof TextTemplateItem) {
                    $textTemplateItem = $childData;
                } else {
                    $textTemplateItem = $this->create((string)$childData);
                }
            } else {
                $childContentSting = $childData['content'] ?? '';
                $childParameters = $childData['parameters'] ?? $childData['params'] ?? [];

                $textTemplateItem = $this->create($childContentSting, $childParameters, $childData['format'] ?? null);
                if (isset($childData['options'])) {
                    $textTemplateItem->setCustomOptions($childData['options']);
                }
            }

            $textTemplatesCollection->add($textTemplateItem, $childContentId);
        }

        return $textTemplatesCollection;
    }

    /**
     * @param TemplateMessageResolver|string $messageFormat
     */
    protected function generateTemplateMessageResolver($messageFormat, ?TextTemplatesCollection $textTemplatesCollection): TemplateMessageResolver
    {
        if (!$messageFormat) {
            $messageFormat = $textTemplatesCollection ? MessageFormatsEnum::TEXT_TEMPLATE : MessageFormatsEnum::PLAIN_TEXT;
        }
        if (is_string($messageFormat)) {
            $templateMessageResolver = $this->templateMessageResolverFactory->generateTemplateMessageResolver($messageFormat);
        } else {
            $templateMessageResolver = $messageFormat;
        }

        return $templateMessageResolver;
    }
}
