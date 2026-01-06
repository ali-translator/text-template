<?php

namespace ALI\TextTemplate\TemplateResolver\Node;

interface NodeInterface
{
    public function resolve(NodeRuntime $runtime): string;
}
