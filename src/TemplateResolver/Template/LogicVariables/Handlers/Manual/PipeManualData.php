<?php

namespace ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Manual;

class PipeManualData
{
    protected bool $isAllow;
    protected bool $isRequired;
    protected ?string $name;
    protected ?string $description;
    protected array $exampleValues;

    public function __construct(
        bool $isAllow,
        bool $isRequired,
        ?string $name,
        ?string $description,
        array $exampleValues = []
    )
    {
        $this->isAllow = $isAllow;
        $this->isRequired = $isRequired;
        $this->name = $name;
        $this->description = $description;
        $this->exampleValues = $exampleValues;
    }

    public function isAllow(): bool
    {
        return $this->isAllow;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return string[]
     */
    public function getExampleValues(): array
    {
        return $this->exampleValues;
    }
}
