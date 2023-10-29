<?php

namespace ALI\TextTemplate\Tests\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Turkish;

use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Turkish\AddLocativeSuffixHandler;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Turkish\Services\LocativeSuffixChooser;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\HandlerInterface;
use PHPUnit\Framework\TestCase;

class AddTurkishLocativeSuffixHandlerTest extends TestCase
{
    public function test()
    {
        $handler = new AddLocativeSuffixHandler(new LocativeSuffixChooser());

        $dataForCheck = [
            'İstanbul' => "İstanbul'da",
            'Düzce' => "Düzce'de",
        ];
        $this->check($dataForCheck, $handler, null);

        $dataForCheck = [
            'Araba' => "Arabada",
            'Kale' => "Kalede",
        ];
        $this->check($dataForCheck, $handler, '');
    }

    protected function check(
        array            $dataForCheck,
        HandlerInterface $handler,
        ?string $separator
    ): void
    {
        foreach ($dataForCheck as $inputText => $correctResolvedText) {
            $this->assertEquals($correctResolvedText, $handler->run('', [$inputText, $separator]));
        }
    }
}
