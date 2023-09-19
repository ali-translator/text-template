<?php

namespace ALI\TextTemplate\Tests\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Common;

use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Common\FirstCharacterInUppercaseHandler;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\HandlerInterface;
use PHPUnit\Framework\TestCase;

class FirstCharacterInUppercaseHandlerTest extends TestCase
{
    public function test()
    {
        $handler = new FirstCharacterInUppercaseHandler();

        $dataForCheck = [
            'Sun' => "Sun",
            'sun' => "Sun",
            'SHop' => "SHop",
            'sHoP' => "SHoP",
            '-SHoP#' => "-SHoP#",
            'i̇stanbul' => "İstanbul",
            'морозиво' => "Морозиво",
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
