<?php

namespace ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Manual;

class HandlerManualData
{
    protected string $alias;
    /**
     * @var string[]|null
     */
    protected ?array $allowedLanguagesIso;
    protected string $description;
    /**
     * @var ArgumentManualData[]
     */
    protected array $arguments;

    protected ?PipeManualData $pipe;

    public function __construct(
        string $alias,
        ?array $allowedLanguagesIso,
        string $description,
        array $arguments,
        ?PipeManualData $pipe
    )
    {
        $this->alias = $alias;
        $this->allowedLanguagesIso = $allowedLanguagesIso;
        $this->description = $description;
        $this->arguments = $arguments;
        $this->pipe = $pipe;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @var ArgumentManualData[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function getPipe(): ?PipeManualData
    {
        return $this->pipe;
    }
}
