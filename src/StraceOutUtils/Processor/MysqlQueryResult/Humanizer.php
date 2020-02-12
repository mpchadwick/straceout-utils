<?php

namespace StraceOutUtils\Processor\MysqlQueryResult;

use StraceOutUtils\Exception\NotMysqlQueryResultException;

/**
 * TODO:
 * Current implementation makes several assumptions that won't always be true
 * Need to fix those at some point
 */
class Humanizer
{
    // See: https://dev.mysql.com/doc/internals/en/com-query-response.html#packet-Protocol::ColumnDefinition
    const CATALOG = '03646566';

    const EOF_HEADER = 'fe';

    private $columnCount;

    private $columnDefinitions;

    private $rows;

    private $currentPosition = 0;

    private $currentSequence = 1;

    private $message;

    private $nextIsEscaped = false;

    public function __construct($message)
    {
        $this->message = str_replace('\\x', '', $message);
    }

    /**
     * See: https://dev.mysql.com/doc/internals/en/com-query-response.html
     */
    public function humanize()
    {
        try {
            $this->extractColumnCount();
            $this->extractColumnDefinitions();
            if ($this->nextPacketIsEof()) {
                $this->skipEof();
            }
            $this->extractRows();
        } catch (NotMysqlQueryResultException $e) {
            return $this->message;
        }

        $return = '';
        foreach ($this->rows as $i => $values) {
            $return .= '************* ' . $i . ' *************' . PHP_EOL;
            foreach ($values as $key => $value) {
                $return .= $key . ': ' . $value . PHP_EOL;
            }
        }

        return $return;
    }

    /**
     * Should be in the following form:
     *
     * - Length: Should be 01 00 00
     * - Sequence ID: Should be 01
     * - Value
     *
     * Example: 01 00 00 01 06
     *
     * TODO:
     * If there are more than 255 columns I guess this might not work?
     * Need to test...
     */
    private function extractColumnCount()
    {
        $this->columnCount = hexdec(substr($this->message, 8, 2));
        $this->currentPosition = 10;
    }

    /**
     * Should be in following form
     *
     * - Length (E.g. 54 00 00)
     * - Sequence ID 02
     * - Payload:
     *     - Catalog (length encoded) -- always 03 64 65 66
     *     - Schema (length encoded)
     *     - Table (length encoded)
     *
     * TODO:
     * If the length is more than 255 will this work?
     *
     * @return [type] [description]
     */
    private function extractColumnDefinitions()
    {
        for ($i = 0; $i < $this->columnCount; $i++) {
            $this->currentSequence++;
            $this->columnDefinitions[$i]['length'] = substr($this->message, $this->currentPosition, 2);
            $this->currentPosition += 6;

            // Validate sequence ID
            $this->validateSequence();

            // Validate catalog
            if (substr($this->message, $this->currentPosition, 8) !== self::CATALOG) {
                throw new NotMysqlQueryResultException('Catalog validation failed');
            }
            $this->currentPosition += 8;

            $this->columnDefinitions[$i]['schema'] = $this->nextValue();
            $this->columnDefinitions[$i]['table'] = $this->nextValue();
            $this->columnDefinitions[$i]['org_table'] = $this->nextValue();
            $this->columnDefinitions[$i]['name'] = $this->nextValue();
            $this->columnDefinitions[$i]['org_name'] = $this->nextValue();

            /**
             * Skip:
             * filler_1 (2) / character_set (4) / column_length (8) / column_type (2)
             * flags (4) / decimals (2) / filler_4 (4)
             */
            $this->currentPosition += 26;
        }
    }

    private function extractRows()
    {
        $i = 0;
        while (!$this->nextPacketIsEof()) {
            $this->currentSequence++;
            $i++;

            $length = hexdec(substr($this->message, $this->currentPosition, 2));
            $this->currentPosition += 6;

            $this->validateSequence();

            for ($j = 0; $j < $this->columnCount; $j++) {
                $this->rows[$i][$this->columnDefinitions[$j]['name']] = $this->nextValue();
            }
        }
    }

    private function nextValue()
    {
        $length = hexdec(substr($this->message, $this->currentPosition, 2));
        $this->currentPosition += 2;

        $value = substr($this->message, $this->currentPosition, $length * 2);
        $this->currentPosition += ($length * 2);

        return hex2bin($value);
    }

    private function nextPacketIsEof()
    {
        return substr($this->message, $this->currentPosition + 8, 2) === self::EOF_HEADER;
    }

    private function skipEof()
    {
        $this->currentSequence++;
        $this->currentPosition += 18;
    }

    private function validateSequence()
    {
        if (hexdec(substr($this->message, $this->currentPosition, 2)) != $this->currentSequence) {
            throw new NotMysqlQueryResultException('Sequence validation failed');
        }
        $this->currentPosition += 2;
    }
}
