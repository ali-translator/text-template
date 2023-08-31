<?php

namespace ALI\TextTemplate\Tests\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Turkish;

use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\HandlerInterface;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Turkish\AddTurkishLocativeSuffixHandler;
use PHPUnit\Framework\TestCase;

class AddTurkishLocativeSuffixHandlerTest extends TestCase
{
    public function test()
    {
        $handler = new AddTurkishLocativeSuffixHandler();

        $dataForCheck = [
            'İstanbul' => "İstanbul'da",
            'Düzce' => "Düzce'de",
        ];
        $this->check($dataForCheck, $handler);
    }

    protected function check(
        array            $dataForCheck,
        HandlerInterface $handler
    ): void
    {
        foreach ($dataForCheck as $inputText => $correctResolvedText) {
            $this->assertEquals($correctResolvedText, $handler->run($inputText, []));
        }
    }
}
