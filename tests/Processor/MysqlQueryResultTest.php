<?php

use PHPUnit\Framework\TestCase;
use StraceOutUtils\Processor\MysqlQueryResult;

class MysqlQueryResultTest extends TestCase
{
    public function testProcess()
    {
        $processor = new MysqlQueryResult;

        $parts = [
            'call' => 'close',
            'args' => '4'
        ];
        $result = $processor->process($parts);
        $this->assertEquals($result, false);

        // TODO: Rewrite with hex input
    }
}
