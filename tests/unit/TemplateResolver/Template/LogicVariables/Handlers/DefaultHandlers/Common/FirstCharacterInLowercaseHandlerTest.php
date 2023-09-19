<?php

namespace ALI\TextTemplate\Tests\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Common;

use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Common\FirstCharacterInLowercaseHandler;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\HandlerInterface;
use PHPUnit\Framework\TestCase;

class FirstCharacterInLowercaseHandlerTest extends TestCase
{
    public function test()
    {
        $handler = new FirstCharacterInLowercaseHandler();

        $dataForCheck = [
            'sun' => "sun",
            'Doggy' => "doggy",
            'Shop' => "shop",
            'sHop' => "sHop",
            'SHop' => "sHop",
            'SHoP' => "sHoP",
            '-SHoP#' => "-SHoP#",
            'İstanbul' => "i̇stanbul",
            'Морозиво' => "морозиво",
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
