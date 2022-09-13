<?php

namespace ALI\TextTemplate\MessageFormat;

use ALI\TextTemplate\KeyGenerators\KeyGenerator;
use ALI\TextTemplate\KeyGenerators\StaticKeyGenerator;
use RuntimeException;

class TemplateMessageResolverFactory
{
    protected KeyGenerator $keyGenerator;
    protected string $locale;

    public function __construct(
        string        $locale,
        ?KeyGenerator $keyGenerator = null
    )
    {
        $this->keyGenerator = $keyGenerator ?: new StaticKeyGenerator('{', '}');
        $this->locale = $locale;
    }

    public function generateTemplateMessageResolver(?string $messageFormat): TemplateMessageResolver
    {
        switch ($messageFormat ?? MessageFormatsEnum::TEXT_TEMPLATE) {
            case MessageFormatsEnum::TEXT_TEMPLATE:
                $templateMessageResolver = new TextTemplateMessageResolver($this->keyGenerator);
                break;
            case MessageFormatsEnum::MESSAGE_FORMATTER:
            case MessageFormatsEnum::PLURAL_TEMPLATE:
                $templateMessageResolver = new PluralTemplateMessageResolver($this->locale);
                break;
            case MessageFormatsEnum::PLAIN_TEXT:
                $templateMessageResolver = new PlainTextMessageResolver();
                break;
            default:
                throw new RuntimeException('Undefined message format "' . $messageFormat . '"');
        }

        return $templateMessageResolver;
    }
}
