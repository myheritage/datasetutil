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

use MyHeritage\DatasetUtil\Serializer\MysqlTsvSerializer;

/**
 * Test class for MysqlTsvSerializer
 */
class MysqlTsvSerializerTest extends \PHPUnit_Framework_TestCase
{
    public function dataForTestSerialize()
    {
        $data = array();

        $data[] = array(array("aaa"), "aaa\n");                             // single plain value
        $data[] = array(array("aaa", "bbb", "ccc"), "aaa\tbbb\tccc\n");     // multiple plain values
        $data[] = array(array("aaa", null, null), "aaa\t\\N\t\\N\n");       // null values
        $data[] = array(array("aaa", chr(0), "13"), "aaa\t\\0\t13\n");      // special char: null
        $data[] = array(array("aaa", chr(9), "13"), "aaa\t\\\t\t13\n");     // special char: tab
        $data[] = array(array("aaa", chr(10), "13"), "aaa\t\\\n\t13\n");    // special char: newline
        $data[] = array(array("aaa", '\\'.chr(10), "13"), "aaa\t\\\\\\\n\t13\n");    // special char: \ before newline
        $data[] = array(array("aaa", '\\\\'.chr(10), "13"), "aaa\t\\\\\\\\\\\n\t13\n");    // special char: \\ before newline
        $data[] = array(array("aaa", '\\\\\\'.chr(10), "13"), "aaa\t\\\\\\\\\\\\\\\n\t13\n");    // special char: \\\ before newline
        $data[] = array(array("aaa", '\\'.chr(10) . 'ok' . chr(10), "13"), "aaa\t\\\\\\\nok\\\n\t13\n");    // special char: \ before newline
        $data[] = array(array("aaa", '\\', "13"), "aaa\t\\\\\t13\n");       // special char: backslash (escape char)

        $data[] = array(array("\\", '\\n\\r\\0\\N', "13"), "\\\\\t\\\\n\\\\r\\\\0\\\\N\t13\n"); // false escaping

        return $data;
    }

    /**
     * @dataProvider dataForTestSerialize
     *
     * @param array $input
     * @param string $output
     */
    public function testSerialize($input, $output)
    {
        $serializer = new MySQLTsvSerializer();
        $this->assertEquals($output, $serializer->serialize($input));
    }

    /**
     * @dataProvider dataForTestSerialize
     *
     * @param array $input
     * @param string $output
     */
    public function testUnserialize($input, $output)
    {
        $serializer = new MysqlTsvSerializer();
        $this->assertEquals($input, $serializer->unserialize($output));
    }

    public function testFgets()
    {
        $serializer = new MysqlTsvSerializer();
        $srcData = array();
        foreach ($this->dataForTestSerialize() as $pair) {
            $srcData[] = $pair[0];
        }


        $fileHandle = fopen('php://memory', 'w+');
        foreach ($srcData as $value) {
            fwrite($fileHandle, $serializer->serialize($value));
        }
        rewind($fileHandle);

        $resultData = array();

        while (($line = $serializer->fgets($fileHandle)) !== false) {
            $resultData[] = $serializer->unserialize($line);
        }

        $this->assertEquals($srcData, $resultData);
    }
}
