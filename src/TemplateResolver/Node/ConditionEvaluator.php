<?php

namespace ALI\TextTemplate\TemplateResolver\Node;

use ALI\TextTemplate\TemplateResolver\Template\VariableResolver\CollectionVariableResolver;
use ALI\TextTemplate\TextTemplatesCollection;

class ConditionEvaluator
{
    private CollectionVariableResolver $collectionVariableResolver;

    public function __construct(?CollectionVariableResolver $collectionVariableResolver = null)
    {
        $this->collectionVariableResolver = $collectionVariableResolver ?? new CollectionVariableResolver();
    }

    public function evaluate(string $expression, ?TextTemplatesCollection $textTemplatesCollection): bool
    {
        $expression = $this->normalizeExpression($expression);
        $expression = trim($expression);
        if ($expression === '') {
            return false;
        }

        $split = $this->splitByOperator($expression);
        if (!$split) {
            $operand = $this->parseOperand($expression, $textTemplatesCollection);
            return $this->toBoolean($operand);
        }

        [$leftRaw, $operator, $rightRaw] = $split;

        $left = $this->parseOperand($leftRaw, $textTemplatesCollection);
        $right = $this->parseOperand($rightRaw, $textTemplatesCollection);

        if (in_array($operator, ['>', '>=', '<', '<='], true)) {
            return $this->compareNumbers($left, $right, $operator);
        }

        $equals = $this->compareEquality($left, $right);
        return $operator === '==' ? $equals : !$equals;
    }

    /**
     * @return string[]
     */
    public function getUsedVariables(string $expression): array
    {
        $expression = $this->normalizeExpression($expression);
        $expression = trim($expression);
        if ($expression === '') {
            return [];
        }

        $variables = [];
        $split = $this->splitByOperator($expression);

        if (!$split) {
            $variable = $this->extractVariable($expression);
            if ($variable !== null) {
                $variables[$variable] = $variable;
            }
            return $variables;
        }

        [$leftRaw, , $rightRaw] = $split;

        $leftVariable = $this->extractVariable($leftRaw);
        if ($leftVariable !== null) {
            $variables[$leftVariable] = $leftVariable;
        }

        $rightVariable = $this->extractVariable($rightRaw);
        if ($rightVariable !== null) {
            $variables[$rightVariable] = $rightVariable;
        }

        return $variables;
    }

    /**
     * @return array<string, string>
     */
    public function getVariablesWithTypes(string $expression): array
    {
        $expression = $this->normalizeExpression($expression);
        $expression = trim($expression);
        if ($expression === '') {
            return [];
        }

        $split = $this->splitByOperator($expression);
        if (!$split) {
            $variable = $this->extractVariable($expression);
            if ($variable !== null) {
                return [$variable => 'boolean'];
            }
            return [];
        }

        [$leftRaw, $operator, $rightRaw] = $split;
        $variables = [];

        if (in_array($operator, ['>', '>=', '<', '<='], true)) {
            $this->addVariableTypeFromOperand($variables, $leftRaw, 'number');
            $this->addVariableTypeFromOperand($variables, $rightRaw, 'number');
            return $variables;
        }

        $leftInfo = $this->inferOperandInfo($leftRaw);
        $rightInfo = $this->inferOperandInfo($rightRaw);

        $this->addComparisonType($variables, $leftInfo, $rightInfo);
        $this->addComparisonType($variables, $rightInfo, $leftInfo);

        return $variables;
    }

    private function splitByOperator(string $expression): ?array
    {
        if (!preg_match('/^(?P<left>.+?)\s*(?P<operator>==|!=|>=|<=|>|<)\s*(?P<right>.+)$/s', $expression, $matches)) {
            return null;
        }

        return [trim($matches['left']), $matches['operator'], trim($matches['right'])];
    }

    /**
     * @param array<string, string> $variables
     */
    private function addVariableTypeFromOperand(array &$variables, string $operand, string $type): void
    {
        $variable = $this->extractVariable($operand);
        if ($variable === null) {
            return;
        }

        $variables[$variable] = $type;
    }

