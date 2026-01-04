<?php

namespace ALI\TextTemplate\TemplateResolver\Node\IfNode;

use ALI\TextTemplate\TemplateResolver\Node\NodeRuntime;

class IfNodeBranch
{
    private ?string $condition;
    private string $content;

    public function __construct(?string $condition, string $content)
    {
        $this->condition = $condition;
        $this->content = $content;
    }

    public function matches(NodeRuntime $runtime): bool
    {
        if ($this->condition === null) {
            return true;
        }

        return $runtime->evaluateCondition($this->condition);
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getCondition(): ?string
    {
        return $this->condition;
    }
}
