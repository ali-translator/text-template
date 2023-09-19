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
    protected PipeManualData $pipe;

    /**
     * @var ArgumentManualData[]
     */
    protected array $arguments;

    public function __construct(
        string $alias,
        ?array $allowedLanguagesIso,
        string $description,
        PipeManualData $pipe,
        array $arguments
    )
    {
        $this->alias = $alias;
        $this->allowedLanguagesIso = $allowedLanguagesIso;
        $this->description = $description;
        $this->pipe = $pipe;
        $this->arguments = $arguments;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getPipe(): PipeManualData
    {
        return $this->pipe;
    }

    /**
     * @var ArgumentManualData[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }
}
