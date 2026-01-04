<?php

namespace ALI\TextTemplate\Tests\TemplateResolver;

use ALI\TextTemplate\TemplateResolver\TemplateMessageResolverFactory;
use ALI\TextTemplate\TextTemplateFactory;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class NodesTest extends TestCase
{
    public function test()
    {
        $textTemplateFactory = new TextTemplateFactory(new TemplateMessageResolverFactory('en'));

        $content =
'{% if is_daytime %}
  Good day, {user_name}!
{% else %}
  Good evening, {user_name}!
{% endif %}';
        $textTemplate = $textTemplateFactory->create($content, [
            'user_name' => 'Jerry',
            'is_daytime' => true,
        ]);

        dd($textTemplate->resolve());

//        self::assertEquals('Good day, Jerry!', $textTemplate->resolve());
    }
}
