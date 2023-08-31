<?php

namespace ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers;

use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\HandlerInterface;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Common\FirstCharacterInLowercaseHandler;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Common\FirstCharacterInUppercaseHandler;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Turkish\AddTurkishLocativeSuffixHandler;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Ukrainian\ChooseUkrainianBySonorityHandler;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\HandlersRepositoryInterface;

class DefaultHandlersFacade
{
    /**
     * @var HandlerInterface[]
     */
    private array $_handlers;

    /**
     * @return HandlerInterface[]
     */
    public function getAllHandlers(): array
    {
        if (!isset($this->_handlers)) {
            $this->_handlers = [
                new FirstCharacterInLowercaseHandler(),
                new FirstCharacterInUppercaseHandler(),
                new AddTurkishLocativeSuffixHandler(),
                new ChooseUkrainianBySonorityHandler(),
            ];
        }
        return $this->_handlers;
    }

    public function registerHandlers(
        HandlersRepositoryInterface $handlersRepository
    ): HandlersRepositoryInterface
    {
        $handlersRepository = clone $handlersRepository;
        $handlers = $this->getAllHandlers();
        foreach ($handlers as $handler) {
            $handlersRepository->addHandler($handler);
        }

        return $handlersRepository;
    }
}
