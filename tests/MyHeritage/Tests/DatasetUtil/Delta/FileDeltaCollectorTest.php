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

use MyHeritage\DatasetUtil\Delta\DeltaSerializerInterface;
use MyHeritage\DatasetUtil\Delta\FileDeltaCollector;

/**
 * A dummy implementation of DeltaSerializerInterface for testing
 */
class DummySerializer implements DeltaSerializerInterface
{
    public function serializeInsert($values)
    {
        return "I-" . implode('-', $values) . "\n";
    }

    public function serializeUpdate($key, $values)
    {
        return "U-" . implode('-', $key) . '-' . implode('-', $values) . "\n";
    }

    public function serializeDelete($key)
    {
        return "D-" . implode('-', $key) . "\n";
    }
}

/**
 * Test class for DatasetDeltaCalculator
 */
class FileDeltaCollectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test creation of file with all kinds of delta actions
     *
     * @return void
     */
    public function testFileDeltaCollector()
    {
        $path = "/tmp/test-" . __METHOD__ . "-" . mt_rand(1, 1000000000);
        $serializer = new DummySerializer();
        $collector = new FileDeltaCollector($path, $serializer);
        $insertValue = array("a"=>1, "b"=>2);
        $updateKey = array("k1"=>1, "k2"=>2);
        $updateValue = array("a"=>3, "b"=>4);
        $deleteKey = array("k1"=>5, "k2"=>6);
        $collector->addInsert($insertValue);
        $collector->addUpdate($updateKey, $updateValue);
        $collector->addDelete($deleteKey);
        $collector->close();

        $content = file_get_contents($path);

        $expectedContent = "I-1-2\nU-1-2-3-4\nD-5-6\n";
        $this->assertEquals($expectedContent, $content);

        unlink($path);
    }

    /**
     * Test that an empty file is created if no actions are passed
     *
     * @return void
     */
    public function testEmptyFile()
    {
        $path = "/tmp/test-" . __METHOD__ . "-" . mt_rand(1, 1000000000);
        $serializer = new DummySerializer();
        $collector = new FileDeltaCollector($path, $serializer);
        $collector->close();

        $content = file_get_contents($path);

        $expectedContent = "";
        $this->assertEquals($expectedContent, $content);

        unlink($path);
    }

}
