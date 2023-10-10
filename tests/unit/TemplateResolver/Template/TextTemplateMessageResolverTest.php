<?php

namespace ALI\TextTemplate\Tests\TemplateResolver\Template;

use ALI\TextTemplate\MessageFormat\MessageFormatsEnum;
use ALI\TextTemplate\TemplateResolver\Template\TextTemplateMessageResolver;
use ALI\TextTemplate\TemplateResolver\TemplateMessageResolverFactory;
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
        $textTemplate = $textTemplateFactory->create('Hello {user_name} from {|tr_addLocativeSuffix(city_name)|makeFirstCharacterInUppercase}', []);
        $this->assertEquals("Hello {user_name} from {|tr_addLocativeSuffix(city_name)|makeFirstCharacterInUppercase}", $textTemplate->resolve());

        // Mixing variables types
        $textTemplate = $textTemplateFactory->create('Hello {user_name} from {|tr_addLocativeSuffix(city_name)|makeFirstCharacterInUppercase} and {|print(city_name)|makeFirstCharacterInLowercase}', [
            'user_name' => 'Tom',
            'city_name' => 'i̇stanbul',
        ]);
        $this->assertEquals("Hello Tom from İstanbul'da and i̇stanbul", $textTemplate->resolve());

        $textTemplateFactory = new TextTemplateFactory(new TemplateMessageResolverFactory('uk'));

        // Check logic variables with parameters
        $templateContent = 'Розваги {|uk_choosePreposition("Розваги", "в/у", city_name)} {city_name}';
        $textTemplate = $textTemplateFactory->create($templateContent, [
            'city_name' => 'Києві',
        ]);
        $this->assertEquals("Розваги в Києві", $textTemplate->resolve());

        // Check undefined logic variable
        $templateContent = 'Розваги {|some_undefined_variable("123")} {city_name}';
        $textTemplate = $textTemplateFactory->create($templateContent, [
            'city_name' => 'Києві',
        ]);
        $this->assertEquals('Розваги {|some_undefined_variable("123")} Києві', $textTemplate->resolve());
    }

    public function testGetAllUsedPlainVariables()
    {
        $templateMessageResolverFactory = new TemplateMessageResolverFactory('uk');
        /** @var TextTemplateMessageResolver $templateMessageResolver */
        $templateMessageResolver = $templateMessageResolverFactory->generateTemplateMessageResolver(MessageFormatsEnum::TEXT_TEMPLATE);

        $content = 'Розваги {|some_undefined_variable("123",test_variable)} {|print(city_name)|makeFirstCharacterInLowercase} {test_variable_1}';
        $allUsedPlainVariables = $templateMessageResolver->getAllUsedPlainVariables($content);
        $this->assertEquals([
            "test_variable" => "test_variable",
            "city_name" => "city_name",
            "test_variable_1" => "test_variable_1"
        ], $allUsedPlainVariables);
    }

    public function testLogicVariablesWithOptionsWithSpecChars()
    {
        $languageISO = 'uk';
        $textTemplateFactory = new TextTemplateFactory(new TemplateMessageResolverFactory($languageISO));
        $templateMessageResolverFactory = new TemplateMessageResolverFactory($languageISO);
        /** @var TextTemplateMessageResolver $templateMessageResolver */
        $templateMessageResolver = $templateMessageResolverFactory->generateTemplateMessageResolver(MessageFormatsEnum::TEXT_TEMPLATE);

        $templateContent = "Tom {|print('has')} {|plural(appleNumbers,'=0[no one apple] =1[one apple] other[many apples]')}";

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

        $templateContent = "Tom {|('has')} {|plural(appleNumbers,'=0[no one appleg *sa =1[one apple] other[many apples]')}";
        $templateItem = $textTemplateFactory->create($templateContent, ['appleNumbers' => 0]);
        $this->assertEquals("Tom {|('has')} {|plural(appleNumbers,'=0[no one appleg *sa =1[one apple] other[many apples]')}", $templateMessageResolver->resolve($templateItem));

        $templateContent = "Tom {|uk_choosePreposition()}";
        $templateItem = $textTemplateFactory->create($templateContent, ['appleNumbers' => 0]);
        $this->assertEquals("Tom {|uk_choosePreposition()}", $templateMessageResolver->resolve($templateItem));
    }
}
