<?php

namespace ALI\TextTemplate\Tests\TemplateResolver\Node;

use ALI\TextTemplate\MessageFormat\MessageFormatsEnum;
use ALI\TextTemplate\TemplateResolver\TemplateMessageResolverFactory;
use ALI\TextTemplate\TemplateResolver\TextTemplateMessageResolver;
use ALI\TextTemplate\TextTemplateFactory;
use PHPUnit\Framework\TestCase;

class NodesPartsTest extends TestCase
{

    public function testIfElseNodeResolvesCondition(): void
    {
        $templateMessageResolverFactory = new TemplateMessageResolverFactory('en');
        $textTemplateFactory = new TextTemplateFactory($templateMessageResolverFactory);

        $content =
'{% if is_daytime %}
  Good day, {user_name}!
  {% for number in numbers %}
      {number}{% if number > 5 %} - big number{% endif %},
  {% endfor %}
{% else %}
  Good evening, {user_name}!
{% endif %}

{% for item in collection %}
{item.name} - {%if item.age > 25 %}old{%else%}young{%endif%}
{% endfor %}
';

        $textTemplate = $textTemplateFactory->create($content, [
            'user_name' => 'Jerry',
            'is_daytime' => true,
            'numbers' => [1,2,3,4,5,6,7],
            'collection' => [
                [
                    'name' => 'Tom',
                    'age' => 20,
                ],
                [
                    'name' => 'Kate',
                    'age' => 30,
                ]
            ],
        ]);

        // $textTemplate->resolve();

        /** @var TextTemplateMessageResolver $templateMessageResolver */
        $templateMessageResolver = $templateMessageResolverFactory->generateTemplateMessageResolver(MessageFormatsEnum::TEXT_TEMPLATE);
        $this->assertEquals([
            'user_name' => 'string',
            'is_daytime' => 'boolean',
            'numbers' => [
                'type' => 'array',
                'items' => 'number',
            ],
            'collection' => [
                'type' => 'array',
                'items' => [
                    'name' => 'string',
                    'age' => 'number',
                ],
            ],
        ], $templateMessageResolver->getAllUsedPlainVariables($content));
    }
}
