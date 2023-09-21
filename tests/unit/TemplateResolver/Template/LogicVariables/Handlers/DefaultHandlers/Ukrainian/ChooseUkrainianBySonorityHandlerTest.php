<?php

namespace ALI\TextTemplate\Tests\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Ukrainian;

use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Ukrainian\ChoosePrepositionBySonorityHandler;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\HandlerInterface;
use PHPUnit\Framework\TestCase;

class ChooseUkrainianBySonorityHandlerTest extends TestCase
{
    public function test()
    {
        $handler = new ChoosePrepositionBySonorityHandler();

        // If you do not set the "previous letter", it will work as the "beginning of a sentence"
        $config = ['', 'в/у'];
        $dataForCheck = [
            'Києві' => 'у',
            'Одесі' => 'в',
            'Ялті' => 'у',
            'Львові' => 'у',
        ];
        $this->check($dataForCheck, $config, $handler);

        // Set "consonants" as a "previous letter"
        $config = ['н','в/у'];
        $dataForCheck = [
            'Києві' => 'у',
            'Одесі' => 'в',
            'Ялті' => 'у',
            'Львові' => 'у',
        ];
        $this->check($dataForCheck, $config, $handler);

        // Set "vowel" as a "previous letter"
        $config = ["и",'в/у'];
        $dataForCheck = [
            'Києві' => 'в',
            'Одесі' => 'в',
            'Ялті' => 'в',
            'Львові' => 'у',
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
