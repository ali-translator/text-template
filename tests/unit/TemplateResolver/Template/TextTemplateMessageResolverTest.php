<?php

namespace ALI\TextTemplate\Tests\TemplateResolver\Template;

use ALI\TextTemplate\MessageFormat\MessageFormatsEnum;
use ALI\TextTemplate\TemplateResolver\TemplateMessageResolverFactory;
use ALI\TextTemplate\TemplateResolver\TextTemplateMessageResolver;
use ALI\TextTemplate\TextTemplateFactory;
use ALI\TextTemplate\TextTemplateItem;
use PHPUnit\Framework\TestCase;

class TextTemplateMessageResolverTest extends TestCase
{
    public function test()
    {
        $textTemplateFactory = new TextTemplateFactory(new TemplateMessageResolverFactory('en'));

        $textTemplate = $textTemplateFactory->create('Hello {user_name}', []);
        $this->assertEquals('Hello {user_name}', $textTemplate->resolve());

        $textTemplate = $textTemplateFactory->create('Hello {user_name}', [
            'user_name' => 'Tom'
        ]);
        $this->assertEquals('Hello Tom', $textTemplate->resolve());

        $textTemplate = $textTemplateFactory->create('Hello {user_name} {user_name}', [
            'user_name' => 'Tom'
        ]);
        $this->assertEquals('Hello Tom Tom', $textTemplate->resolve());

        $textTemplate = $textTemplateFactory->create('{first} {second}', [
            'first' => [
                'content' => 'Hello {user_name}.',
                'parameters' => [
                    'user_name' => 'Tom'
                ]
            ],
            'second' => [
                'content' => 'Hello {user_name}.',
                'parameters' => [
                    'user_name' => 'Jerry'
                ]
            ]
        ]);
        $this->assertEquals('Hello Tom. Hello Jerry.', $textTemplate->resolve());

        { // Use object of TextTemplateItem in Factory
            $textTemplate = $textTemplateFactory->create('Tom has {object_name}', [
                'object_name' => new TextTemplateItem('a pen'),
            ]);
            self::assertEquals('Tom has a pen', $textTemplate->resolve());
        }

        { // Resolve two parameters with the same value
            $textTemplate = $textTemplateFactory->create('{a}{b}', [
                'a' => 1,
                'b' => 1,
            ]);
            self::assertEquals('11', $textTemplate->resolve());
        }
    }

    public function testTemplateWithLogicVariable()
    {
        $textTemplateFactory = new TextTemplateFactory(new TemplateMessageResolverFactory('tr'));

        // Template without "variable values"
        $textTemplate = $textTemplateFactory->create('Hello {user_name} from {tr_addLocativeSuffix(city_name)|makeFirstCharacterInUppercase()}', []);
        $this->assertEquals("Hello {user_name} from {tr_addLocativeSuffix(city_name)|makeFirstCharacterInUppercase()}", $textTemplate->resolve());

        // Mixing variables types
        $textTemplate = $textTemplateFactory->create('Hello {user_name} from {tr_addLocativeSuffix(city_name, "\'")|makeFirstCharacterInUppercase()} and {print(city_name)|makeFirstCharacterInLowercase()}', [
            'user_name' => 'Tom',
            'city_name' => 'i̇stanbul',
        ]);
        $this->assertEquals("Hello Tom from İstanbul'da and i̇stanbul", $textTemplate->resolve());

        $textTemplateFactory = new TextTemplateFactory(new TemplateMessageResolverFactory('uk'));

        // Check logic variables with parameters
        $templateContent = 'Розваги {uk_choosePreposition("Розваги", "в/у", city_name)} {city_name}';
        $textTemplate = $textTemplateFactory->create($templateContent, [
            'city_name' => 'Києві',
        ]);
        $this->assertEquals("Розваги в Києві", $textTemplate->resolve());

        // Check undefined logic variable
        $templateContent = 'Розваги {some_undefined_variable("123")} {city_name}';
        $textTemplate = $textTemplateFactory->create($templateContent, [
            'city_name' => 'Києві',
        ]);
        $this->assertEquals('Розваги {some_undefined_variable("123")} Києві', $textTemplate->resolve());
    }

