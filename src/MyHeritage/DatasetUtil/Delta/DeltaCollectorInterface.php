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
 * Interface for collecting delta actions
 */
 interface DeltaCollectorInterface
{
    /**
     * Adds an insert action
     *
     * @param array $values maps field names to values
     */
    public function addInsert($values);

    /**
     * Adds an update action
     *
     * @param array $keys maps key names to values
     * @param array $values maps updated field names to values
     */
    public function addUpdate($keys, $values);

    /**
     * Adds a delete action
     *
     * @param array $key maps key names to values
     */
    public function addDelete($key);

    /**
     * Closes the collector
     */
    public function close();
}
