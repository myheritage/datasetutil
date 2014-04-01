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
 * Interface for serializing delta actions
 */
 interface DeltaSerializerInterface
{
    /**
     * Serializes an insert delta action
     *
     * @param array $values maps field names to values
     * @return string
     */
    public function serializeInsert($values);

    /**
     * Serializes an update delta action
     *
     * @param array $key maps key field names to values
     * @param array $values maps updated field names to values
     * @return string
     */
    public function serializeUpdate($key, $values);

    /**
     * Serializes a delete delta action
     *
     * @param array $key maps key names to values
     * @return string
     */
    public function serializeDelete($key);
}
