<?php

namespace ALI\TextTemplate\TemplateResolver\Node;

class NodeKeyGenerator
{
    private int $index = 1;

    public function nextId(): string
    {
        return 'node_number_' . $this->index++;
    }
}
