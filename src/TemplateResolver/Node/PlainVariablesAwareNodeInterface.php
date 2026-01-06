<?php

namespace ALI\TextTemplate\TemplateResolver\Node;

use ALI\TextTemplate\TemplateResolver\Template\PlainVariables\PlainVariablesUsageContextInterface;

interface PlainVariablesAwareNodeInterface
{
    public function collectPlainVariables(PlainVariablesUsageContextInterface $context): void;
}
