<?php

namespace StraceOutUtils\Processor;

use StraceOutUtils\Processor\MysqlQueryResult\PacketParser;
use StraceOutUtils\Processor\MysqlQueryResult\Formatter;

class MysqlQueryResult
{
    private $isBuffering = false;

    private $buffer;

    private $query;

    public function process($parts)
    {
        // We can expect these in the middle of buffering
        if ($parts['call'] === 'poll') {
            return false;
        }

        if ($parts['call'] === 'recvfrom') {
            $this->isBuffering = true;
            $this->buffer .= $this->extractBuffer($parts['args']);
            return false;
        }

        if ($this->isBuffering) {
            $return = '';
            $return .= $this->query . PHP_EOL;
            $parser = new PacketParser($this->buffer);
            $parsed = $parser->parse();

            $formatter = new Formatter;
            $return .= $formatter->format($parsed);

            if ($parts['call'] === 'sendto') {
                $this->query = $this->extractQuery($parts['args']);
            } else {
                $this->query = '';
            }

            $this->isBuffering = false;
            $this->buffer = '';

            return $return;
        }

        if ($parts['call'] === 'sendto') {
            $this->query = $this->extractQuery($parts['args']);
        }

        return false;
    }

    private function extractQuery($args)
    {
        $query = $this->extractBuffer($args);
        $query = str_replace('\\x', '', $query);
        $query = hex2bin($query);
        $query = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $query);

        return $query;
    }

    private function extractBuffer($args)
    {
        $start = strpos($args, '"') + 1;

        // TODO:
        // This could in theory miss if the quote is escaped.
        // It should really be the second occurence of a non escaped double-quote
        // Anyway, this is good enough for now...
        $end = strpos($args, '", ');

        return substr($args, $start, $end - $start);
    }
}
