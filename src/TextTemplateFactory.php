<?php

namespace ALI\TextTemplate;

use ALI\TextTemplate\MessageFormat\MessageFormatsEnum;
use ALI\TextTemplate\MessageFormat\TemplateMessageResolver;
use ALI\TextTemplate\MessageFormat\TemplateMessageResolverFactory;

class TextTemplateFactory
{
    protected TemplateMessageResolverFactory $templateMessageResolverFactory;

    public function __construct(TemplateMessageResolverFactory $templateMessageResolverFactory)
    {
        $this->templateMessageResolverFactory = $templateMessageResolverFactory;
    }

    /**
     * @param string $content
     * @param array $parameters
     * @param string|TemplateMessageResolver $messageFormat
     * @param array $customTextItemOptions
     * @return TextTemplateItem
     */
    public function create(
        string $content,
        array $parameters = [],
               $messageFormat = null,
        array $customTextItemOptions = []
    ): TextTemplateItem
    {
        $textTemplatesCollection = null;
        if ($parameters) {
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
        }

        if (!$messageFormat) {
            $messageFormat = $textTemplatesCollection ? MessageFormatsEnum::TEXT_TEMPLATE : MessageFormatsEnum::PLAIN_TEXT;
        }
        if (is_string($messageFormat)) {
            $templateMessageResolver = $this->templateMessageResolverFactory->generateTemplateMessageResolver($messageFormat);
        } else {
            $templateMessageResolver = $messageFormat;
        }

        return new TextTemplateItem($content, $textTemplatesCollection, $templateMessageResolver, $customTextItemOptions);
    }
}
