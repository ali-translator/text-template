<?php

namespace ALI\TextTemplate\TemplateResolver\Node\IfNode;

use ALI\TextTemplate\TemplateResolver\Node\NodeInterface;
use ALI\TextTemplate\TemplateResolver\Node\NodeRuntime;
use ALI\TextTemplate\TemplateResolver\Node\PlainVariablesAwareNodeInterface;
use ALI\TextTemplate\TemplateResolver\Template\PlainVariables\PlainVariablesUsageContextInterface;

class IfNode implements PlainVariablesAwareNodeInterface, NodeInterface
{
    /**
     * @var IfNodeBranch[]
     */
    private array $branches;

    /**
     * @param IfNodeBranch[] $branches
     */
    public function __construct(array $branches)
    {
        $this->branches = $branches;
    }

    /**
     * @return IfNodeBranch[]
     */
    public function getBranches(): array
    {
        return $this->branches;
    }

    public function resolve(NodeRuntime $runtime): string
    {
        foreach ($this->branches as $branch) {
            if ($branch->matches($runtime)) {
                return $runtime->resolveContent($branch->getContent());
            }
        }

        return '';
    }

    public function collectPlainVariables(PlainVariablesUsageContextInterface $context): void
    {
        foreach ($this->branches as $branch) {
            $condition = $branch->getCondition();
            if ($condition !== null) {
                $context->collectCondition($condition);
            }

            $context->collectContent($branch->getContent());
        }
    }
}
