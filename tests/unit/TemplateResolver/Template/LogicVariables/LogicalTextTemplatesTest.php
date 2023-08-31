<?php

namespace ALI\TextTemplate\Tests\TemplateResolver\Template\LogicVariables;

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

        $this->checkEmptyVariableValue($logicVariableParser, $handlersRepository);
        $this->checkHandlerWithParameters($logicVariableParser, $handlersRepository);
        $this->checkFewHandlersInChain($logicVariableParser, $handlersRepository);
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

    protected function checkHandlerWithParameters(LogicVariableParser $logicVariableParser, HandlersRepositoryInterface $handlersRepository): void
    {
        $logicVariableTemplate = 'city_name|chooseUkrainianBySonority("в", "и")';
        $dataForCheck = [
            'Києві' => 'в',
            'Одесі' => 'в',
            'Ялті' => 'в',
            'Львові' => 'у',
        ];
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
