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

namespace MyHeritage\DatasetUtil\Comparator;

/**
 * Implementation of ComparatorInterface that compares two name-value arrays by comparing values at a given list of keys.
 *
 * By default, the values are compared numerically, and non-numeric values will throw an exception.
 * This requirement can be relaxed to avoid the check, and effectively allow non-numeric keys to be used, and will rely
 * on php's comparison operator semantics (numeric or lexicographic, depending on the given values)
 */
class NumericKeyComparator implements ComparatorInterface
{
    /**
     * If set to true, non-numeric keys will cause exceptions
     *
     * Setting it to false basically allows non-numeric keys to be used, and will rely on php's comparison operator
     * semantics (numeric or lexicographic, depending on the given values)
     *
     * @var bool
     */
    private $enforceNumericValues = true;

    /**
     * Ordered list of key field names to compare
     *
     * @var string[]
     */
    private $keyFields;


    /**
     * Constructor
     *
     * @param string[] $keyFields
     */
    public function __construct($keyFields)
    {
        $this->keyFields = $keyFields;
    }

    /**
     * Compares key fields of two arrays to determine their order
     *
     * @param array $values1
     * @param array $values2
     * @return int 0 if equal, -1 if key1 is less than key2, 1 if key1 is greater than key2
     * @throws \Exception
     *
     */
    public function compare($values1, $values2)
    {
        foreach ($this->keyFields as $field) {
            $value1 = $values1[$field];
            $value2 = $values2[$field];
            if ($this->enforceNumericValues) {
                if (!is_numeric($value1)) {
                    throw new \Exception("Non-numeric key field $field in values1: $value1");
                }
                if (!is_numeric($value2)) {
                    throw new \Exception("Non-numeric key field $field in values2: $value2");
                }
            }
            if ($value1 < $value2) return -1;
            if ($value1 > $value2) return 1;
        }
        return 0;
    }

    /**
     * @param boolean $enforceNumericValues
     */
    public function setEnforceNumericValues($enforceNumericValues)
    {
        $this->enforceNumericValues = $enforceNumericValues;
    }
}
