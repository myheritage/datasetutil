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

namespace MyHeritage\DatasetUtil\Delta;

/**
 * Implementation of DeltaCollectorInterface that generates SQL statements (MySQL flavor)
 * for applying the delta and writes them to a given file.
 */
class MysqlDeltaSerializer implements DeltaSerializerInterface
{

    /**
     * Name of table to use in all delta actions
     *
     * @var string
     */
    private $table;

    /**
     * @var bool
     */
    private $useIgnore;

    /**
     * The constructor.
     *
     * @param $table
     */
    public function __construct($table)
    {
        $this->table = $table;

        // By default we should not add 'IGNORE' to statements
        $this->useIgnore = false;
    }

    /**
     * Serializes an insert delta action
     *
     * @param array $values maps field names to values
     * @return string
     */
    public function serializeInsert($values)
    {
        $assignments = $this->constructAssignments($values, ',');

        $ignoreString = $this->getIgnoreKeyword() ? $this->getIgnoreKeyword() . " " : "";

        return "INSERT " . $ignoreString . "INTO " . $this->table . " SET " . $assignments . ";\n";
    }

    /**
     * Serializes an update delta action
     *
     * @param array $key maps key field names to values
     * @param array $values maps updated field names to values
     * @return string
     */
    public function serializeUpdate($key, $values)
    {
        $condition = $this->constructAssignments($key, ' AND ');
        $assignments = $this->constructAssignments($values, ',');

        $ignoreString = $this->getIgnoreKeyword() ? $this->getIgnoreKeyword() . " " : "";

        return "UPDATE " . $ignoreString . $this->table . " SET " . $assignments . " WHERE " . $condition . ";\n";
    }

    /**
     * Serializes a delete delta action
     *
     * @param array $key maps key names to values
     * @return string
     */
    public function serializeDelete($key)
    {
        $condition = $this->constructAssignments($key, ' AND ');

        return "DELETE FROM " . $this->table . " WHERE " . $condition . ";\n";
    }

    /**
     * Constructs a comma separated field=value... string from a given array of values
     *
     * @param array $values
     * @param string $glue
     * @return string
     * @throws \Exception
     */
    private function constructAssignments($values, $glue)
    {
        if (!$values) {
            throw new \Exception("Empty list of values given for table " . $this->table);
        }
        $assignments = array();
        foreach ($values as $field => $value) {
            $assignments[] = $field . '=' . "'" . mysql_escape_string($value) . "'";
        }
        return implode($glue, $assignments);
    }

    /**
     * IGNORE flag
     *
     * @param $useIgnore
     */
    public function setUseIgnore($useIgnore)
    {
        $this->useIgnore = $useIgnore;
    }

    /**
     * Returns IGNORE keyword if needed.
     *
     * @return string
     */
    private function getIgnoreKeyword()
    {
        return $this->useIgnore ? "IGNORE" : "";
    }
}