    /**
     * @return array{name: string|null, type: string|null, isVariable: bool}
     */
    private function inferOperandInfo(string $operand): array
    {
        $operand = trim($operand);
        if ($operand === '') {
            return [
                'name' => null,
                'type' => null,
                'isVariable' => false,
            ];
        }

        if ($this->isQuotedString($operand)) {
            return [
                'name' => null,
                'type' => 'string',
                'isVariable' => false,
            ];
        }

        $lowerOperand = strtolower($operand);
        if ($lowerOperand === 'true' || $lowerOperand === 'false') {
            return [
                'name' => null,
                'type' => 'boolean',
                'isVariable' => false,
            ];
        }

        if (is_numeric($operand)) {
            return [
                'name' => null,
                'type' => 'number',
                'isVariable' => false,
            ];
        }

        return [
            'name' => $operand,
            'type' => null,
            'isVariable' => true,
        ];
    }

    /**
     * @param array<string, string> $variables
     * @param array{name: string|null, type: string|null, isVariable: bool} $variableInfo
     * @param array{name: string|null, type: string|null, isVariable: bool} $otherInfo
     */
    private function addComparisonType(array &$variables, array $variableInfo, array $otherInfo): void
    {
        if (!$variableInfo['isVariable'] || !$variableInfo['name']) {
            return;
        }

        $type = $otherInfo['type'] ?? null;
        if (!$type) {
            $type = 'string';
        }

        $variables[$variableInfo['name']] = $type;
    }

    private function parseOperand(string $operand, ?TextTemplatesCollection $textTemplatesCollection): array
    {
        $operand = trim($operand);
        if ($operand === '') {
            return ['type' => 'null', 'value' => null];
        }

        if ($this->isQuotedString($operand)) {
            return ['type' => 'string', 'value' => $this->stripQuotes($operand)];
        }

        $lowerOperand = strtolower($operand);
        if ($lowerOperand === 'true' || $lowerOperand === 'false') {
            return ['type' => 'bool', 'value' => $lowerOperand === 'true'];
        }

        if (is_numeric($operand)) {
            return ['type' => 'number', 'value' => $operand + 0];
        }

        $resolvedValue = $this->resolveVariableValue($operand, $textTemplatesCollection);
        return $this->normalizeValue($resolvedValue);
    }

    private function extractVariable(string $operand): ?string
    {
        $operand = trim($operand);
        if ($operand === '') {
            return null;
        }

        if ($this->isQuotedString($operand)) {
            return null;
        }

        $lowerOperand = strtolower($operand);
        if ($lowerOperand === 'true' || $lowerOperand === 'false') {
            return null;
        }

        if (is_numeric($operand)) {
            return null;
        }

        return $operand;
    }

    private function normalizeValue($value): array
    {
        if ($value === null) {
            return ['type' => 'null', 'value' => null];
        }

        if (is_array($value)) {
            return ['type' => 'array', 'value' => $value];
        }

        if (is_bool($value)) {
            return ['type' => 'bool', 'value' => $value];
        }

        if (is_int($value) || is_float($value)) {
            return ['type' => 'number', 'value' => $value];
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            $lower = strtolower($trimmed);
            if ($lower === 'true' || $lower === 'false') {
                return ['type' => 'bool', 'value' => $lower === 'true'];
            }
            if ($trimmed !== '' && is_numeric($trimmed)) {
                return ['type' => 'number', 'value' => $trimmed + 0];
            }
            return ['type' => 'string', 'value' => $value];
        }

        if (is_object($value)) {
            return ['type' => 'object', 'value' => $value];
        }

        return ['type' => 'string', 'value' => (string)$value];
    }

    private function toBoolean(array $value): bool
    {
        switch ($value['type']) {
            case 'array':
                return count($value['value']) > 0;
            case 'bool':
                return $value['value'];
            case 'number':
                return (float)$value['value'] !== 0.0;
            case 'null':
                return false;
            case 'object':
                if ($value['value'] instanceof \Countable) {
                    return count($value['value']) > 0;
                }
                return true;
            case 'string':
            default:
                $trimmed = strtolower(trim((string)$value['value']));
                if ($trimmed === '' || $trimmed === '0' || $trimmed === 'false' || $trimmed === 'off' || $trimmed === 'no') {
                    return false;
                }
                return true;
        }
    }

