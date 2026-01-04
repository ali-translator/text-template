<?php

namespace ALI\TextTemplate\TemplateResolver\Node\Definition;

use ALI\TextTemplate\TemplateResolver\Node\NodeTag;

interface LoopVariableProviderInterface
{
    /**
     * @param NodeTag[] $tags
     * @return string[]
     */
    public function getLoopVariables(array $tags): array;
}
