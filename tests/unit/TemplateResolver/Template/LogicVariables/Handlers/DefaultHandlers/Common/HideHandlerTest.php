<?php

namespace ALI\TextTemplate\Tests\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Common;

use ALI\TextTemplate\TemplateResolver\Template\LogicVariables\Handlers\DefaultHandlers\Common\HideHandler;
use PHPUnit\Framework\TestCase;

class HideHandlerTest extends TestCase
{
    public function test()
    {
        $handler = new HideHandler();
        $this->assertEquals(
            '',
            $handler->run('pipe content', ['first argument content'])
        );
    }
}
