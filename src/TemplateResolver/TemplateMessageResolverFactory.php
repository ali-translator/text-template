<?php

namespace ALI\TextTemplate\TemplateResolver;

use ALI\TextTemplate\MessageFormat\MessageFormatsEnum;
use ALI\TextTemplate\TemplateResolver\Plain\PlainTextMessageResolver;
use ALI\TextTemplate\TemplateResolver\Plural\PluralTemplateMessageResolver;
use ALI\TextTemplate\TemplateResolver\Template\KeyGenerators\KeyGenerator;
use ALI\TextTemplate\TemplateResolver\Template\KeyGenerators\StaticKeyGenerator;
use ALI\TextTemplate\TemplateResolver\Template\KeyGenerators\TextKeysHandler;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\DefaultHandlersFacade;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\HandlersRepository;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\HandlersRepositoryInterface;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\LogicVariableParser;
use ALI\TextTemplate\TemplateResolver\Template\TextTemplateMessageResolver;
use RuntimeException;

class TemplateMessageResolverFactory
{
    protected KeyGenerator $keyGenerator;
    protected string $locale;

    protected ?LogicVariableParser $logicVariableParser;
    protected TextKeysHandler $textKeysHandler;
    protected ?HandlersRepositoryInterface $handlersRepository;

    // "SilentMode" will catch all parser errors and not pass them to you
    private bool $silentMode;

    public function __construct(
        string        $locale,
        ?KeyGenerator $keyGenerator = null,
        ?HandlersRepositoryInterface $logicVariableHandlersRepository = null,
        ?LogicVariableParser $logicVariableParser = null,
        bool $silentMode = true
    )
    {
        $this->locale = $locale;
        $this->keyGenerator = $keyGenerator ?: new StaticKeyGenerator('{', '}');
        $this->silentMode = $silentMode;

        // Services for "TEXT_TEMPLATE"
        $this->textKeysHandler = new TextKeysHandler();

        if (!$logicVariableHandlersRepository) {
            $logicVariableHandlersRepository = (new DefaultHandlersFacade())->registerHandlers(
                new HandlersRepository(),
                [$locale]
            );
        }
        $this->handlersRepository = $logicVariableHandlersRepository;

        if (!$logicVariableParser) {
            $logicVariableParser = new LogicVariableParser();
        }
        $this->logicVariableParser = $logicVariableParser;
    }

    private array $cachedTemplateMessageResolvers = [];

    /**
     * @param string|null $messageFormat
     * @return TemplateMessageResolver
     * @see MessageFormatsEnum
     */
    public function generateTemplateMessageResolver(?string $messageFormat): TemplateMessageResolver
    {
        $messageFormat = $messageFormat ?? MessageFormatsEnum::TEXT_TEMPLATE;

        if (isset($this->cachedTemplateMessageResolvers[$messageFormat])) {
            return $this->cachedTemplateMessageResolvers[$messageFormat];
        }

        // TODO in next iterations - leave only MessageFormatsEnum::TEXT_TEMPLATE one
        switch ($messageFormat) {
            case MessageFormatsEnum::TEXT_TEMPLATE:
                $templateMessageResolver = new TextTemplateMessageResolver(
                    $this->keyGenerator,
                    $this->handlersRepository,
                    $this->logicVariableParser,
                    $this->silentMode
                );
                break;
            case MessageFormatsEnum::MESSAGE_FORMATTER:
            case MessageFormatsEnum::PLURAL_TEMPLATE:
                $templateMessageResolver = new PluralTemplateMessageResolver($this->locale);
                break;
            case MessageFormatsEnum::PLAIN_TEXT:
                $templateMessageResolver = new PlainTextMessageResolver();
                break;
            default:
                throw new RuntimeException('Undefined message format "' . $messageFormat . '"');
        }

        $this->cachedTemplateMessageResolvers[$messageFormat] = $templateMessageResolver;

        return $templateMessageResolver;
    }
}
