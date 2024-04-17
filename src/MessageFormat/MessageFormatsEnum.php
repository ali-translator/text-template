<?php

namespace ALI\TextTemplate\MessageFormat;

class MessageFormatsEnum
{
    /**
     * Allows simple and logical variables:
     * 'Hello {name}'
     * 'Tom has {plural(appleNumbers, "=0[no one apple] =1[one apple] other[many apples]")}'
     */
    public const TEXT_TEMPLATE = 'tt';

    // Resolving without parameters
    public const PLAIN_TEXT = 'pt';

    /**
     * DEPRECATED - use "TEXT_TEMPLATE" instead with "Logic variables"
     *
     * Uses the PECL intl packet "MessageFormatter::formatMessage()" to format text (example {0, plural, =0{Zero}=1{One}other{Unknown #}}).
     * @deprecated
     */
    public const PLURAL_TEMPLATE = 'mf';
    /**
     * @deprecated
     */
    public const MESSAGE_FORMATTER = 'mf';
}
