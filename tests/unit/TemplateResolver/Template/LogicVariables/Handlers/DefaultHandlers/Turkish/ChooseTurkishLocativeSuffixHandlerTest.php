<?php

namespace ALI\TextTemplate\Tests\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Turkish;

use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Turkish\ChooseLocativeSuffixHandler;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Turkish\Services\LocativeSuffixChooser;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\HandlerInterface;
use PHPUnit\Framework\TestCase;

class ChooseTurkishLocativeSuffixHandlerTest extends TestCase
{
    public function test()
    {
        $handler = new ChooseLocativeSuffixHandler(new LocativeSuffixChooser());

        $dataForCheck = [
            'İstanbul' => "'da",
            'Düzce' => "'de",
            '' => null,
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
