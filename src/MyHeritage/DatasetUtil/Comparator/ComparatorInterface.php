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
 * Interface for comparing values for determining their order
 */
interface ComparatorInterface
{
    /**
     * Compares two values to determine their order
     *
     * @param mixed $v1
     * @param mixed $v2
     * @return int 0 if equal, -1 if v1 is less than v2, 1 if v1 is greater than v2
     */
    public function compare($v1, $v2);
}
