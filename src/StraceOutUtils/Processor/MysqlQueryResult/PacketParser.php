<?php

namespace StraceOutUtils\Processor\MysqlQueryResult;

use StraceOutUtils\Exception\NotMysqlQueryResultException;

/**
 * TODO:
 * Current implementation makes several assumptions that won't always be true
 * Need to fix those at some point
 */
class PacketParser
{
    /**
     * @see https://dev.mysql.com/doc/internals/en/com-query-response.html#packet-Protocol::ColumnDefinition
     *
     * > catalog (lenenc_str) -- catalog (always "def")
     */
    const CATALOG = '03646566';

    const EOF_HEADER = 'fe';

    /**
     * @see https://dev.mysql.com/doc/internals/en/com-query-response.html
     *
     * 14.6.4.1.1.3 Text Resultset Row:
     *
     * > NULL is sent as 0xfb
     */
    const NULL_VALUE = 251;

    /**
     * @see https://dev.mysql.com/doc/internals/en/integer.html#length-encoded-integer
     *
     * > If the value is ≥ 251 and < (216), it is stored as fc + 2-byte integer.
     * > If the value is ≥ (216) and < (224), it is stored as fd + 3-byte integer.
     */
    const LENGTH_ENCODED_INTEGER_TWO_BYTE_FLAG = 252;
    const LENGTH_ENCODED_INTEGER_THREE_BYTE_FLAG = 253;

    private $columnCount;

    private $columnDefinitions;

    private $rows = [];

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
    public function parse()
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

        if (sizeof($this->rows) === 0) {
            return 'Empty set' . PHP_EOL;
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

        if ($length === self::NULL_VALUE) {
            return 'NULL';
        } else if ($length === self::LENGTH_ENCODED_INTEGER_TWO_BYTE_FLAG) {
            $length = substr($this->message, $this->currentPosition, 4);
            $length = $this->reverseMultiByteLength($length);
            $length = hexdec($length);
            $this->currentPosition += 4;
        } else if ($length === self::LENGTH_ENCODED_INTEGER_THREE_BYTE_FLAG) {
            $length = substr($this->message, $this->currentPosition, 6);
            $length = $this->reverseMultiByteLength($length);
            $length = hexdec($length);
            $this->currentPosition += 6;
        }

        $value = substr($this->message, $this->currentPosition, $length * 2);
        $this->currentPosition += ($length * 2);

        return hex2bin($value);
    }

    /**
     * For some reason MySQL stores multi-byte lengths backwards
     *
     * For example:
     *
     * fc ae 02
     *
     * In this case fc is a flag that the length is in the next two bytes
     *
     * ae 02 In decimal is 44546
     *
     * However, in testing the value was actually 686 bytes, which should be 02 ae
     */
    private function reverseMultiByteLength($length)
    {
        $parts = str_split($length, 2);
        $parts = array_reverse($parts);

        return implode('', $parts);
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
