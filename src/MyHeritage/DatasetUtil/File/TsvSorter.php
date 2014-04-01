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

namespace MyHeritage\DatasetUtil\File;

use MyHeritage\DatasetUtil\Serializer\TsvSerializerInterface;

/**
 * Sorts lines in a TSV file
 */
class TsvSorter
{
    /**
     * Sorts lines in a TSV file by the values of given columns
     *
     * Currently supports only numeric sorting
     *
     * @param string $inputTsv
     * @param string $outputTsv
     * @param int[] $sortPositions list of zero-based positions to sort by
     * @param TsvSerializerInterface $serializer
     */
    public function sort($inputTsv, $outputTsv, $sortPositions, $serializer)
    {
        // pass 1
        // read input file and remember its keys, offsets and lengths
        list($keys, $offsets, $lengths) = $this->index($inputTsv, $sortPositions, $serializer);

        // sort line keys by values (composed line keys)
        uasort($keys, array($this, "compareKeys"));

        // pass 2
        // read lines from input file and write to output file at correct order
        $this->reorder($inputTsv, $outputTsv, $keys, $offsets, $lengths);
    }

    /**
     * Scan TSV file and return the keys, offsets and lengths of each line
     *
     * @param string $tsv
     * @param int[] $sortPositions
     * @param TsvSerializerInterface $serializer
     * @return array
     */
    private function index($tsv, $sortPositions, $serializer)
    {
        $keys = array();
        $offsets = array();
        $lengths = array();

        $resource = $this->fopen($tsv, "r");

        $offset = $this->ftell($resource);
        while (($line = $serializer->fgets($resource)) !== FALSE) {
            if ($line == "\n") continue;
            $values = $serializer->unserialize($line);
            $keys[] = $this->constructKey($values, $sortPositions);
            $offsets[] = $offset;
            $lengths[] = (($newOffset = $this->ftell($resource)) - $offset);
            $offset = $newOffset;
        }

        $this->close($resource);

        return array($keys, $offsets, $lengths);
    }

    /**
     * Reorder the TSV rows at a given order
     *
     * @param string $inputTsv
     * @param string $outputTsv
     * @param array $keys array whose keys are used for line ordering
     * @param array $offsets
     * @param array $lengths
     */
    private function reorder($inputTsv, $outputTsv, $keys, $offsets, $lengths)
    {
        $inputResource = $this->fopen($inputTsv, "r");
        $outputResource = $this->fopen($outputTsv, "w");

        foreach ($keys as $line => $key) {
            $this->fseek($inputResource, $offsets[$line]);
            $buf = $this->fread($inputResource, $lengths[$line]);
            $this->fwrite($outputResource, $buf);
        }

        $this->close($outputResource);
        $this->close($inputResource);
    }

    /**
     * Constructs a sort key from a tuple of values
     *
     * @param array $values
     * @param int[] $sortPositions
     * @return string
     * @throws \Exception
     */
    private function constructKey($values, $sortPositions)
    {
        $parts = array();
        foreach ($sortPositions as $position) {
            if (!isset($values[$position])) {
                throw new \Exception("sort position $position does not match number of values " . count($values));
            }
            $parts[] = $values[$position];
        }
        return implode('-', $parts);
    }

    /**
     * Compares two key strings
     *
     * @param string $k1
     * @param string $k2
     * @return int
     */
    private function compareKeys($k1, $k2)
    {
        $components1 = explode('-', $k1);
        $components2 = explode('-', $k2);
        foreach ($components1 as $pos => $v1) {
            $v2 = $components2[$pos];
            if ($v1 < $v2) return -1;
            if ($v1 > $v2) return 1;
        }
        return 0;
    }

    /**
     * fopen replacement that throws an exception on error
     *
     * @param string $path
     * @param string $mode
     * @return resource
     * @throws \Exception
     */
    private function fopen($path, $mode)
    {
        $resource = fopen($path, $mode);
        if ($resource === FALSE) {
            throw new \Exception("failed to open file $path in mode $mode");
        }
        return $resource;
    }

    /**
     * close replacement that throws an exception on error
     *
     * @param resource $resource
     * @throws \Exception
     */
    private function close($resource)
    {
        if (fclose($resource) === FALSE) {
            throw new \Exception("failed to close file");
        }
    }

    /**
     * ftell replacement that throws an exception on error
     *
     * @param resource $resource
     * @return int
     * @throws \Exception
     */
    private function ftell($resource)
    {
        if (($offset = ftell($resource)) === FALSE) {
            throw new \Exception("failed to fseek to offset $offset");
        }
        return $offset;
    }

    /**
     * fseek replacement that throws an exception on error
     *
     * @param resource $resource
     * @param int $offset
     * @throws \Exception
     */
    private function fseek($resource, $offset)
    {
        if (fseek($resource, $offset) === -1) {
            throw new \Exception("failed to fseek to offset $offset");
        }
    }

    /**
     * fread replacement that throws an exception on error
     *
     * @param resource $resource
     * @param int $length
     * @return string
     * @throws \Exception
     */
    private function fread($resource, $length)
    {
        if (($buf = fread($resource, $length)) === FALSE) {
            throw new \Exception("failed to fread $length bytes");
        }
        return $buf;
    }

    /**
     * fwrite replacement that throws an exception on error
     *
     * @param resource $resource
     * @param string $buf
     * @throws \Exception
     */
    private function fwrite($resource, $buf)
    {
        if (($buf = fwrite($resource, $buf)) === FALSE) {
            throw new \Exception("failed to fwrite");
        }
    }

}