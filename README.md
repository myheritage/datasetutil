
Utility classes for manipulating datasets

Delta
-----
- Generic comparison of an old and a new datasets to produce the delta actions that need
  to be applied on the old dataset. The input datasets are given as iterators over arrays
  containing name-value fields representing records. The resulting delta actions are passed
  to a generic collector and serialized using a generic serializer.
- Concrete implementation of an input iterator: File/TsvIterator
- Concrete implementation of a delta action collector: FileDeltaCollector
- Concrete implementation of a delta action serializer: SqlDeltaSerializer

File
----
- Iterator over records stored in a TSV file
- Sorter of TSV files

Serializer
----------
- Genaric record serialization interface
- Concrete implementation that emulates MySQL-default TSV format

Comparator
----------
- Generic record key comparator interface used by DatasetDeltaCalculator
- Concrete implementation for comparing numeric keys
