<?php

namespace ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers;

use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Common\FirstCharacterInLowercaseHandler;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Common\FirstCharacterInUppercaseHandler;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Common\HideHandler;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Common\PluralHandler;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Common\PrintHandler;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Turkish\AddDirectionalSuffixHandler;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Turkish\AddLocativeSuffixHandler;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Turkish\ChooseDirectionalSuffixHandler;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Turkish\ChooseLocativeSuffixHandler;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Turkish\Services\DirectionalSuffixChooser;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Turkish\Services\LocativeSuffixChooser;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\HandlerInterface;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\HandlersRepositoryInterface;

class DefaultHandlersFacade
{
    static array $allDefaultHandlersClasses = [
        PrintHandler::class,
        HideHandler::class,
        FirstCharacterInLowercaseHandler::class,
        FirstCharacterInUppercaseHandler::class,
        PluralHandler::class,
        Turkish\AddDirectionalSuffixHandler::class,
        Turkish\ChooseDirectionalSuffixHandler::class,
        Turkish\AddLocativeSuffixHandler::class,
        Turkish\ChooseLocativeSuffixHandler::class,
        Ukrainian\ChoosePrepositionBySonorityHandler::class,
        Russian\ChoosePrepositionBySonorityHandler::class,
    ];

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

    /**
     * @param string[]|null $forLanguagesISO
     * @return HandlerInterface[]
     */
    public function createHandlers(
        ?array $forLanguagesISO
    ): array
    {
        $handlers = [];
        foreach (static::$allDefaultHandlersClasses as $handlerClassName) {
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

            switch ($handlerClassName) {
                case AddDirectionalSuffixHandler::class:
                    $handlers[] = new AddDirectionalSuffixHandler(new DirectionalSuffixChooser());
                    break;
                case ChooseDirectionalSuffixHandler::class:
                    $handlers[] = new ChooseDirectionalSuffixHandler(new DirectionalSuffixChooser());
                    break;
                case AddLocativeSuffixHandler::class:
                    $handlers[] = new AddLocativeSuffixHandler(new LocativeSuffixChooser());
                    break;
                case ChooseLocativeSuffixHandler::class:
                    $handlers[] = new ChooseLocativeSuffixHandler(new LocativeSuffixChooser());
                    break;
                case PluralHandler::class:
                    $handlers[] = new PluralHandler($forLanguagesISO ? current($forLanguagesISO) : 'en');
                    break;
                default:
                    $handlers[] = new $handlerClassName();
                    break;
            }
        }

        return $handlers;
    }
}
