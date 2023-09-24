<?php

namespace ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers;

use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Exceptions\HandlerProcessingException;
use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\Manual\HandlerManualData;

interface HandlerInterface
{
    public static function getAlias(): string;

    public static function getAllowedLanguagesIso(): ?array;

    /**
     * @throws HandlerProcessingException
     */
    public function run(string $pipeInputText, array $config): string;

    public static function generateManual(): HandlerManualData;
}
