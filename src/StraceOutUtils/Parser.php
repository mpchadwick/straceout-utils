<?php

namespace StraceOutUtils;

class Parser
{
    /**
     * Parse a line from strace out into parts
     *
     * [
     *     'call' => '',
     *     'args' => '',
     *     'return' => ''
     * ]
     * @param  [type] $line [description]
     * @return [type]       [description]
     */
    public function parse($line)
    {
        $parts = [];
        $parts['call'] = $this->extractCall($line);

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
}
