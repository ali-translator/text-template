<?php

namespace ALI\TextTemplate\Tests\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Turkish;

use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Turkish\AddDirectionalSuffixHandler;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Turkish\Services\DirectionalSuffixChooser;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\HandlerInterface;
use PHPUnit\Framework\TestCase;

class AddTurkishDirectionalSuffixHandlerTest extends TestCase
{
    public function test()
    {
        $handler = new AddDirectionalSuffixHandler(new DirectionalSuffixChooser());

        // Without apostrophe
        $dataForCheck = [
            'Ev' => "Eve",
            'Okul' => "Okula",
            'Kale' => "Kaleye",
            'Bahçe' => "Bahçeye",
            'Kitap' => "Kitapa",
        ];
        $this->check($dataForCheck, $handler, '');

        // With Apostrophe
        $dataForCheck = [
            'İstanbul' => "İstanbul'a",
            'Düzce' => "Düzce'ye",
            'Yalova' => "Yalova'ya"
        ];
        $this->check($dataForCheck, $handler, null);
    }

    protected function check(
        array            $dataForCheck,
        HandlerInterface $handler,
        ?string $separator
    ): void
    {
        foreach ($dataForCheck as $inputText => $correctResolvedText) {
            $this->assertEquals($correctResolvedText, $handler->run('', [
                $inputText,
                $separator
            ]));
        }
    }
}
