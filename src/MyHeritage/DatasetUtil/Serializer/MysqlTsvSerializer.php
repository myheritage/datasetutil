<?php

/**
 * Copyright 2014 MyHeritage, Ltd.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace MyHeritage\DatasetUtil\Serializer;

/**
 * TsvSerializerInterface implementation that use the DEFAULT  MySQL TSV serialization rules
 *
 * (from http://dev.mysql.com/doc/refman/5.0/en/load-data.html)
 *
 *   For output, if the FIELDS ESCAPED BY character is not empty, it is used to prefix the following characters on output:
 *
 *   - The FIELDS ESCAPED BY character (default: BACKSLASH)
 *   - The first character of the FIELDS TERMINATED BY and LINES TERMINATED BY values (default: TAB, LF)
 *   - ASCII 0 (what is actually written following the escape character is ASCII “0”, not a zero-valued byte)
 *
 *   For input, if the FIELDS ESCAPED BY character is not empty, occurrences of that character are stripped
 *   and the following character is taken literally as part of a field value.
 *
 */
class MysqlTsvSerializer implements TsvSerializerInterface
{
    /**
     * Replacement rules for TSV-escaping values
     *
     * @var array
     */
    protected static $escapingRules = array(
        "\0"  => '\\0',
        "\n"  => "\\\n",
        "\t"  => "\\\t",
        '\\'  => '\\\\',
    );

    /**
     * Serializes a list of values into a TSV line
     *
     * The resulting line is always terminated by a newline character
     *
     * @param array $values
     *
     * @return string
     */
    public function serialize($values)
    {
        return implode("\t", array_map(array($this, "escape"), $values)) . "\n";
    }

    /**
     * Unserializes a TSV line into a list of values
     *
     * The TSV line is assumed to be terminated by a newline character
     *
     * @param string $tsv
     *
     * @return array
     */
    public function unserialize($tsv)
    {
        // use backtracking control verbs splitting by unescaped tab characters
        return array_map(array($this, "unescape"), preg_split("/\\\\.(*SKIP)(*FAIL)|\\t/s", substr($tsv, 0, -1)));
    }

    /**
     * Escapes a single value for TSV
     *
     * Non-NULL values are encoded using TSV escaping rules, NULL values are escaped as '\N' strings.
     *
     * @param string $value
     *
     * @return string
     */
    private function escape($value)
    {
        return ($value !== null ? $part = strtr($value, self::$escapingRules) : '\N');
    }

    /**
     * Unescapes a single value from its TSV-escaped form
     *
     * @param string $tsvValue
     *
     * @return string|null
     */
    private function unescape($tsvValue)
    {
        return ($tsvValue != '\N' ? stripslashes($tsvValue) : null);
    }

    /**
     * fgets replacement for TSV lines
     *
     * A single TSV line might span over several lines due to escaped newline characters, so this method guarantees
     * full TSV lines
     *
     * @param $resource
     * @return string|bool TSV line on success, FALSE on end of file or error
     */
    public function fgets($resource)
    {
        $line = FALSE;
        do {
            $buf = fgets($resource);
            if ($buf === FALSE) {
                return FALSE;
            }

            if ($line === FALSE) {
                $line = $buf;
            } else {
                $line .= $buf;
            }
        } while (!$this->isFullTsvLine($buf));

        return $line;
    }

    /**
     * Checks if a given line ends with an unescaped newline
     *
     * @param string $line
     * @return bool
     */
    private function isFullTsvLine($line)
    {
        // check that last character is a newline
        $pos = strlen($line) - 1;
        if ($line[$pos] != "\n") return false;

        // check that it is preceded by an even number of backslashes (zero is also even)
        $pos--;
        $backslashes = 0;
        while ($pos >= 0) {
            if ($line[$pos] != '\\') break;
            $backslashes++;
            $pos--;
        }
        return ($backslashes % 2 == 0);
    }
}
