<?php

namespace ALI\TextTemplate\Tests\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Ukrainian;

use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\HandlerInterface;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Ukrainian\ChooseUkrainianBySonorityHandler;
use PHPUnit\Framework\TestCase;

class ChooseUkrainianBySonorityHandlerTest extends TestCase
{
    public function test()
    {
        $handler = new ChooseUkrainianBySonorityHandler();

        // If you do not set the "previous letter", it will work as the "beginning of a sentence"
        $config = ['в'];
        $dataForCheck = [
            'Києві' => 'у',
            'Одесі' => 'в',
            'Ялті' => 'у',
            'Львові' => 'у',
        ];
        $this->check($dataForCheck, $config, $handler);
        $config = ['у']; // revert
        $this->check($dataForCheck, $config, $handler);

        // Set "consonants" as a "previous letter"
        $config = ['в',"н"];
        $dataForCheck = [
            'Києві' => 'у',
            'Одесі' => 'в',
            'Ялті' => 'у',
            'Львові' => 'у',
        ];
        $this->check($dataForCheck, $config, $handler);
        $config = ['у',"н"]; // revert
        $this->check($dataForCheck, $config, $handler);

        // Set "vowel" as a "previous letter"
        $config = ['в',"и"];
        $dataForCheck = [
            'Києві' => 'в',
            'Одесі' => 'в',
            'Ялті' => 'в',
            'Львові' => 'у',
        ];
        $this->check($dataForCheck, $config, $handler);
        $config = ['у',"и"]; // revert
        $this->check($dataForCheck, $config, $handler);
    }

    protected function check(
        array            $dataForCheck,
        array            $config,
        HandlerInterface $handler
    ): void
    {
        foreach ($dataForCheck as $inputText => $correctResolvedText) {
            $this->assertEquals($correctResolvedText, $handler->run($inputText,$config));
        }
    }
}
