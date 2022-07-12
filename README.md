# Text template

## Installation
require php ^7.4
```bash
$ composer require ali-translator/text-template
```

### Using

```php
use \ALI\TextTemplate\MessageFormat\MessageFormatsEnum;
use \ALI\TextTemplate\TextTemplateFactory;
use \ALI\TextTemplate\TextTemplateResolver;

$textTemplateFactory = new TextTemplateFactory();
$textTemplate = $textTemplateFactory->create('Tom has {appleNumbers}', [
    'appleNumbers' => [
        'content' => '{appleNumbers, plural, =0{no one apple}=1{one apple}other{many apples}}',
        'parameters' => [
            'appleNumbers' => 1,
        ],
        'format' => MessageFormatsEnum::MESSAGE_FORMATTER,
    ],
], MessageFormatsEnum::TEXT_TEMPLATE);

(new TextTemplateResolver('en'))->resolve($textTemplate)
```

Message formats:
```php
// Allow only simple parameters such as "Hello {name}"
// Allow multi-level structures. Child parameters can be a different message format
MessageFormatsEnum::TEXT_TEMPLATE;

// Uses the PECL intl packet "MessageFormatter::formatMessage()" to format text (example {0, plural, =0{Zero}=1{One}other{Unknown #}}).
// Allow only one level structures
MessageFormatsEnum::MESSAGE_FORMATTER;
```

### Tests
In packet exist docker-compose file, with environment for testing.
```bash
docker-compose run php composer install
docker-compose run php vendor/bin/phpunit
```
