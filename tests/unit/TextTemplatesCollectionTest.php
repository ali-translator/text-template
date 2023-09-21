<?php

namespace ALI\TextTemplate\Tests;

use ALI\TextTemplate\TemplateResolver\TemplateMessageResolverFactory;
use ALI\TextTemplate\TextTemplateFactory;
use ALI\TextTemplate\TextTemplatesCollection;
use PHPUnit\Framework\TestCase;

class TextTemplatesCollectionTest extends TestCase
{
    public function testSliceByKeys()
    {
        $textTemplateCollection = new TextTemplatesCollection();

        $textTemplateFactory = new TextTemplateFactory(
            new TemplateMessageResolverFactory('en')
        );

        $neededKeys = [];
        $neededKeys[] = $textTemplateCollection->add($textTemplateFactory->create('1'));
        $textTemplateCollection->add($textTemplateFactory->create('2'));
        $neededKeys[] = $textTemplateCollection->add($textTemplateFactory->create('3'));
        $textTemplateCollection->add($textTemplateFactory->create('4'));

        $slicedTextCollection = $textTemplateCollection->sliceByKeys($neededKeys);
        $this->assertCount(2, $slicedTextCollection->getArray());
    }
}
