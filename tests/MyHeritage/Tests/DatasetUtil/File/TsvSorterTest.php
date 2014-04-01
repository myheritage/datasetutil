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

use MyHeritage\DatasetUtil\File\TsvSorter;
use MyHeritage\DatasetUtil\Serializer\MysqlTsvSerializer;

/**
 * Test class for TsvSorter
 */
class TsvSorterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test sorting of a TSV file
     */
    public function testSort()
    {
        $sorter = new TsvSorter();
        $inputTsv = __DIR__ . '/tsv-sort.tsv';
        $outputTsv = "/tmp/test-" . __METHOD__ . "-" . time();
        $sortPositions = array(2,1);
        $serializer = new MysqlTsvSerializer();
        $sorter->sort($inputTsv, $outputTsv, $sortPositions, $serializer);
        $output = file_get_contents($outputTsv);
        $expected = "AAA\t9\t1\taaa\nABC\t3\t4\tabc\nBBB\t1\t5\tbbb\nDEF\t2\t6\tdef\n";
        $this->assertEquals($expected, $output);
    }

    /**
     * Test exception is thrown if input file is missing
     */
    public function testSortMissingFile()
    {
        $sorter = new TsvSorter();
        $inputTsv = "missing-file.tsv";
        $outputTsv = "missing-file.tsv";
        $sortPositions = array(2,1);
        $serializer = new MysqlTsvSerializer();
        try {
            $sorter->sort($inputTsv, $outputTsv, $sortPositions, $serializer);
            $this->fail("should have thrown an exception");
        } catch (\Exception $e) {
        }
    }

    /**
     * Test exception is thrown if invalid sort position is given
     */
    public function testSortInvalidSortPosition()
    {
        $sorter = new TsvSorter();
        $inputTsv = __DIR__ . '/tsv-sort.tsv';
        $outputTsv = "/tmp/test-" . __METHOD__ . "-" . time();
        $sortPositions = array(2,8);
        $serializer = new MysqlTsvSerializer();
        try {
            $sorter->sort($inputTsv, $outputTsv, $sortPositions, $serializer);
            $this->fail("should have thrown an exception");
        } catch (\Exception $e) {
        }
    }
}
