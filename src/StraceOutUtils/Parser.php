<?php

namespace StraceOutUtils;

class Parser
{
    /**
     * Parse a line from strace out into parts
     *
     * - call: The syscall
     * - args: Arguments it was passed
     */
    public function parse($line)
    {
        $parts = [];
        $parts['call'] = $this->extractCall($line);
        $parts['args'] = $this->extractArgs($line);

        return $parts;
    }

    /**
     * [extractCall description]
     * @param  [type] $line [description]
     * @return [type]       [description]
     */
    private function extractCall($line)
    {
        $parts = explode('(', $line);
        if (strpos($parts[0], ' ') === false) {
            return $parts[0];
        }

        $subParts = explode(' ', $parts[0]);

        return end($subParts);
    }

    private function extractArgs($line)
    {
        $start = strpos($line, '(') + 1;
        $end = strrpos($line, ')');

        return substr($line, $start, $end - $start);
    }
}
