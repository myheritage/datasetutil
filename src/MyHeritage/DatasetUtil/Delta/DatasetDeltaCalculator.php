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

use MyHeritage\DatasetUtil\Comparator\ComparatorInterface;

/**
 * Compares an old and a new datasets and produces the delta that needs to be applied on the old dataset.
 * The input datasets are given as iterators over arrays containing name-value fields representing records.
 * A subset of the fields are defined to be the key of each record.
 * Both datasets MUST be given sorted by their keys.
 */
class DatasetDeltaCalculator
{
    /**
     * Last seen key of old dataset, for verifying it is sorted
     *
     * @var array
     */
    private $prevOldKey;

    /**
     * Last seen key of new dataset, for verifying it is sorted
     *
     * @var array
     */
    private $prevNewKey;

    /**
     * Compares and existing dataset to a new dataset and calculates the delta
     *
     * Old and new iterators should return arrays containing key=>value fields, and MUST be sorted by their keys
     *
     * @param \Iterator $oldDataset old dataset
     * @param \Iterator $newDataset new dataset
     * @param string[] $keyFields Ordered list of field names that define the record key
     * @param ComparatorInterface $comparator
     * @param DeltaCollectorInterface $delta collector of delta actions
     */
    public function calculateDatasetDelta($oldDataset, $newDataset, $keyFields, $comparator, $delta)
    {
        $this->leapFrogCompare($oldDataset, $newDataset, $keyFields, $comparator, $delta);
    }

    /**
     * Scans both datasets in parallel and passes delta actions to the delta container
     *
     * @param \Iterator $oldDataset old dataset
     * @param \Iterator $newDataset new dataset
     * @param string[] $keyFields Ordered list of field names that define the record key
     * @param ComparatorInterface $comparator
     * @param DeltaCollectorInterface $delta collector of delta actions
     */
    private function leapFrogCompare($oldDataset, $newDataset, $keyFields, $comparator, $delta)
    {
        // clear last seen keys
        $this->prevOldKey = null;
        $this->prevNewKey = null;

        // scan both datasets until one is exhausted
        while ($oldDataset->valid() && $newDataset->valid()) {
            $this->leapFrogCompareCurrentValues($oldDataset, $newDataset, $keyFields, $comparator, $delta);
        }

        // pass any remaining items in the old dataset as delete actions
        $this->passRemainingDeletes($oldDataset, $keyFields, $comparator, $delta);

        // pass any remaining items in the new dataset as insert actions
        $this->passRemainingInserts($newDataset, $keyFields, $comparator, $delta);

        // close the collector
        $delta->close();
    }

    /**
     * Apply leap-frog step on current values of both datasets
     *
     * Advance the dataset iterators as needed (both or just one)
     *
     * @param \Iterator $oldDataset old dataset
     * @param \Iterator $newDataset new dataset
     * @param string[] $keyFields Ordered list of field names that define the record key
     * @param ComparatorInterface $comparator
     * @param DeltaCollectorInterface $delta collector of delta actions
     */
    private function leapFrogCompareCurrentValues($oldDataset, $newDataset, $keyFields, $comparator, $delta)
    {
        $oldValues = $oldDataset->current();
        $newValues = $newDataset->current();

        $this->verifyDatasetsAreSorted($oldValues, $newValues, $keyFields, $comparator);

        switch ($comparator->compare($oldValues, $newValues, $keyFields)) {
            case 0:
                // keys are equal
                $keyValues = $this->extractKeyValues($oldValues, $keyFields);
                $updatedValues = $this->extractUpdatedValues($oldValues, $newValues, $keyFields);
                if ($updatedValues) {
                    $delta->addUpdate($keyValues, $updatedValues);
                }
                $oldDataset->next();
                $newDataset->next();
                break;
            case -1:
                // pk1 is less than pk2
                $keyValues = $this->extractKeyValues($oldValues, $keyFields);
                $delta->addDelete($keyValues);
                $oldDataset->next();
                break;
            case 1:
                // pk1 is greater than pk2
                $delta->addInsert($newValues);
                $newDataset->next();
                break;
        }
    }

    /**
     * Passes remaining items in old dataset to the delta collector as deletes
     *
     * @param \Iterator $oldDataset old dataset
     * @param string[] $keyFields Ordered list of field names that define the record key
     * @param ComparatorInterface $comparator
     * @param DeltaCollectorInterface $delta collector of delta actions
     */
    private function passRemainingDeletes($oldDataset, $keyFields, $comparator, $delta)
    {
        while ($oldDataset->valid()) {
            $values = $oldDataset->current();
            $this->verifyDatasetsAreSorted($values, null, $keyFields, $comparator);
            $keyValues = $this->extractKeyValues($values, $keyFields);
            $delta->addDelete($keyValues);
            $oldDataset->next();
        }
    }

    /**
     * Passes remaining items in dataset to the delta collector as inserts
     *
     * @param \Iterator $newDataset new dataset
     * @param string[] $keyFields Ordered list of field names that define the record key
     * @param ComparatorInterface $comparator
     * @param DeltaCollectorInterface $delta collector of delta actions
     */
    private function passRemainingInserts($newDataset, $keyFields, $comparator, $delta)
    {
        while ($newDataset->valid()) {
            $values = $newDataset->current();
            $this->verifyDatasetsAreSorted(null, $values, $keyFields, $comparator);
            $delta->addInsert($values);
            $newDataset->next();
        }
    }

    /**
     * Extracts the key values from an array of name-value mappings
     *
     * @param array $values map of fields to values
     * @param string[] $keyFields Ordered list of field names that define the record key
     * @return array mapping key fields to values
     */
    private function extractKeyValues($values, $keyFields)
    {
        $keyValues = array();
        foreach ($keyFields as $field) {
            $keyValues[$field] = $values[$field];
        }
        return $keyValues;
    }

    /**
     * Extracts the updated values from the old and new arrays of values
     *
     * @param array $values1 mapping fields to values
     * @param array $values2 mapping fields to values
     * @return array
     */
    private function extractUpdatedValues($values1, $values2)
    {
        $updatedValues = array();
        foreach ($values2 as $field => $value) {
            if ($value !== $values1[$field]) {
                $updatedValues[$field] = $value;
            }
        }
        return $updatedValues;
    }

    /**
     * @param array $oldValues
     * @param array $newValues
     * @param array $keyFields
     * @param ComparatorInterface $comparator
     * @throws \Exception
     */
    private function verifyDatasetsAreSorted($oldValues, $newValues, $keyFields, $comparator)
    {
        if ($oldValues) {
            if ($this->prevOldKey) {
                if ($comparator->compare($this->prevOldKey, $oldValues, $keyFields) > 0) {
                    throw new \Exception(
                        "old dataset is not sorted by key: " . print_r($oldValues, true) .
                        " comes after " . print_r($this->prevOldKey, true));
                }
            }
            $this->prevOldKey = $oldValues;
        }

        if ($newValues) {
            if ($this->prevNewKey) {
                if ($comparator->compare($this->prevNewKey, $newValues, $keyFields) > 0) {
                    throw new \Exception(
                        "new dataset is not sorted by key: " . print_r($newValues, true) .
                        " comes after " . print_r($this->prevNewKey, true));
                }
            }
            $this->prevNewKey = $newValues;
        }
    }

}
