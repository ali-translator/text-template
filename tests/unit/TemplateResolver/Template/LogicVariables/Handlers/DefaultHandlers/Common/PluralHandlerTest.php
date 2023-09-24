<?php

namespace ALI\TextTemplate\Tests\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Common;

use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Common\PluralHandler;
use PHPUnit\Framework\TestCase;

class PluralHandlerTest extends TestCase
{
    public function test()
    {
        $handler = new PluralHandler();

        $config = '=0[Zero] =1[One] other[Unknown #]';

        $this->assertEquals('Zero', $handler->run('', [
            0, $config
        ]));
        $this->assertEquals('One', $handler->run('', [
            1, $config
        ]));
        $this->assertEquals('Unknown 50', $handler->run('', [
            50, $config
        ]));
    }
}
