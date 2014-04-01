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
 * Interface for a serializing/unserializing of tuples into/from strings
 */
interface TupleSerializerInterface
{
    /**
     * Serializes a list of values into a string
     *
     * @param array $values
     *
     * @return string
     */
    public function serialize($values);

    /**
     * Unserializes a string into a list of values
     *
     * The TSV line is assumed to be terminated by a newline character
     *
     * @param string $string
     *
     * @return array
     */
    public function unserialize($string);
}
