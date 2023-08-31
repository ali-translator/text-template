<?php

namespace ALI\TextTemplate\TemplateResolver\Template\KeyGenerators;

interface KeyGenerator
{
    public function generateKey(string $contentId): string;

    public function getRegularExpression($regDelimiter = '/'): string;
}
