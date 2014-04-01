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
 * Iterates over lines in a TSV file, and returns them as key-value arrays
 *
 */
class TsvIterator implements \Iterator
{
    /**
     * Path to input file
     *
     * @var string
     */
    private $path;

    /**
     * File handle
     *
     * @var resource
     */
    private $resource;

    /**
     * List of field names in input TSV
     *
     * @var string[]
     */
    private $fields;

    /**
     * Current key
     *
     * @var int
     */
    private $key;

    /**
     * Current TSV line
     *
     * @var string
     */
    private $line;

    /**
     * Deserializer
     *
     * @var TsvSerializerInterface
     */
    private $serializer;


    /**
     * @param string $path
     * @param string[] $fields
     * @param TsvSerializerInterface $serializer
     */
    public function __construct($path, $fields, $serializer)
    {
        $this->path = $path;
        $this->fields = $fields;
        $this->serializer = $serializer;
        $this->rewind();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        return $this->valuesFromTuple($this->tupleFromLine($this->line));
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        do {
            $this->line = $this->serializer->fgets($this->resource);
        } while ($this->line == "\n");

        if ($this->line !== FALSE) {
            $this->key++;
        }
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        return $this->line !== FALSE;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @throws \Exception
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->line = FALSE;
        if ($this->resource) {
            fclose($this->resource);
        }
        $this->resource = fopen($this->path, "r");
        if ($this->resource === FALSE) {
            throw new \Exception("cannot open file for reading: " . $this->path);
        }
        $this->key = 0;
        $this->next();
    }

    private function tupleFromLine($line)
    {
        return $this->serializer->unserialize($line);
    }

    /**
     * Converts a positional array of values to key based array of values
     *
     * @param array $tuple
     * @return array
     */
    private function valuesFromTuple($tuple)
    {
        $values = array();
        for ($i = 0; $i < count($tuple); $i++) {
            if (isset($this->fields[$i])) {
            $values[$this->fields[$i]] = $tuple[$i];
        }
        }

        return $values;
    }
}
