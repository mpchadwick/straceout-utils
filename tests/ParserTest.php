<?php

use PHPUnit\Framework\TestCase;
use StraceOutUtils\Parser;

class ParserTest extends TestCase
{
    public function testParse()
    {
        $parser = new Parser;
        $line = 'open("/lib64/libcrypt.so.1", O_RDONLY|O_CLOEXEC) = 3';
        $parts = $parser->parse($line);
        $this->assertEquals($parts['call'], 'open');

        $line = 'brk(NULL)                               = 0x7fc6bc216000';
        $parts = $parser->parse($line);
        $this->assertEquals($parts['call'], 'brk');

        $line = 'close(3)';
        $parts = $parser->parse($line);
        $this->assertEquals($parts['call'], 'close');

        $line = '[00007f150a7a9654] fstat(3, {st_mode=S_IFREG|0755, st_size=41080, ...}) = 0';
        $parts = $parser->parse($line);
        $this->assertEquals($parts['call'], 'fstat');
    }
}
