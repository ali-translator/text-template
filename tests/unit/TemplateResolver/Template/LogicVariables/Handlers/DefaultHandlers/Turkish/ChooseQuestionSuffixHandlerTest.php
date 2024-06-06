<?php

namespace ALI\TextTemplate\Tests\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Turkish;

use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Turkish\ChooseQuestionSuffixHandler;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\HandlerInterface;
use PHPUnit\Framework\TestCase;

class ChooseQuestionSuffixHandlerTest extends TestCase
{
    public function test()
    {
        $handler = new ChooseQuestionSuffixHandler();

        $dataForCheck = [
            'İstanbul' => "mu",
            'Ankara' => "mı",
            'İzmir' => "mi",
            'Bolu' => "mu",
            'Bursa' => "mı",
            'Kocaeli' => "mi",
        ];
        $this->check($dataForCheck, $handler);
    }

    protected function check(
        array $dataForCheck,
        HandlerInterface $handler
    ): void
    {
        foreach ($dataForCheck as $inputText => $correctSuffix) {
            $this->assertEquals($correctSuffix, $handler->run('', [$inputText]));
        }
    }
}
