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

namespace MyHeritage\Tests\DatasetUtil\File;

use MyHeritage\DatasetUtil\File\TsvIterator;
use MyHeritage\DatasetUtil\Serializer\MysqlTsvSerializer;

/**
 * Test class for TsvIterator
 */
class TsvIteratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function testEmpty()
    {
        $fields = array("i1", "i2", "s1", "s2");
        $serializer = new MySQLTsvSerializer();
        $iter = new TsvIterator(__DIR__ . '/tsv-empty.tsv', $fields, $serializer);
        $this->assertFalse($iter->valid());
    }

    /**
     * @return void
     */
    public function testSimple()
    {
        $fields = array("i1", "i2", "s1", "s2");
        $serializer = new MySQLTsvSerializer();
        $iter = new TsvIterator(__DIR__ . '/tsv-simple.tsv', $fields, $serializer);
        $this->assertTrue($iter->valid());
        $row = $iter->current();
        $this->assertEquals(array("i1"=>1, "i2"=>2, "s1"=> "ABC", "s2"=>"abc"), $row);
        $iter->next();
        $row = $iter->current();
        $this->assertEquals(array("i1"=>4, "i2"=>5, "s1"=> "DEF", "s2"=>"def"), $row);
        $iter->next();
        $this->assertFalse($iter->valid());
    }

    /**
     * @return void
     */
    public function testEscaped()
    {
        $fields = array("i1", "i2", "s1", "s2");
        $serializer = new MySQLTsvSerializer();
        $iter = new TsvIterator(__DIR__ . '/tsv-escaped.tsv', $fields, $serializer);
        $this->assertTrue($iter->valid());
        $row = $iter->current();
        $this->assertEquals(array("i1"=>1, "i2"=>2, "s1"=> "ABC", "s2"=>"a\tbc"), $row);
        $iter->next();
        $row = $iter->current();
        $this->assertEquals(array("i1"=>4, "i2"=>5, "s1"=> "DEF\\", "s2"=>"def"), $row);
        $iter->next();
        $this->assertFalse($iter->valid());
    }

    /**
     * @return void
     */
    public function testMultiline()
    {
        $fields = array("i1", "i2", "s1");
        $serializer = new MySQLTsvSerializer();
        $iter = new TsvIterator(__DIR__ . '/tsv-multiline.tsv', $fields, $serializer);
        $this->assertTrue($iter->valid());
        $row = $iter->current();
        $this->assertEquals(array("i1"=>1, "i2"=>2, "s1"=> "AB\nC"), $row);
        $iter->next();
        $row = $iter->current();
        $this->assertEquals(array("i1"=>4, "i2"=>5, "s1"=> "D\\"), $row);
        $iter->next();
        $row = $iter->current();
        $this->assertEquals(array("i1"=>6, "i2"=>7, "s1"=> "E\\\nF"), $row);
        $iter->next();
        $this->assertFalse($iter->valid());
    }
}
