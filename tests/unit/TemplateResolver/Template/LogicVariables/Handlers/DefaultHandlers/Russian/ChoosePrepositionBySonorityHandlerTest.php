<?php

namespace ALI\TextTemplate\Tests\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Russian;

use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\HandlerInterface;
use PHPUnit\Framework\TestCase;

class ChoosePrepositionBySonorityHandlerTest extends TestCase
{
    public function test()
    {
        $handler = new DefaultHandlers\Russian\ChoosePrepositionBySonorityHandler();

        // If you do not set the "previous letter", it will work as the "beginning of a sentence"
        $config = ['во/в'];
        $dataForCheck = [
            'Киев' => 'в',
            'Львов' => 'во',
            'Одесса' => 'в',
        ];
        $this->check($dataForCheck, $config, $handler);
    }

    protected function check(
        array            $dataForCheck,
        array            $config,
        HandlerInterface $handler
    ): void
    {
        foreach ($dataForCheck as $cityName => $correctResolvedText) {
            $currentConfig = $config;
            $currentConfig[] = $cityName;
            $result = $handler->run('', $currentConfig);
            $this->assertEquals($correctResolvedText, $result);
        }
    }
}
