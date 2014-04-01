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

namespace MyHeritage\Tests\DatasetUtil\Comparator;

use MyHeritage\DatasetUtil\Comparator\NumericKeyComparator;

/**
 * Test class for NumericKeyComparator
 */
class NumericKeyComparatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test less than
     *
     * @return void
     */
    public function testLessThan()
    {
        $keyFields = array("c", "b", "a");
        $values1 = array("a" => 1, "b" => 2, "c" => 3, "d" => 4);
        $values2 = array("a" => 0, "b" => 3, "c" => 3, "d" => 2);
        $comparator = new NumericKeyComparator($keyFields);
        $actual = $comparator->compare($values1, $values2);
        $expected = -1;
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test greater than
     *
     * @return void
     */
    public function testGreaterThan()
    {
        $keyFields = array("c", "b", "a");
        $values1 = array("a" => 1, "b" => 2, "c" => 3, "d" => 4);
        $values2 = array("a" => 0, "b" => 1, "c" => 3, "d" => 2);
        $comparator = new NumericKeyComparator($keyFields);
        $actual = $comparator->compare($values1, $values2);
        $expected = 1;
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test equals
     *
     * @return void
     */
    public function testEquals()
    {
        $keyFields = array("c", "b", "a");
        $values1 = array("a" => 1, "b" => 2, "c" => 3, "d" => 4);
        $values2 = array("a" => 1, "b" => 2, "c" => 3, "d" => 2);
        $comparator = new NumericKeyComparator($keyFields);
        $actual = $comparator->compare($values1, $values2);
        $expected = 0;
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test non numeric field 1
     *
     * Make sure an exception is thrown
     *
     * @expectedException \Exception
     * @return void
     */
    public function testNonNumeric1()
    {
        $keyFields = array("c", "b", "a");
        $values1 = array("a" => 1, "b" => "a", "c" => 3, "d" => 4);
        $values2 = array("a" => 0, "b" => 3, "c" => 3, "d" => 2);
        $comparator = new NumericKeyComparator($keyFields);
        $actual = $comparator->compare($values1, $values2);
        $expected = 777;
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test non numeric field 2
     *
     * Make sure an exception is thrown
     *
     * @expectedException \Exception
     * @return void
     */
    public function testNonNumeric2()
    {
        $keyFields = array("c", "b", "a");
        $values1 = array("a" => 1, "b" => 2, "c" => 3, "d" => 4);
        $values2 = array("a" => 0, "b" => "z", "c" => 3, "d" => 2);
        $comparator = new NumericKeyComparator($keyFields);
        $actual = $comparator->compare($values1, $values2);
        $expected = 777;
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test non numeric when not enforced
     *
     * Make sure an exception is thrown
     *
     * @return void
     */
    public function testNonNumericWhenNotEnforced()
    {
        $keyFields = array("c", "b", "a");
        $values1 = array("a" => 1, "b" => 2, "c" => 3, "d" => 4);
        $values2 = array("a" => 0, "b" => "z", "c" => 3, "d" => 2);
        $comparator = new NumericKeyComparator($keyFields);
        $comparator->setEnforceNumericValues(false);
        $actual = $comparator->compare($values1, $values2);
        $expected = 1;
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test numeric strings
     *
     * Makes sure that strings are compared numerically and not lexicographically
     *
     * @return void
     */
    public function testNumericStrings()
    {
        $keyFields = array("c", "b", "a");
        $values1 = array("a" => "2", "b" => "2", "c" => "3", "d" => 4);
        $values2 = array("a" => "10", "b" => "13", "c" => "3", "d" => 2);
        $comparator = new NumericKeyComparator($keyFields);
        $actual = $comparator->compare($values1, $values2);
        $expected = -1;
        $this->assertEquals($expected, $actual);
    }

}
