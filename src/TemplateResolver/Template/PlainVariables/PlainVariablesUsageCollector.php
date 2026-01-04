<?php

namespace ALI\TextTemplate\TemplateResolver\Template\PlainVariables;

use ALI\TextTemplate\TemplateResolver\Node\ConditionEvaluator;
use ALI\TextTemplate\TemplateResolver\Node\Exceptions\NodeParsingException;
use ALI\TextTemplate\TemplateResolver\Node\NodeParser;
use ALI\TextTemplate\TemplateResolver\Node\PlainVariablesAwareNodeInterface;
use ALI\TextTemplate\TemplateResolver\Template\Exceptions\VariableResolvingException;
use ALI\TextTemplate\TemplateResolver\Template\KeyGenerators\KeyGenerator;
use ALI\TextTemplate\TemplateResolver\Template\KeyGenerators\TextKeysHandler;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Exceptions\LogicVariableParsingExcepting;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\LogicVariableParser;

class PlainVariablesUsageCollector
{
    private NodeParser $nodeParser;
    private TextKeysHandler $textKeysHandler;
    private KeyGenerator $keyGenerator;
    private LogicVariableParser $logicVariableParser;
    private ConditionEvaluator $conditionEvaluator;
    private bool $silentMode;
    private PlainVariablesTypeMap $typeMap;

    public function __construct(
        NodeParser $nodeParser,
        TextKeysHandler $textKeysHandler,
        KeyGenerator $keyGenerator,
        LogicVariableParser $logicVariableParser,
        ConditionEvaluator $conditionEvaluator,
        bool $silentMode = true
    )
    {
        $this->nodeParser = $nodeParser;
        $this->textKeysHandler = $textKeysHandler;
        $this->keyGenerator = $keyGenerator;
        $this->logicVariableParser = $logicVariableParser;
        $this->conditionEvaluator = $conditionEvaluator;
        $this->silentMode = $silentMode;
        $this->typeMap = new PlainVariablesTypeMap();
    }

    public function collect(string $content): array
    {
        $this->typeMap = new PlainVariablesTypeMap();
        $this->collectFromContent($content, []);

        return $this->typeMap->toArray();
    }

    /**
     * @param array<string, array<int, string>> $aliases
     */
    public function collectFromContent(string $content, array $aliases): void
    {
        $parseResult = null;
        if (NodeParser::hasNodeTags($content)) {
            try {
                $parseResult = $this->nodeParser->parse($content);
            } catch (NodeParsingException $exception) {
                if (!$this->silentMode) {
                    throw $exception;
                }
            }
        }

        if ($parseResult && $parseResult->hasNodes()) {
            $nodeIds = array_fill_keys(array_keys($parseResult->getNodes()), true);
            $this->collectPlainVariables($parseResult->getContent(), $nodeIds, $aliases);

            $context = $this->createContext($aliases);
            foreach ($parseResult->getNodes() as $nodeId => $node) {
                if ($node instanceof PlainVariablesAwareNodeInterface) {
                    $node->collectPlainVariables($context);
                    continue;
                }

                $nodeContent = $parseResult->getNodeContent($nodeId);
                if ($nodeContent !== null) {
                    $this->collectFromContent($nodeContent, $aliases);
                }
            }

            return;
        }

        $this->collectPlainVariables($content, [], $aliases);
    }

    /**
     * @param array<string, bool> $nodeIds
     * @param array<string, array<int, string>> $aliases
     */
    private function collectPlainVariables(string $content, array $nodeIds, array $aliases): void
    {
        $allKeys = $this->textKeysHandler->getAllKeys($this->keyGenerator, $content);
        foreach ($allKeys as $templateKey) {
            if (NodeParser::isNodeTagKey($templateKey)) {
                continue;
            }

            if ($nodeIds && isset($nodeIds[$templateKey])) {
                continue;
            }

            if ($this->logicVariableParser->isTextLogicalVariable($templateKey)) {
                try {
                    $logicVariableData = $this->logicVariableParser->parse($templateKey);
                } catch (LogicVariableParsingExcepting $excepting) {
                    if (!$this->silentMode) {
                        throw new VariableResolvingException($templateKey, $excepting->getMessage());
                    }
                    continue;
                }

                foreach ($logicVariableData->getAllPlainVariablesNames() as $plainVariableName) {
                    $this->addVariableWithAliases($plainVariableName, PlainVariablesTypeMap::TYPE_STRING, $aliases);
                }
                continue;
            }

            $this->addVariableWithAliases($templateKey, PlainVariablesTypeMap::TYPE_STRING, $aliases);
        }
    }

    /**
     * @param array<string, array<int, string>> $aliases
     */
    public function collectConditionExpression(string $expression, array $aliases): void
    {
        $variables = $this->conditionEvaluator->getVariablesWithTypes($expression);
        foreach ($variables as $variable => $type) {
            $this->addVariableWithAliases($variable, $type, $aliases);
        }
    }

    /**
     * @param array<string, array<int, string>> $aliases
     */
    public function createLoopContext(string $itemName, string $collectionName, array $aliases): PlainVariablesUsageContextInterface
    {
        $collectionPath = $this->resolvePath($collectionName, $aliases);
        if ($collectionPath) {
            $this->typeMap->addArray($collectionPath);
        }

        $nestedAliases = $aliases;
        if ($collectionPath) {
            $nestedAliases[$itemName] = $collectionPath;
        }

        return $this->createContext($nestedAliases);
    }

    /**
     * @param array<string, array<int, string>> $aliases
     */
    public function addVariableWithAliases(string $name, string $type, array $aliases): void
    {
        $path = $this->splitPath($name);
        if (!$path) {
            return;
        }

        $alias = $path[0];
        if (isset($aliases[$alias])) {
            $collectionPath = $aliases[$alias];
            if (!$collectionPath) {
                return;
            }

            $itemPath = array_slice($path, 1);
            if (!$itemPath) {
                $this->typeMap->addArrayItemScalar($collectionPath, $type);
            } else {
                $this->typeMap->addArrayItemPropertyScalar($collectionPath, $itemPath, $type);
            }
            return;
        }

        $this->typeMap->addScalar($path, $type);
    }

    /**
     * @param array<string, array<int, string>> $aliases
     * @return string[]
     */
    private function resolvePath(string $name, array $aliases): array
    {
        $segments = $this->splitPath($name);
        if (!$segments) {
            return [];
        }

        $first = $segments[0];
        if (isset($aliases[$first])) {
            return array_merge($aliases[$first], array_slice($segments, 1));
        }

        return $segments;
    }

    /**
     * @return string[]
     */
    private function splitPath(string $name): array
    {
        $name = trim($name);
        if ($name === '') {
            return [];
        }

        $segments = array_map('trim', explode('.', $name));
        $segments = array_filter($segments, static function (string $segment): bool {
            return $segment !== '';
        });

        return array_values($segments);
    }

    /**
     * @param array<string, array<int, string>> $aliases
     */
    private function createContext(array $aliases): PlainVariablesUsageContextInterface
    {
        return new PlainVariablesUsageContext($this, $aliases);
    }
}
