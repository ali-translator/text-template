<?php

namespace ALI\TextTemplate\KeyGenerators;

class StaticKeyGenerator implements KeyGenerator
{
    protected string $keyPrefix;

    protected string $keyPostfix;

    public function __construct(string $keyPrefix, string $keyPostfix)
    {
        $this->keyPrefix = $keyPrefix;
        $this->keyPostfix = $keyPostfix;
    }

    public function generateKey(string $contentId): string
    {
        return $this->keyPrefix . $contentId . $this->keyPostfix;
    }

    public function getRegularExpression($regDelimiter = '/'): string
    {
        return $regDelimiter . preg_quote($this->keyPrefix, $regDelimiter) . '(?P<id>[-_a-zA-Z0-9%#\$\^\*\(\)\&\!\?,.]+?)' . preg_quote($this->keyPostfix, $regDelimiter) . $regDelimiter;
    }

    public function getKeyPrefix(): string
    {
        return $this->keyPrefix;
    }

    public function getKeyPostfix(): string
    {
        return $this->keyPostfix;
    }
}
