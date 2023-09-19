<?php

namespace ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers;

use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Common\FirstCharacterInLowercaseHandler;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Common\FirstCharacterInUppercaseHandler;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Common\PrintHandler;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\HandlerInterface;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\HandlersRepositoryInterface;

class DefaultHandlersFacade
{
    static array $allHandlersClasses = [
        PrintHandler::class,
        FirstCharacterInLowercaseHandler::class,
        FirstCharacterInUppercaseHandler::class,
        Turkish\AddLocativeSuffixHandler::class,
        Ukrainian\ChoosePrepositionBySonorityHandler::class,
        Russian\ChoosePrepositionBySonorityHandler::class,
    ];

    /**
     * @param string[]|null $forLanguagesISO
     * @return HandlerInterface[]
     */
    public function createHandlers(
        ?array $forLanguagesISO
    ): array
    {
        $handlers = [];
        foreach (static::$allHandlersClasses as $handlerClassName) {
            if ($forLanguagesISO !== null) {
                /** @var HandlerInterface $handlerClassName */
                $allowedLanguagesIso = $handlerClassName::getAllowedLanguagesIso();
                if ($allowedLanguagesIso !== null) {
                    // At least one language must be intersected
                    if (!array_intersect($forLanguagesISO, $allowedLanguagesIso)) {
                        continue;
                    }
                }
            }
            $handlers[] = new $handlerClassName();
        }

        return $handlers;
    }

    /**
     * @param string[]|null $forLanguagesISO
     */
    public function registerHandlers(
        HandlersRepositoryInterface $handlersRepository,
        ?array                      $forLanguagesISO
    ): HandlersRepositoryInterface
    {
        $handlersRepository = clone $handlersRepository;
        $handlers = $this->createHandlers($forLanguagesISO);
        foreach ($handlers as $handler) {
            $handlersRepository->addHandler($handler);
        }

        return $handlersRepository;
    }
}
