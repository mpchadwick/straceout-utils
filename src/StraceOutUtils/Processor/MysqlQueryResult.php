<?php

namespace StraceOutUtils\Processor;

class MysqlQueryResult
{
    private $isBuffering = false;

    private $buffer;

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
            $return = $this->buffer;

            $this->isBuffering = false;
            $this->buffer = '';

            return $return;
        }

        return false;
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
