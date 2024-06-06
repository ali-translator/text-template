# Text template

## Installation

This installation requires php ^7.4

```bash
$ composer require ali-translator/text-template:^1
```

### Using

```php
use \ALI\TextTemplate\MessageFormat\MessageFormatsEnum;
use \ALI\TextTemplate\TextTemplateFactory;

$textTemplateFactory = new TextTemplateFactory(new TemplateMessageResolverFactory('en'));

# Simple variable
$textTemplate = $textTemplateFactory->create('Tom has {appleNumbers} apples', [
    'appleNumbers' => 5,
]);
echo $textTemplate->resolve();
// Result: "Tom has 5 apples"

# For better results, you can add plural form selection
$textTemplate = $textTemplateFactory->create('Tom has {plural(appleNumbers, "=0[no one apple] =1[one apple] other[many apples]")}', [
    'appleNumbers' => 1,
]);
echo $textTemplate->resolve();
// Result: "Tom has one apple"

# It is also possible to create multi-nested templates
$textTemplate = $textTemplateFactory->create('Tom has {appleNumbers}', [
    'appleNumbers' => [
        'content' => '{plural(appleNumbers,"=0[no one apple] =1[one apple] other[many apples]")}',
        'parameters' => [
            'appleNumbers' => 1,
        ],
        // Custom values, if required (mostly for add-on libraries)
        'options' => ['some_notes' => 123]
    ],
]);
echo $textTemplate->resolve();
// Result: "Tom has one apple"

# The same effect will occur when using php objects
$insideTextTemplate = $textTemplateFactory->create(
    '{plural(appleNumbers,"=0[no one apple] =1[one apple] other[many apples]")}', 
    ['appleNumbers' => 1],
    MessageFormatsEnum::TEXT_TEMPLATE,
    ['some_notes' => 123]
);
$textTemplate = $textTemplateFactory->create('Tom has {appleNumbers}', [
    'appleNumbers' => $insideTextTemplate,
]);
// Result: "Tom has one apple"
```

### Functions Syntax

In our system, Functions provide a dynamic way to manipulate and format text. They utilize a syntax that closely resembles the pipe functionality in Unix-based systems, allowing for a chained or sequential application of multiple functions.

#### Basic Syntax:

```{functionName(some_variable_name, 'some static text')|anotherFunctionWithoutArguments}```

[More details about the syntax](./guides/FUNCTIONS_SYNTAX.md)

### Handlers that process Functions

Handlers are the core functionalities behind the Function Syntax.
They offer the ability to manipulate text and data in various ways.

#### Handlers available out of the box:

* PrintHandler
: Print the value of a "static"/"plain variable". Can be used as input to another handler function.
```{print('Hello World')}```

* HideHandler
  : This handler is designed to acknowledge variables without displaying them in the text. This can be useful in situations where you need to ensure that all registered variables are used in the text, even if they don't visibly appear.<br>
  ```{hide(variable1, variable2, ...)}```

* PluralHandler
: Handles pluralization based on the given parameters and locale.<br>
```{plural(appleNumbers,"=0[no one apple] =1[one apple] other[many apples]")}``` 

* FirstCharacterInLowercaseHandler
: Changes the first character of the input string to lowercase.<br>
```{print('HELLO')|makeFirstCharacterInLowercase()}```

* FirstCharacterInUppercaseHandler
: Transforms the first character of the given text to uppercase.
```{print('hello')|makeFirstCharacterInUppercase()}```

* ChoosePrepositionBySonorityHandler (Russian)
: Determines the correct preposition for the given word in Russian.<br>
```Поездка {ru_choosePreposition('во/в', 'Львов')} Львов```

* AddLocativeSuffixHandler (Turkish)
: Appends the correct locative suffix to a given word in Turkish.<br>
```{tr_addLocativeSuffix('İstanbul')}``` -> ```İstanbul'da```

* AddDirectionalSuffixHandler (Turkish)
: Adds the appropriate directional suffix ("\'a", "\'e", "\'ya", "\'ye") to the given word based on vowel harmony.<br>
```{tr_addDirectionalSuffix('İstanbul')}``` -> ```İstanbul'a``` <br>
: Without apostrophe: <br>
```{tr_addDirectionalSuffix('Ev', '')}``` -> ```Eve```

* ChooseQuestionSuffixHandler (Turkish)
  : Choose the appropriate question suffix ("mı", "mi", "mu", "mü") for the given word based on vowel harmony.
  Specific to the Turkish language.<br>
  ```Şehriniz {city_name} {tr_chooseQuestionSuffix(city_name)}?``` results for "İstanbul"
  will be ```Şehriniz İstanbul mu?```

* ChoosePrepositionBySonorityHandler (Ukrainian)
: Determines the correct preposition for the given word in Ukrainian.<br>
```Поїздка {uk_choosePreposition('Поїздка', 'в/у', 'Львів')} Львів```

### Tests
```bash
php composer install
./vendor/bin/phpunit
```