    public function testGetAllUsedPlainVariables()
    {
        $templateMessageResolverFactory = new TemplateMessageResolverFactory('uk');
        /** @var TextTemplateMessageResolver $templateMessageResolver */
        $templateMessageResolver = $templateMessageResolverFactory->generateTemplateMessageResolver(MessageFormatsEnum::TEXT_TEMPLATE);

        $content = '{% if is_active == true %}Розваги {some_undefined_function("123",test_variable)} {print(city_name)|makeFirstCharacterInLowercase()} {test_variable_1}{% else %}{city_name}{% endif %} {% for user in users %}{user.name}{% endfor %}';
        $allUsedPlainVariables = $templateMessageResolver->getAllUsedPlainVariables($content)->toArray();
        $this->assertEquals([
            "test_variable" => "string",
            "city_name" => "string",
            "test_variable_1" => "string",
            "is_active" => "boolean",
            "users" => [
                "type" => "array",
                "items" => [
                    "name" => "string",
                ],
            ],
        ], $allUsedPlainVariables);
    }

    public function testLogicVariablesWithOptionsWithSpecChars()
    {
        $languageISO = 'uk';
        $textTemplateFactory = new TextTemplateFactory(new TemplateMessageResolverFactory($languageISO));
        $templateMessageResolverFactory = new TemplateMessageResolverFactory($languageISO);
        /** @var TextTemplateMessageResolver $templateMessageResolver */
        $templateMessageResolver = $templateMessageResolverFactory->generateTemplateMessageResolver(MessageFormatsEnum::TEXT_TEMPLATE);

        $templateContent = "Tom {print('has')} {plural(appleNumbers,'=0[no one apple] =1[one apple] other[many apples]')}";

        $templateItem = $textTemplateFactory->create($templateContent, ['appleNumbers' => 0]);
        $this->assertEquals("Tom has no one apple", $templateMessageResolver->resolve($templateItem));

        $templateItem = $textTemplateFactory->create($templateContent, ['appleNumbers' => 1]);
        $this->assertEquals("Tom has one apple", $templateMessageResolver->resolve($templateItem));

        $templateItem = $textTemplateFactory->create($templateContent, ['appleNumbers' => 555]);
        $this->assertEquals("Tom has many apples", $templateMessageResolver->resolve($templateItem));
    }

    public function testHidingParserErrors()
    {
        $languageISO = 'uk';
        $textTemplateFactory = new TextTemplateFactory(new TemplateMessageResolverFactory($languageISO));
        $templateMessageResolverFactory = new TemplateMessageResolverFactory($languageISO);
        /** @var TextTemplateMessageResolver $templateMessageResolver */
        $templateMessageResolver = $templateMessageResolverFactory->generateTemplateMessageResolver(MessageFormatsEnum::TEXT_TEMPLATE);

        $templateContent = "Tom {('has')} {plural(appleNumbers,'=0[no one appleg *sa =1[one apple] other[many apples]')}";
        $templateItem = $textTemplateFactory->create($templateContent, ['appleNumbers' => 0]);
        $this->assertEquals("Tom {('has')} {plural(appleNumbers,'=0[no one appleg *sa =1[one apple] other[many apples]')}", $templateMessageResolver->resolve($templateItem));

        $templateContent = "Tom {uk_choosePreposition()}";
        $templateItem = $textTemplateFactory->create($templateContent, ['appleNumbers' => 0]);
        $this->assertEquals("Tom {uk_choosePreposition()}", $templateMessageResolver->resolve($templateItem));
    }

    public function testAfterResolvedContentModifier()
    {
        $textTemplateFactory = new TextTemplateFactory(new TemplateMessageResolverFactory('en'));

        $templateItem = $textTemplateFactory->create('1 {appleName} {print(appleName)|makeFirstCharacterInUppercase()} 3', [
                'appleName' => [
                    'content' => 'yy{number}',
                    'parameters' => [
                        'number' => 53,
                    ],
                    'options' => [
                        TextTemplateItem::OPTION_AFTER_RESOLVED_CONTENT_MODIFIER => function (?string $text) {
                            return $text . $text;
                        }
                    ]
                ],
            ]
            , MessageFormatsEnum::TEXT_TEMPLATE,
            [
                TextTemplateItem::OPTION_AFTER_RESOLVED_CONTENT_MODIFIER => function (?string $text) {
                    return '#' . $text . '@';
                }
            ]);

        $this->assertEquals('#1 yy53yy53 Yy53yy53 3@', $templateItem->resolve());

        $templateItem = $textTemplateFactory->create('Tom has {appleNumbers} apples', [
            'appleNumbers' => 5,
        ], MessageFormatsEnum::TEXT_TEMPLATE, [
            TextTemplateItem::OPTION_AFTER_RESOLVED_CONTENT_MODIFIER => function (?string $text) {
                return str_replace(5, 3, $text);
            }
        ]);
        $this->assertEquals('Tom has 3 apples', $templateItem->resolve());
    }
}
