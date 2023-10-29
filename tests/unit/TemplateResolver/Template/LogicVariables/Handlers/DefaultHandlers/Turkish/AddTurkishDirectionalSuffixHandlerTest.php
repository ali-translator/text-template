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

        $dataForCheck = [
            'Ev' => "Ev'e",
            'Okul' => "Okul'a",
            'Kale' => "Kale'ye",
            'Bahçe' => "Bahçe'ye",
            'Kitap' => "Kitap'a"
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
