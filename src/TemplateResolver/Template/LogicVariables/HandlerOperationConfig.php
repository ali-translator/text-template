<?php

namespace ALI\TextTemplate\TemplateResolver\Template\LogicVariables;

use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Exceptions\HandlerArgumentUndefinedVariableExcepting;
use ALI\TextTemplate\TemplateResolver\Template\VariableResolver\CollectionVariableResolver;
use ALI\TextTemplate\TextTemplatesCollection;

class HandlerOperationConfig
{
    private string $handlerAlias;
    private array $rawConfig;
    private CollectionVariableResolver $collectionVariableResolver;

    public function __construct(
        string $handlerAlias,
        array $rawConfig = [],
        ?CollectionVariableResolver $collectionVariableResolver = null
    )
    {
        $this->handlerAlias = $handlerAlias;
        $this->rawConfig = $rawConfig;
        $this->collectionVariableResolver = $collectionVariableResolver ?? new CollectionVariableResolver();
    }

    public function getHandlerAlias(): string
    {
        return $this->handlerAlias;
    }

    public function resolveConfig(
        TextTemplatesCollection $variablesCollection
    ): array
    {
        $config = [];
        foreach ($this->rawConfig as $key => $value) {
            if (is_array($value) && $value['type'] === 'variable') {
                $templateItem = $this->collectionVariableResolver->find($variablesCollection, $value['value']);
                if ($templateItem) {
                    $config[$key] = $templateItem->resolve();
                } else {
                    throw new HandlerArgumentUndefinedVariableExcepting($value['value'], $key + 1);
                }
            } else {
                $config[$key] = $value;
            }
        }

        return $config;
    }

    public function getRawConfig(): array
    {
        return $this->rawConfig;
    }

    /**
     * @return string[]
     */
    public function getAllPlainVariablesNames(): array
    {
        $plainVariablesNames = [];
        foreach ($this->rawConfig as $key => $value) {
            if (is_array($value) && $value['type'] === 'variable') {
                $plainVariablesNames[] = $value['value'];
            }
        }

        return $plainVariablesNames;
    }
}
