<?php

use ALI\TextTemplate\TextTemplateFactory;
use ALI\TextTemplate\MessageFormat\MessageFormatsEnum;
use ALI\TextTemplate\TextTemplateResolver;
use PHPUnit\Framework\TestCase;

/**
 * Class
 */
class BufferTranslationTest extends TestCase
{
    const ORIGINAL_LANGUAGE = 'en';
    const CURRENT_LANGUAGE = 'ua';

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

            $numberTextTemplate->setContentString(1);
            $this->assertEquals('One', $textTemplatesResolver->resolve($textTemplate));

            $numberTextTemplate->setContentString(50);
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
            $numberTextTemplate->setContentString(0);

            $this->assertEquals("Tom has no one apple", $textTemplatesResolver->resolve($textTemplate));
        }

        { // Resolve two parameters with the same value
            $textTemplate = $textTemplateFactory->create('{a}{b}', [
                'a' => 1,
                'b' => 1,
            ]);
            self::assertEquals('11', $textTemplatesResolver->resolve($textTemplate));
        }
    }
}