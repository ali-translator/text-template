<?php

namespace ALI\TextTemplate\TemplateResolver\Node\Definition;

use ALI\TextTemplate\TemplateResolver\Node\NodeTag;

interface ConditionExpressionProviderInterface
{
    /**
     * @param NodeTag[] $tags
     * @return string[]
     */
    public function getConditionExpressions(array $tags): array;
}
