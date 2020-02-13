<?php

namespace StraceOutUtils\Processor\MysqlQueryResult;

class Formatter
{
    public function format($parsed)
    {
        if (!empty($parsed['message'])) {
            return $parsed['message'];
        }

        if (sizeof($parsed['rows']) === 0) {
            return 'Empty set' . PHP_EOL;
        }

        $return = '';
        foreach ($parsed['rows'] as $i => $values) {
            $return .= '************* ' . $i . ' *************' . PHP_EOL;
            foreach ($values as $key => $value) {
                $return .= $key . ': ' . $value . PHP_EOL;
            }
        }

        return $return;
    }
}
