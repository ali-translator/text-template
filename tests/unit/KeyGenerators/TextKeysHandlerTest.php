<?php

namespace ALI\TextTemplate\Tests\KeyGenerators;

use ALI\TextTemplate\KeyGenerators\StaticKeyGenerator;
use ALI\TextTemplate\KeyGenerators\TextKeysHandler;
use PHPUnit\Framework\TestCase;

class TextKeysHandlerTest extends TestCase
{
    public function testGetAllKeys()
    {
        [$textKeysHandler, $correctKeyGenerator, $text] = $this->generateBaseData();

        $keys = $textKeysHandler->getAllKeys($correctKeyGenerator, $text);
        $this->assertEquals(['2', '4'], $keys);

        $inCorrectKeyGenerator = new StaticKeyGenerator('[', ']');
        $keys = $textKeysHandler->getAllKeys($inCorrectKeyGenerator, $text);
        $this->assertEquals([], $keys);
    }

    public function testReplaceKeys()
    {
        [$textKeysHandler, $correctKeyGenerator, $text] = $this->generateBaseData();

        $resolvedText = $textKeysHandler->replaceKeys($correctKeyGenerator, $text, function (string $variableKey): ?string {
            return [
                '2' => '@',
                '4' => '#',
            ][$variableKey];
        });
        $this->assertEquals('1 @ 3 #', $resolvedText);
    }

    protected function generateBaseData(): array
    {
        $textKeysHandler = new TextKeysHandler();
        $correctKeyGenerator = new StaticKeyGenerator('{', '}');
        $text = '1 ' . $correctKeyGenerator->generateKey(2) . ' 3 ' . $correctKeyGenerator->generateKey(4);

        return [$textKeysHandler, $correctKeyGenerator, $text];
    }
}
