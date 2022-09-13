<?php

namespace ALI\TextTemplate\MessageFormat;

class MessageFormatsEnum
{
    // Allow only simple parameters such as "Hello {name}"
    // Allow multi-level structures. Child parameters can be a different message format
    public const TEXT_TEMPLATE = 'tt';

    // Uses the PECL intl packet "MessageFormatter::formatMessage()" to format text (example {0, plural, =0{Zero}=1{One}other{Unknown #}}).
    // Allow only one level structures
    /**
     * @deprecated
     */
    public const MESSAGE_FORMATTER = 'mf';
    public const PLURAL_TEMPLATE = 'mf';

    // Without parameters resolving
    public const PLAIN_TEXT = 'pt';
}
