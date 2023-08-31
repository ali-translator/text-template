<?php

namespace ALI\TextTemplate\Tests;

use ALI\TextTemplate\TemplateResolver\TemplateMessageResolverFactory;
use ALI\TextTemplate\TextTemplateFactory;
use PHPUnit\Framework\TestCase;

class TextTemplatesWithLogicVariablesTest extends TestCase
{
    public function testTemplateWithLogicVariable()
    {
        $textTemplateFactory = new TextTemplateFactory(new TemplateMessageResolverFactory('en'));

        // Template without "variable values"
        $textTemplate = $textTemplateFactory->create('Hello {user_name} from {city_name|makeFirstCharacterInUppercase|addTurkishLocativeSuffix}', []);
        $this->assertEquals("Hello {user_name} from {city_name|makeFirstCharacterInUppercase|addTurkishLocativeSuffix}", $textTemplate->resolve());

        // Mixing variables types
        $textTemplate = $textTemplateFactory->create('Hello {user_name} from {city_name|makeFirstCharacterInUppercase|addTurkishLocativeSuffix} and {city_name|makeFirstCharacterInLowercase}', [
            'user_name' => 'Tom',
            'city_name' => 'i̇stanbul',
        ]);
        $this->assertEquals("Hello Tom from İstanbul'da and i̇stanbul", $textTemplate->resolve());

        // Check logic variables with parameters
        $templateContent = 'Розваги {city_name|chooseUkrainianBySonority("в")} {city_name}';
        $textTemplate = $textTemplateFactory->create($templateContent, [
            'city_name' => 'Києві',
        ]);
        $this->assertEquals("Розваги у Києві", $textTemplate->resolve());
    }
}
