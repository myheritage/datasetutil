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

namespace MyHeritage\DatasetUtil\Delta;

/**
 * Implementation of DeltaCollectorInterface that stores deltas to a given file using a given serializer.
 */
class FileDeltaCollector implements DeltaCollectorInterface
{
    /**
     * Path to output file
     *
     * @var string
     */
    protected $path;

    /**
     * File handle
     *
     * @var resource
     */
    protected $resource;

    /**
     * Serializer for converting delta actions to strings
     *
     * @var DeltaSerializerInterface
     */
    private $serializer;


    /**
     * Constructor
     *
     * @param string $path path to output file
     * @param DeltaSerializerInterface $serializer serializer of delta actions to strings
     * @throws \Exception
     */
    public function __construct($path, $serializer)
    {
        $this->serializer = $serializer;
        $this->path = $path;
        $this->resource = fopen($this->path, "w");
        if ($this->resource === FALSE) {
            throw new \Exception("cannot open file for writing: " . $this->path);
        }
    }

    /**
     * Adds an insert action
     *
     * @param array $values maps field names to values
     * @throws \Exception
     */
    public function addInsert($values)
    {
        $string = $this->serializer->serializeInsert($values);
        if (fwrite($this->resource, $string) === FALSE) {
            throw new \Exception("cannot write to file: " . $this->path);
        }
    }

    /**
     * Adds an update action
     *
     * @param array $keys maps key names to values
     * @param array $values maps updated field names to values
     * @throws \Exception
     */
    public function addUpdate($keys, $values)
    {
        $string = $this->serializer->serializeUpdate($keys, $values);
        if (fwrite($this->resource, $string) === FALSE) {
            throw new \Exception("cannot write to file: " . $this->path);
        }
    }

    /**
     * Adds a delete action
     *
     * @param array $keys maps key names to values
     * @throws \Exception
     */
    public function addDelete($keys)
    {
        $string = $this->serializer->serializeDelete($keys);
        if (fwrite($this->resource, $string) === FALSE) {
            throw new \Exception("cannot write to file: " . $this->path);
        }
    }

    /**
     * Closes the collector
     */
    public function close()
    {
        if (fclose($this->resource) === FALSE) {
            throw new \Exception("cannot close file: " . $this->path);
        }
    }
}
