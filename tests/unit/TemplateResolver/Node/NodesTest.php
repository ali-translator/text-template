<?php

namespace ALI\TextTemplate\Tests\TemplateResolver\Node;

use ALI\TextTemplate\TemplateResolver\TemplateMessageResolverFactory;
use ALI\TextTemplate\TextTemplateFactory;
use PHPUnit\Framework\TestCase;

class NodesTest extends TestCase
{
    public function testIfElseNodeResolvesCondition(): void
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

        $this->assertEquals("\n  Good day, Jerry!\n", $textTemplate->resolve());

        $textTemplate = $textTemplateFactory->create($content, [
            'user_name' => 'Jerry',
            'is_daytime' => false,
        ]);

        $this->assertEquals("\n  Good evening, Jerry!\n", $textTemplate->resolve());
    }

    public function testIfElseIfNodeResolvesComparisons(): void
    {
        $textTemplateFactory = new TextTemplateFactory(new TemplateMessageResolverFactory('en'));

        $content = '{% if product_stock &gt; 10 %}Available{% elseif product_stock &gt; 0 %}Only {product_stock} left!{% else %}Sold-out!{% endif %}';

        $textTemplate = $textTemplateFactory->create($content, [
            'product_stock' => 11,
        ]);
        $this->assertEquals('Available', $textTemplate->resolve());

        $textTemplate = $textTemplateFactory->create($content, [
            'product_stock' => 5,
        ]);
        $this->assertEquals('Only 5 left!', $textTemplate->resolve());

        $textTemplate = $textTemplateFactory->create($content, [
            'product_stock' => 0,
        ]);
        $this->assertEquals('Sold-out!', $textTemplate->resolve());
    }

    public function testEqualityConditionAgainstFalse(): void
    {
        $textTemplateFactory = new TextTemplateFactory(new TemplateMessageResolverFactory('en'));

        $content = '{% if online == false %}Offline{% else %}Online{% endif %}';

        $textTemplate = $textTemplateFactory->create($content, [
            'online' => false,
        ]);

        $this->assertEquals('Offline', $textTemplate->resolve());
    }

    public function testNestedNodeWithLogicAndPlainVariables(): void
    {
        $textTemplateFactory = new TextTemplateFactory(new TemplateMessageResolverFactory('en'));

        $content = '{% if show_outer %}Outer {% if is_daytime %}Day {print(user_name)|makeFirstCharacterInUppercase()}{% else %}Night {user_name}{% endif %}{% endif %}';

        $textTemplate = $textTemplateFactory->create($content, [
            'show_outer' => true,
            'is_daytime' => true,
            'user_name' => 'tom',
        ]);

        $this->assertEquals('Outer Day Tom', $textTemplate->resolve());
    }

    public function testForNodeResolvesListItems(): void
    {
        $textTemplateFactory = new TextTemplateFactory(new TemplateMessageResolverFactory('en'));

        $content = '{% for name in names %}Hello {name}! {% endfor %}';
        $textTemplate = $textTemplateFactory->create($content, [
            'names' => [
                'Tom',
                'Jerry',
            ],
        ]);

        $this->assertEquals('Hello Tom! Hello Jerry! ', $textTemplate->resolve());
    }

    public function testForNodeWithNestedIfAndLogicVariable(): void
    {
        $textTemplateFactory = new TextTemplateFactory(new TemplateMessageResolverFactory('en'));

        $content = '{% for user in users %}{% if user.active == true %}{print(user.name)|makeFirstCharacterInUppercase()} ({user.city})|{% endif %}{% endfor %}';
        $textTemplate = $textTemplateFactory->create($content, [
            'users' => [
                [
                    'name' => 'tom',
                    'active' => true,
                    'city' => 'london',
                ],
                [
                    'name' => 'jerry',
                    'active' => false,
                    'city' => 'paris',
                ],
                [
                    'name' => 'kate',
                    'active' => true,
                    'city' => 'rome',
                ],
            ],
        ]);

        $this->assertEquals('Tom (london)|Kate (rome)|', $textTemplate->resolve());
    }

    public function testIfNodeContainsNestedIfNode(): void
    {
        $textTemplateFactory = new TextTemplateFactory(new TemplateMessageResolverFactory('en'));

        $content = '{% if outer %}Outer:{% if inner %}Inner OK{% else %}Inner NO{% endif %}{% endif %}';
        $textTemplate = $textTemplateFactory->create($content, [
            'outer' => true,
            'inner' => true,
        ]);

        $this->assertEquals('Outer:Inner OK', $textTemplate->resolve());
    }

    public function testIfNodeContainsForNode(): void
    {
        $textTemplateFactory = new TextTemplateFactory(new TemplateMessageResolverFactory('en'));

        $content = '{% if show_list %}{% for item in items %}{item.name},{% endfor %}{% endif %}';
        $textTemplate = $textTemplateFactory->create($content, [
            'show_list' => true,
            'items' => [
                ['name' => 'A'],
                ['name' => 'B'],
            ],
        ]);

        $this->assertEquals('A,B,', $textTemplate->resolve());
    }
}
