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

namespace MyHeritage\Tests\DatasetUtil\Delta;

use MyHeritage\DatasetUtil\Delta\DeltaCollectorInterface;

/**
 * Implementation of DeltaCollectorInterface for testing
 */
class DeltaCollectorStub implements DeltaCollectorInterface
{
    /**
     * List of collected inserts
     *
     * @var array
     */
    private $inserts;

    /**
     * List of collected updates
     *
     * @var array
     */
    private $updates;

    /**
     * List of collected deletes
     *
     * @var array
     */
    private $deletes;

    /**
     * Indication that close was called
     *
     * @var boolean
     */
    private $closed;


    /**
     * @return array
     */
    public function getDeletes()
    {
        return $this->deletes;
    }

    /**
     * @return array
     */
    public function getInserts()
    {
        return $this->inserts;
    }

    /**
     * @return array
     */
    public function getUpdates()
    {
        return $this->updates;
    }

    /**
     * Adds a new row
     *
     * @param array $values maps field names to values
     */
    public function addInsert($values)
    {
        $this->inserts[] = $values;
    }

    /**
     * Updates an existing row
     *
     * @param array $keys maps key names to values
     * @param array $values maps updated field names to values
     */
    public function addUpdate($keys, $values)
    {
        $this->updates[] = array_merge($keys, $values);
    }

    /**
     * Deletes an existing row
     *
     * @param array $key maps key names to values
     */
    public function addDelete($key)
    {
        $this->deletes[] = $key;
    }

    /**
     * Closes the collector
     */
    public function close()
    {
        $this->closed = true;
    }

    /**
     * @return boolean
     */
    public function getClosed()
    {
        return $this->closed;
    }

}
