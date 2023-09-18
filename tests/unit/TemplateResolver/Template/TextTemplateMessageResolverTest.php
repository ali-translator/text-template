<?php

namespace ALI\TextTemplate\Tests\TemplateResolver\Template;

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
        $textTemplateFactory = new TextTemplateFactory(new TemplateMessageResolverFactory('en'));

        // Template without "variable values"
        $textTemplate = $textTemplateFactory->create('Hello {user_name} from {TR_addLocativeSuffix(city_name)|makeFirstCharacterInUppercase}', []);
        $this->assertEquals("Hello {user_name} from {TR_addLocativeSuffix(city_name)|makeFirstCharacterInUppercase}", $textTemplate->resolve());

        // Mixing variables types
        $textTemplate = $textTemplateFactory->create('Hello {user_name} from {TR_addLocativeSuffix(city_name)|makeFirstCharacterInUppercase} and {print(city_name)|makeFirstCharacterInLowercase}', [
            'user_name' => 'Tom',
            'city_name' => 'i̇stanbul',
        ]);
        $this->assertEquals("Hello Tom from İstanbul'da and i̇stanbul", $textTemplate->resolve());

        // Check logic variables with parameters
        $templateContent = 'Розваги {UK_chooseBySonority("Розваги", "в/у", city_name)} {city_name}';
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
}