    private function compareNumbers(array $left, array $right, string $operator): bool
    {
        $leftNumber = $this->toNumber($left);
        $rightNumber = $this->toNumber($right);

        if ($leftNumber === null || $rightNumber === null) {
            return false;
        }

        switch ($operator) {
            case '>':
                return $leftNumber > $rightNumber;
            case '>=':
                return $leftNumber >= $rightNumber;
            case '<':
                return $leftNumber < $rightNumber;
            case '<=':
                return $leftNumber <= $rightNumber;
        }

        return false;
    }

    private function toNumber(array $value): ?float
    {
        if ($value['type'] === 'number') {
            return (float)$value['value'];
        }

        if ($value['type'] === 'bool') {
            return $value['value'] ? 1.0 : 0.0;
        }

        if ($value['type'] === 'string' && is_numeric(trim((string)$value['value']))) {
            return (float)$value['value'];
        }

        return null;
    }

    private function compareEquality(array $left, array $right): bool
    {
        if ($left['type'] === 'number' && $right['type'] === 'number') {
            return (float)$left['value'] === (float)$right['value'];
        }

        if ($left['type'] === 'bool' || $right['type'] === 'bool') {
            return $this->toBoolean($left) === $this->toBoolean($right);
        }

        if ($left['type'] === 'null' || $right['type'] === 'null') {
            return $left['type'] === 'null' && $right['type'] === 'null';
        }

        if ($left['type'] === 'array' && $right['type'] === 'array') {
            return $left['value'] == $right['value'];
        }

        if ($left['type'] === 'object' && $right['type'] === 'object') {
            return $left['value'] == $right['value'];
        }

        return (string)$left['value'] === (string)$right['value'];
    }

    private function resolveVariableValue(string $name, ?TextTemplatesCollection $textTemplatesCollection)
    {
        $templateItem = $this->collectionVariableResolver->find($textTemplatesCollection, $name);
        if (!$templateItem) {
            return null;
        }

        if ($templateItem->hasRawValue()) {
            return $templateItem->getRawValue();
        }

        return $templateItem->resolve();
    }

    private function isQuotedString(string $value): bool
    {
        if (strlen($value) < 2) {
            return false;
        }

        $start = $value[0];
        $end = $value[strlen($value) - 1];

        return ($start === '"' && $end === '"') || ($start === "'" && $end === "'");
    }

    private function stripQuotes(string $value): string
    {
        if (!$this->isQuotedString($value)) {
            return $value;
        }

        return substr($value, 1, -1);
    }

    private function normalizeExpression(string $expression): string
    {
        if ($expression === '') {
            return $expression;
        }

        if (strpos($expression, '&') === false) {
            return $expression;
        }

        $hasGt = strpos($expression, '&gt;') !== false;
        $hasLt = strpos($expression, '&lt;') !== false;
        if (!$hasGt && !$hasLt) {
            return $expression;
        }

        if (strpos($expression, "'") === false && strpos($expression, '"') === false) {
            return strtr($expression, [
                '&gt;' => '>',
                '&lt;' => '<',
            ]);
        }

        $length = strlen($expression);
        $result = '';
        $inSingle = false;
        $inDouble = false;
        $changed = false;

        for ($i = 0; $i < $length; $i++) {
            $char = $expression[$i];
            if ($char === "'" && !$inDouble) {
                $inSingle = !$inSingle;
                $result .= $char;
                continue;
            }

            if ($char === '"' && !$inSingle) {
                $inDouble = !$inDouble;
                $result .= $char;
                continue;
            }

            if (!$inSingle && !$inDouble && $char === '&') {
                if ($hasGt && substr($expression, $i, 4) === '&gt;') {
                    $result .= '>';
                    $i += 3;
                    $changed = true;
                    continue;
                }

                if ($hasLt && substr($expression, $i, 4) === '&lt;') {
                    $result .= '<';
                    $i += 3;
                    $changed = true;
                    continue;
                }
            }

            $result .= $char;
        }

        return $changed ? $result : $expression;
    }
}
