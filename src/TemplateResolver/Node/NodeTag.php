<?php

namespace ALI\TextTemplate\TemplateResolver\Node;

class NodeTag
{
    private string $name;
    private ?string $arguments;
    private int $start;
    private int $end;

    public function __construct(string $name, ?string $arguments, int $start, int $end)
    {
        $this->name = $name;
        $this->arguments = $arguments;
        $this->start = $start;
        $this->end = $end;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getArguments(): ?string
    {
        return $this->arguments;
    }

    public function getStart(): int
    {
        return $this->start;
    }

    public function getEnd(): int
    {
        return $this->end;
    }
}
