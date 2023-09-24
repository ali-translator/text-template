<?php

namespace ALI\TextTemplate\Tests\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Common;

use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Common\PrintHandler;
use PHPUnit\Framework\TestCase;

class PrintHandlerTest extends TestCase
{
    public function test()
    {
        $handler = new PrintHandler();

        $this->assertEquals(
            'test',
            $handler->run('test', [])
        );

        $this->assertEquals(
            'test',
            $handler->run('', ['test'])
        );
    }
}
