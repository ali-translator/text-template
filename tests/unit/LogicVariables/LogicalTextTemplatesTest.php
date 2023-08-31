<?php

namespace ALI\TextTemplate\Tests\LogicVariables;

use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Common\FirstCharacterInLowercaseHandler;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Common\FirstCharacterInUppercaseHandler;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\DefaultHandlersFacade;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Turkish\AddTurkishLocativeSuffixHandler;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\HandlersRepository;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\HandlersRepositoryInterface;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\LogicVariableParser;
use PHPUnit\Framework\TestCase;

class LogicalTextTemplatesTest extends TestCase
{
    public function test()
    {
        $logicVariableParser = new LogicVariableParser('|');
        $handlersRepository = (new DefaultHandlersFacade())->registerHandlers(
            new HandlersRepository()
        );

        $this->checkFirstCharacterInLowercaseHandler($logicVariableParser, $handlersRepository);
        $this->checkFirstCharacterInUppercaseHandler($logicVariableParser, $handlersRepository);
        $this->checkAddTurkishLocativeSuffixHandler($logicVariableParser, $handlersRepository);
        $this->checkChooseUkrainianBySonorityHandler($logicVariableParser, $handlersRepository);
        $this->checkFewHandlersInChain($logicVariableParser, $handlersRepository);
        $this->checkEmptyVariableValue($logicVariableParser, $handlersRepository);
    }

    /**
     * @param LogicVariableParser $logicVariableParser
     * @param HandlersRepositoryInterface $handlersRepository
     * @return void
     */
    protected function checkEmptyVariableValue(LogicVariableParser $logicVariableParser, HandlersRepositoryInterface $handlersRepository): void
    {
        $dataForCheck = [
            '' => "",
        ];
        $logicVariableTemplate = 'variable_name|' . AddTurkishLocativeSuffixHandler::getAlias() . '|' . FirstCharacterInUppercaseHandler::getAlias() . '|' . FirstCharacterInLowercaseHandler::getAlias();
        $this->check($dataForCheck, $logicVariableParser, $logicVariableTemplate, $handlersRepository);
    }

    protected function checkFirstCharacterInLowercaseHandler(LogicVariableParser $logicVariableParser, HandlersRepositoryInterface $handlersRepository): void
    {
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
        $logicVariableTemplate = 'variable_name|' . FirstCharacterInLowercaseHandler::getAlias();
        $this->check($dataForCheck, $logicVariableParser, $logicVariableTemplate, $handlersRepository);
    }

    protected function checkFirstCharacterInUppercaseHandler(LogicVariableParser $logicVariableParser, HandlersRepositoryInterface $handlersRepository): void
    {
        $dataForCheck = [
            'Sun' => "Sun",
            'sun' => "Sun",
            'SHop' => "SHop",
            'sHoP' => "SHoP",
            '-SHoP#' => "-SHoP#",
            'i̇stanbul' => "İstanbul",
            'морозиво' => "Морозиво",
        ];
        $logicVariableTemplate = 'variable_name|' . FirstCharacterInUppercaseHandler::getAlias();
        $this->check($dataForCheck, $logicVariableParser, $logicVariableTemplate, $handlersRepository);
    }

    protected function checkAddTurkishLocativeSuffixHandler(LogicVariableParser $logicVariableParser, HandlersRepositoryInterface $handlersRepository): void
    {
        $dataForCheck = [
            'İstanbul' => "İstanbul'da",
            'Düzce' => "Düzce'de",
        ];
        $logicVariableTemplate = 'variable_name|' . AddTurkishLocativeSuffixHandler::getAlias();
        $this->check($dataForCheck, $logicVariableParser, $logicVariableTemplate, $handlersRepository);
    }

    protected function checkChooseUkrainianBySonorityHandler(LogicVariableParser $logicVariableParser, HandlersRepositoryInterface $handlersRepository): void
    {
        // If you do not set the "previous letter", it will work as the "beginning of a sentence"
        $logicVariableTemplate = 'city_name|chooseUkrainianBySonority("в")';
        $dataForCheck = [
            'Києві' => 'у',
            'Одесі' => 'в',
            'Ялті' => 'у',
            'Львові' => 'у',
        ];
        $this->check($dataForCheck, $logicVariableParser, $logicVariableTemplate, $handlersRepository);

        $logicVariableTemplate = 'city_name|chooseUkrainianBySonority("у")';
        $this->check($dataForCheck, $logicVariableParser, $logicVariableTemplate, $handlersRepository);

        // Set "consonants" as a "previous letter"
        $logicVariableTemplate = 'city_name|chooseUkrainianBySonority("в", "н")';
        $dataForCheck = [
            'Києві' => 'у',
            'Одесі' => 'в',
            'Ялті' => 'у',
            'Львові' => 'у',
        ];
        $this->check($dataForCheck, $logicVariableParser, $logicVariableTemplate, $handlersRepository);

        $logicVariableTemplate = 'city_name|chooseUkrainianBySonority("у", "н")';
        $this->check($dataForCheck, $logicVariableParser, $logicVariableTemplate, $handlersRepository);

        // Set "vowel" as a "previous letter"
        $logicVariableTemplate = 'city_name|chooseUkrainianBySonority("в", "и")';
        $dataForCheck = [
            'Києві' => 'в',
            'Одесі' => 'в',
            'Ялті' => 'в',
            'Львові' => 'у',
        ];
        $this->check($dataForCheck, $logicVariableParser, $logicVariableTemplate, $handlersRepository);

        $logicVariableTemplate = 'city_name|chooseUkrainianBySonority("у", "и")';
        $this->check($dataForCheck, $logicVariableParser, $logicVariableTemplate, $handlersRepository);
    }

    /**
     * @param LogicVariableParser $logicVariableParser
     * @param HandlersRepositoryInterface $handlersRepository
     * @return void
     */
    protected function checkFewHandlersInChain(LogicVariableParser $logicVariableParser, HandlersRepositoryInterface $handlersRepository): void
    {
        $dataForCheck = [
            'İstanbul' => "İstanbul'da",
            'düzce' => "Düzce'de",
        ];
        $logicVariableTemplate = 'variable_name|' . AddTurkishLocativeSuffixHandler::getAlias() . '|' . FirstCharacterInUppercaseHandler::getAlias();
        $this->check($dataForCheck, $logicVariableParser, $logicVariableTemplate, $handlersRepository);
    }

    protected function check(
        array                       $dataForCheck,
        LogicVariableParser         $logicVariableParser,
        string                      $logicVariableTemplate,
        HandlersRepositoryInterface $handlersRepository
    ): void
    {
        foreach ($dataForCheck as $cityName => $correctCityNameTransformationResult) {
            $logicVariableData = $logicVariableParser->parse($logicVariableTemplate);
            $resolvedText = $logicVariableData
                ->getOperationConfigChain()
                ->run($cityName, $handlersRepository);

            $this->assertEquals($correctCityNameTransformationResult, $resolvedText);
        }
    }
}
