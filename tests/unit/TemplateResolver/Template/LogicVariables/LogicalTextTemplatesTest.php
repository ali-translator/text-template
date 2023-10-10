<?php

namespace ALI\TextTemplate\Tests\TemplateResolver\Template\LogicVariables;

use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Common\FirstCharacterInLowercaseHandler;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Common\FirstCharacterInUppercaseHandler;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\DefaultHandlersFacade;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Turkish\AddLocativeSuffixHandler;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Exceptions\UndefinedHandlerException;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\HandlersRepository;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\HandlersRepositoryInterface;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\LogicVariableParser;
use ALI\TextTemplate\TextTemplateItem;
use ALI\TextTemplate\TextTemplatesCollection;
use PHPUnit\Framework\TestCase;

class LogicalTextTemplatesTest extends TestCase
{
    public function test()
    {
        $logicVariableParser = new LogicVariableParser('|');
        $handlersRepository = (new DefaultHandlersFacade())->registerHandlers(
            new HandlersRepository(),
            null
        );

        $this->checkEmptyVariableValue($logicVariableParser, $handlersRepository);
        $this->checkHandlerWithParameters($logicVariableParser, $handlersRepository);
        $this->checkFewHandlersInChain($logicVariableParser, $handlersRepository);
    }

    public function testHandlerWithSpecificLanguage()
    {
        $logicVariableParser = new LogicVariableParser('|');
        $handlersRepository = (new DefaultHandlersFacade())->registerHandlers(
            new HandlersRepository(),
            ['uk']
        );

        // Correct resolving
        $logicVariableTemplate = '|uk_choosePreposition("Розваги","в/у", city_name)';
        $dataForCheck = ['Києві' => 'в'];
        $this->check($dataForCheck, $logicVariableParser, $logicVariableTemplate, $handlersRepository);

        // Use handler for another language(which not included)
        $logicVariableTemplate = AddLocativeSuffixHandler::getAlias() . '(city_name)';
        $dataForCheck = [
            'İstanbul' => '',
        ];
        try {
            $this->check($dataForCheck, $logicVariableParser, $logicVariableTemplate, $handlersRepository);
            $allOk = false;
        } catch (UndefinedHandlerException $exception) {
            $allOk = true;
        }
        $this->assertEquals(true, $allOk);
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
        $logicVariableTemplate = '|'.AddLocativeSuffixHandler::getAlias() . '(city_name, "")|' . FirstCharacterInUppercaseHandler::getAlias() . '|' . FirstCharacterInLowercaseHandler::getAlias();
        $this->check($dataForCheck, $logicVariableParser, $logicVariableTemplate, $handlersRepository);
    }

    protected function checkHandlerWithParameters(LogicVariableParser $logicVariableParser, HandlersRepositoryInterface $handlersRepository): void
    {
        $logicVariableTemplate = '|uk_choosePreposition("и","в/у", city_name)';
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
        $logicVariableTemplate = '|' . AddLocativeSuffixHandler::getAlias() . '(city_name)|' . FirstCharacterInUppercaseHandler::getAlias();
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
            $variablesCollection = new TextTemplatesCollection();
            $variablesCollection->add(new TextTemplateItem($cityName), 'city_name');

            $logicVariableData = $logicVariableParser->parse($logicVariableTemplate);
            $resolvedText = $logicVariableData
                ->run($variablesCollection, $handlersRepository);

            $this->assertEquals($correctCityNameTransformationResult, $resolvedText);
        }
    }
}
