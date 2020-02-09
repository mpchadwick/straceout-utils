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
        $this->assertEquals($parts['args'], '"/lib64/libcrypt.so.1", O_RDONLY|O_CLOEXEC');

        $line = 'brk(NULL)                               = 0x7fc6bc216000';
        $parts = $parser->parse($line);
        $this->assertEquals($parts['call'], 'brk');
        $this->assertEquals($parts['args'], 'NULL');

        $line = 'close(3)';
        $parts = $parser->parse($line);
        $this->assertEquals($parts['call'], 'close');
        $this->assertEquals($parts['args'], '3');

        $line = '[00007f150a7a9654] fstat(3, {st_mode=S_IFREG|0755, st_size=41080, ...}) = 0';
        $parts = $parser->parse($line);
        $this->assertEquals($parts['call'], 'fstat');
        $this->assertEquals($parts['args'], '3, {st_mode=S_IFREG|0755, st_size=41080, ...}');
    }
}
