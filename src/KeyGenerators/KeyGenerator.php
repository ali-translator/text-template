<?php

namespace ALI\TextTemplate\KeyGenerators;

interface KeyGenerator
{
    public function generateKey(string $contentId): string;

    public function getRegularExpression(): string;
}
