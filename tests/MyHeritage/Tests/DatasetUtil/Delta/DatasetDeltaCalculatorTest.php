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

use MyHeritage\DatasetUtil\Comparator\NumericKeyComparator;
use MyHeritage\DatasetUtil\Delta\DatasetDeltaCalculator;

/**
 * Test class for DatasetDeltaCalculator
 */
class DatasetDeltaCalculatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function testInserts()
    {
        $calc = new DatasetDeltaCalculator();
        $dataset1 = new \ArrayIterator(
            array(
                array("pk1"=>1, "pk2"=>2, "s"=>"abc"),
                array("pk1"=>1, "pk2"=>5, "s"=>"def")));
        $dataset2 = new \ArrayIterator(
            array(
                array("pk1"=>1, "pk2"=>1, "s"=>"ABC"),      // new row before existing keys
                array("pk1"=>1, "pk2"=>2, "s"=>"abc"),
                array("pk1"=>1, "pk2"=>4, "s"=>"mno"),      // new row in the middle of existing keys
                array("pk1"=>1, "pk2"=>5, "s"=>"def"),
                array("pk1"=>2, "pk2"=>0, "s"=>"DEF")));    // new row after existing keys

        $keyFields = array("pk1", "pk2");
        $comparator = new NumericKeyComparator($keyFields);
        $collector = new DeltaCollectorStub();
        $calc->calculateDatasetDelta($dataset1, $dataset2, $keyFields, $comparator, $collector);
        $this->assertEquals(3, count($collector->getInserts()));
        $this->assertEquals(0, count($collector->getUpdates()));
        $this->assertEquals(0, count($collector->getDeletes()));

        $inserts = $collector->getInserts();
        $this->assertEquals(array("pk1"=>1, "pk2"=>1, "s"=>"ABC"), $inserts[0]);
        $this->assertEquals(array("pk1"=>1, "pk2"=>4, "s"=>"mno"), $inserts[1]);
        $this->assertEquals(array("pk1"=>2, "pk2"=>0, "s"=>"DEF"), $inserts[2]);

        $this->assertTrue($collector->getClosed());
    }

    /**
     * @return void
     */
    public function testUpdates()
    {
        $calc = new DatasetDeltaCalculator();
        $dataset1 = new \ArrayIterator(
            array(
                array("pk1"=>1, "pk2"=>2, "s"=>"abc"),
                array("pk1"=>1, "pk2"=>5, "s"=>"def")));
        $dataset2 = new \ArrayIterator(
            array(
                array("pk1"=>1, "pk2"=>2, "s"=>"ABC"),
                array("pk1"=>1, "pk2"=>5, "s"=>"DEF")));

        $keyFields = array("pk1", "pk2");
        $comparator = new NumericKeyComparator($keyFields);
        $collector = new DeltaCollectorStub();
        $calc->calculateDatasetDelta($dataset1, $dataset2, $keyFields, $comparator, $collector);
        $this->assertEquals(0, count($collector->getInserts()));
        $this->assertEquals(2, count($collector->getUpdates()));
        $this->assertEquals(0, count($collector->getDeletes()));

        $updates = $collector->getUpdates();
        $this->assertEquals(array("pk1"=>1, "pk2"=>2, "s"=>"ABC"), $updates[0]);
        $this->assertEquals(array("pk1"=>1, "pk2"=>5, "s"=>"DEF"), $updates[1]);

        $this->assertTrue($collector->getClosed());
    }

    /**
     * @return void
     */
    public function testDeletes()
    {
        $calc = new DatasetDeltaCalculator();
        $dataset1 = new \ArrayIterator(
            array(
                array("pk1"=>1, "pk2"=>2, "s"=>"abc"),
                array("pk1"=>1, "pk2"=>3, "s"=>"def"),
                array("pk1"=>1, "pk2"=>4, "s"=>"ghi"),
                array("pk1"=>1, "pk2"=>5, "s"=>"jkl"),
                array("pk1"=>1, "pk2"=>6, "s"=>"mno")));
        $dataset2 = new \ArrayIterator(
            array(
                array("pk1"=>1, "pk2"=>3, "s"=>"def"),
                array("pk1"=>1, "pk2"=>5, "s"=>"jkl")));

        $keyFields = array("pk1", "pk2");
        $comparator = new NumericKeyComparator($keyFields);
        $collector = new DeltaCollectorStub();
        $calc->calculateDatasetDelta($dataset1, $dataset2, $keyFields, $comparator, $collector);
        $this->assertEquals(0, count($collector->getInserts()));
        $this->assertEquals(0, count($collector->getUpdates()));
        $this->assertEquals(3, count($collector->getDeletes()));

        $deletes = $collector->getDeletes();
        $this->assertEquals(array("pk1"=>1, "pk2"=>2), $deletes[0]);
        $this->assertEquals(array("pk1"=>1, "pk2"=>4), $deletes[1]);
        $this->assertEquals(array("pk1"=>1, "pk2"=>6), $deletes[2]);

        $this->assertTrue($collector->getClosed());
    }

    /**
     * @return void
     */
    public function testMix()
    {
        $calc = new DatasetDeltaCalculator();
        $dataset1 = new \ArrayIterator(
            array(
                array("pk1"=>1, "pk2"=>2, "s"=>"abc"),
                array("pk1"=>1, "pk2"=>3, "s"=>"def"),
                array("pk1"=>1, "pk2"=>4, "s"=>"ghi"),
                array("pk1"=>1, "pk2"=>5, "s"=>"jkl"),
                array("pk1"=>1, "pk2"=>6, "s"=>"mno")));
        $dataset2 = new \ArrayIterator(
            array(
                array("pk1"=>1, "pk2"=>1, "s"=>"qwe"),
                array("pk1"=>1, "pk2"=>2, "s"=>"abc"),
                array("pk1"=>1, "pk2"=>3, "s"=>"DEF"),
                array("pk1"=>1, "pk2"=>4, "s"=>"ghi"),
                array("pk1"=>1, "pk2"=>6, "s"=>"mno")));

        $keyFields = array("pk1", "pk2");
        $comparator = new NumericKeyComparator($keyFields);
        $collector = new DeltaCollectorStub();
        $calc->calculateDatasetDelta($dataset1, $dataset2, $keyFields, $comparator, $collector);
        $this->assertEquals(1, count($collector->getInserts()));
        $this->assertEquals(1, count($collector->getUpdates()));
        $this->assertEquals(1, count($collector->getDeletes()));

        $inserts = $collector->getInserts();
        $updates = $collector->getUpdates();
        $deletes = $collector->getDeletes();
        $this->assertEquals(array("pk1"=>1, "pk2"=>1, "s"=>"qwe"), $inserts[0]);
        $this->assertEquals(array("pk1"=>1, "pk2"=>3, "s"=>"DEF"), $updates[0]);
        $this->assertEquals(array("pk1"=>1, "pk2"=>5), $deletes[0]);

        $this->assertTrue($collector->getClosed());
    }

    /**
     * @return void
     */
    public function testEmptyBoth()
    {
        $calc = new DatasetDeltaCalculator();
        $dataset1 = new \ArrayIterator(array());
        $dataset2 = new \ArrayIterator(array());
        $keyFields = array("pk1", "pk2");
        $comparator = new NumericKeyComparator($keyFields);
        $collector = new DeltaCollectorStub();
        $calc->calculateDatasetDelta($dataset1, $dataset2, $keyFields, $comparator, $collector);
        $this->assertEquals(0, count($collector->getInserts()));
        $this->assertEquals(0, count($collector->getUpdates()));
        $this->assertEquals(0, count($collector->getDeletes()));
        $this->assertTrue($collector->getClosed());
    }

    /**
     * @return void
     */
    public function testEmpty1()
    {
        $calc = new DatasetDeltaCalculator();
        $dataset1 = new \ArrayIterator(array());
        $dataset2 = new \ArrayIterator(
            array(
                array("pk1"=>1, "pk2"=>2, "s"=>"abc"),
                array("pk1"=>1, "pk2"=>3, "s"=>"def")));

        $keyFields = array("pk1", "pk2");
        $comparator = new NumericKeyComparator($keyFields);
        $collector = new DeltaCollectorStub();
        $calc->calculateDatasetDelta($dataset1, $dataset2, $keyFields, $comparator, $collector);
        $this->assertEquals(2, count($collector->getInserts()));
        $this->assertEquals(0, count($collector->getUpdates()));
        $this->assertEquals(0, count($collector->getDeletes()));

        $inserts = $collector->getInserts();
        $this->assertEquals(array("pk1"=>1, "pk2"=>2, "s"=>"abc"), $inserts[0]);
        $this->assertEquals(array("pk1"=>1, "pk2"=>3, "s"=>"def"), $inserts[1]);

        $this->assertTrue($collector->getClosed());
    }

    /**
     * @return void
     */
    public function testEmpty2()
    {
        $calc = new DatasetDeltaCalculator();
        $dataset1 = new \ArrayIterator(
            array(
                array("pk1"=>1, "pk2"=>2, "s"=>"abc"),
                array("pk1"=>1, "pk2"=>3, "s"=>"def")));
        $dataset2 = new \ArrayIterator(array());

        $keyFields = array("pk1", "pk2");
        $comparator = new NumericKeyComparator($keyFields);
        $collector = new DeltaCollectorStub();
        $calc->calculateDatasetDelta($dataset1, $dataset2, $keyFields, $comparator, $collector);
        $this->assertEquals(0, count($collector->getInserts()));
        $this->assertEquals(0, count($collector->getUpdates()));
        $this->assertEquals(2, count($collector->getDeletes()));

        $deletes = $collector->getDeletes();
        $this->assertEquals(array("pk1"=>1, "pk2"=>2), $deletes[0]);
        $this->assertEquals(array("pk1"=>1, "pk2"=>3), $deletes[1]);

        $this->assertTrue($collector->getClosed());
    }

    /**
     * @expectedException \Exception
     */
    public function testInvalidSortOld()
    {
        $calc = new DatasetDeltaCalculator();
        $dataset1 = new \ArrayIterator(
            array(
                array("pk1"=>1, "pk2"=>2, "s"=>"abc"),
                array("pk1"=>1, "pk2"=>3, "s"=>"def"),
                array("pk1"=>1, "pk2"=>4, "s"=>"ghi"),
                array("pk1"=>1, "pk2"=>1, "s"=>"jkl"),
                array("pk1"=>1, "pk2"=>6, "s"=>"mno")));
        $dataset2 = new \ArrayIterator(
            array(
                array("pk1"=>1, "pk2"=>1, "s"=>"qwe"),
                array("pk1"=>1, "pk2"=>2, "s"=>"abc"),
                array("pk1"=>1, "pk2"=>3, "s"=>"DEF"),
                array("pk1"=>1, "pk2"=>4, "s"=>"ghi"),
                array("pk1"=>1, "pk2"=>6, "s"=>"mno")));

        $keyFields = array("pk1", "pk2");
        $comparator = new NumericKeyComparator($keyFields);
        $collector = new DeltaCollectorStub();
        $calc->calculateDatasetDelta($dataset1, $dataset2, $keyFields, $comparator, $collector);
    }

    /**
     * @expectedException \Exception
     */
    public function testInvalidSortNew()
    {
        $calc = new DatasetDeltaCalculator();
        $dataset1 = new \ArrayIterator(
            array(
                array("pk1"=>1, "pk2"=>2, "s"=>"abc"),
                array("pk1"=>1, "pk2"=>3, "s"=>"def"),
                array("pk1"=>1, "pk2"=>4, "s"=>"ghi"),
                array("pk1"=>1, "pk2"=>5, "s"=>"jkl"),
                array("pk1"=>1, "pk2"=>6, "s"=>"mno")));
        $dataset2 = new \ArrayIterator(
            array(
                array("pk1"=>1, "pk2"=>1, "s"=>"qwe"),
                array("pk1"=>1, "pk2"=>2, "s"=>"abc"),
                array("pk1"=>2, "pk2"=>3, "s"=>"DEF"),
                array("pk1"=>1, "pk2"=>4, "s"=>"ghi"),
                array("pk1"=>1, "pk2"=>6, "s"=>"mno")));

        $keyFields = array("pk1", "pk2");
        $comparator = new NumericKeyComparator($keyFields);
        $collector = new DeltaCollectorStub();
        $calc->calculateDatasetDelta($dataset1, $dataset2, $keyFields, $comparator, $collector);
    }
}
