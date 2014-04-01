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

use MyHeritage\DatasetUtil\Delta\MysqlDeltaSerializer;

/**
 * Test class for DatasetDeltaCalculator
 */
class SqlDeltaSerializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test inserts
     *
     * @return void
     */
    public function testInsert()
    {
        $serializer = new MysqlDeltaSerializer("ttt");
        $sql = $serializer->serializeInsert(array("a"=>5, "b"=>"abc"));
        $expected = "INSERT INTO ttt SET a='5',b='abc';\n";
        $this->assertEquals($expected, $sql);
    }

    /**
     * Test updates
     *
     * @return void
     */
    public function testUpdate()
    {
        $serializer = new MysqlDeltaSerializer("ttt");
        $sql = $serializer->serializeUpdate(array("k1"=>3, "k2"=>5), array("a"=>5, "b"=>"abc"));
        $expected = "UPDATE ttt SET a='5',b='abc' WHERE k1='3' AND k2='5';\n";
        $this->assertEquals($expected, $sql);
    }

    /**
     * Test deletes
     *
     * @return void
     */
    public function testDelete()
    {
        $serializer = new MysqlDeltaSerializer("ttt");
        $sql = $serializer->serializeDelete(array("k1"=>3, "k2"=>5));
        $expected = "DELETE FROM ttt WHERE k1='3' AND k2='5';\n";
        $this->assertEquals($expected, $sql);
    }

}
