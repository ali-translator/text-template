<?php

use ALI\TextTemplate\TextTemplateFactory;
use ALI\TextTemplate\MessageFormat\MessageFormatsEnum;
use ALI\TextTemplate\TextTemplateItem;
use ALI\TextTemplate\TextTemplateResolver;
use PHPUnit\Framework\TestCase;

class TextTemplatesTest extends TestCase
{
    public function test()
    {
        $textTemplateFactory = new TextTemplateFactory();

        $textTemplatesResolver = new TextTemplateResolver('en');

        $textTemplate = $textTemplateFactory->create('Hello {user_name}',[
            'user_name' => 'Tom'
        ]);
        $this->assertEquals('Hello Tom', $textTemplatesResolver->resolve($textTemplate));

        $textTemplate = $textTemplateFactory->create('Hello {user_name} {user_name}',[
            'user_name' => 'Tom'
        ]);
        $this->assertEquals('Hello Tom Tom', $textTemplatesResolver->resolve($textTemplate));

        $textTemplate = $textTemplateFactory->create('{first} {second}',[
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
        $this->assertEquals('Hello Tom. Hello Jerry.', $textTemplatesResolver->resolve($textTemplate));

        { // Message format
            $textTemplate = $textTemplateFactory->create('{number, plural, =0{Zero}=1{One}other{Unknown #}}', [
                'number' => 0,
            ], MessageFormatsEnum::MESSAGE_FORMATTER);
            $numberTextTemplate = $textTemplate->getChildTextTemplatesCollection()->getBufferContent('number');

            $this->assertEquals('Zero', $textTemplatesResolver->resolve($textTemplate));

            $numberTextTemplate->setContent(1);
            $this->assertEquals('One', $textTemplatesResolver->resolve($textTemplate));

            $numberTextTemplate->setContent(50);
            $this->assertEquals('Unknown 50', $textTemplatesResolver->resolve($textTemplate));
        }

        { // Plural with another text, on different TextTemplate, without translate
            $textTemplate = $textTemplateFactory->create('Tom has {appleNumbers}', [
                'appleNumbers' => [
                    'content' => '{appleNumbers, plural, =0{no one apple}=1{one apple}other{many apples}}',
                    'parameters' => [
                        'appleNumbers' => 1,
                    ],
                    'format' => MessageFormatsEnum::MESSAGE_FORMATTER,
                ],
            ]);

            $this->assertEquals("Tom has one apple", $textTemplatesResolver->resolve($textTemplate));

            $numberTextTemplate = $textTemplate
                ->getChildTextTemplatesCollection()
                ->getBufferContent('appleNumbers')
                ->getChildTextTemplatesCollection()
                ->getBufferContent('appleNumbers')
            ;
            $numberTextTemplate->setContent(0);

            $this->assertEquals("Tom has no one apple", $textTemplatesResolver->resolve($textTemplate));
        }

        { // Use object of TextTemplateItem in Factory
            $textTemplate = $textTemplateFactory->create('Tom has {object_name}', [
                'object_name' => new TextTemplateItem('a pen'),
            ]);
            self::assertEquals('Tom has a pen', $textTemplatesResolver->resolve($textTemplate));
        }

        { // Resolve two parameters with the same value
            $textTemplate = $textTemplateFactory->create('{a}{b}', [
                'a' => 1,
                'b' => 1,
            ]);
            self::assertEquals('11', $textTemplatesResolver->resolve($textTemplate));
        }

        { // Check re-adding the key as a template
            $textTemplate = $textTemplateFactory->create('Hello {user_name}', ['user_name' => 'Tom']);
            $childTextTemplatesCollection = $textTemplate->getChildTextTemplatesCollection();
            $userNameKey = $childTextTemplatesCollection->generateKey('user_name');
            $newUserNameKey = $childTextTemplatesCollection->add(new ALI\TextTemplate\TextTemplateItem($userNameKey));
            self::assertEquals($newUserNameKey, $userNameKey);
        }
    }
}
