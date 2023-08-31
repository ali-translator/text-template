<?php

namespace ALI\TextTemplate\Tests\TemplateResolver\Plural;

use ALI\TextTemplate\MessageFormat\MessageFormatsEnum;
use ALI\TextTemplate\TemplateResolver\TemplateMessageResolverFactory;
use ALI\TextTemplate\TextTemplateFactory;
use PHPUnit\Framework\TestCase;

class PluralTemplateMessageResolverTest extends TestCase
{
    public function test()
    {
        $textTemplateFactory = new TextTemplateFactory(new TemplateMessageResolverFactory('en'));

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
    }
}
