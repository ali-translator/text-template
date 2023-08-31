<?php

namespace ALI\TextTemplate\Tests;

use ALI\TextTemplate\MessageFormat\MessageFormatsEnum;
use ALI\TextTemplate\TemplateResolver\TemplateMessageResolverFactory;
use ALI\TextTemplate\TextTemplateFactory;
use ALI\TextTemplate\TextTemplateItem;
use PHPUnit\Framework\TestCase;

class TextTemplatesTest extends TestCase
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

        { // Message format
            $textTemplate = $textTemplateFactory->create('{number, plural, =0{Zero}=1{One}other{Unknown #}}', [
                'number' => 0,
            ], MessageFormatsEnum::PLURAL_TEMPLATE);
            $numberTextTemplate = $textTemplate->getChildTextTemplatesCollection()->get('number');

            $this->assertEquals('Zero', $textTemplate->resolve());

            $numberTextTemplate->setContent(1);
            $this->assertEquals('One', $textTemplate->resolve());

            $numberTextTemplate->setContent(50);
            $this->assertEquals('Unknown 50', $textTemplate->resolve());
        }

        { // Plural with another text, on different TextTemplate, without translate
            $textTemplate = $textTemplateFactory->create('Tom has {appleNumbers}', [
                'appleNumbers' => [
                    'content' => '{appleNumbers, plural, =0{no one apple}=1{one apple}other{many apples}}',
                    'parameters' => [
                        'appleNumbers' => 1,
                    ],
                    'format' => MessageFormatsEnum::PLURAL_TEMPLATE,
                ],
            ]);

            $this->assertEquals("Tom has one apple", $textTemplate->resolve());

            $numberTextTemplate = $textTemplate
                ->getChildTextTemplatesCollection()
                ->get('appleNumbers')
                ->getChildTextTemplatesCollection()
                ->get('appleNumbers');
            $numberTextTemplate->setContent(0);

            $this->assertEquals("Tom has no one apple", $textTemplate->resolve());
        }

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
}
