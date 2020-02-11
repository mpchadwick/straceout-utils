<?php

namespace StraceOutUtils\Processor;

use StraceOutUtils\Processor\MysqlQueryResult\Humanizer;

class MysqlQueryResult
{
    private $isBuffering = false;

    private $buffer;

    /**
     * If the packets are received in hex it may be difficult to parse as it seems
     *
     * We'll then need to
     *
     * @param  [type] $parts [description]
     * @return [type]        [description]
     */
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
            $humanizer = new Humanizer($this->buffer);
            $return = $humanizer->humanize();

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
