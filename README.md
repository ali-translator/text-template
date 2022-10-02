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

$textTemplateFactory = new TextTemplateFactory(new TemplateMessageResolverFactory('en'));
$textTemplate = $textTemplateFactory->create('Tom has {appleNumbers}', [
    'appleNumbers' => [
        'content' => '{appleNumbers, plural, =0{no one apple}=1{one apple}other{many apples}}',
        'parameters' => [
            'appleNumbers' => 1,
        ],
        'format' => MessageFormatsEnum::PLURAL_TEMPLATE,
        // Custom values if you need it
        'options' => ['some_notes' => 123]
    ],
], MessageFormatsEnum::TEXT_TEMPLATE);

echo $textTemplate->resolve();
// Tom has one apple 
```

Message formats:
```php
// Allow only simple parameters such as "Hello {name}"
// Allow multi-level structures. Child parameters can be a different message format
MessageFormatsEnum::TEXT_TEMPLATE;

// Uses the PECL intl packet "MessageFormatter::formatMessage()" to format text (example {0, plural, =0{Zero}=1{One}other{Unknown #}}).
// Allow only one level structures
MessageFormatsEnum::PLURAL_TEMPLATE;
```

### Tests
```bash
php composer install
php vendor/bin/phpunit
```
