<?php

namespace DataApplier\Mock\Data;

use DataApplier\Data\DataApplierInterface;

class TestDataApplier1 implements DataApplierInterface
{
    public function applyData()
    {
        return [
            TestEntity::createNew('value1', 'key1'),
            TestEntity::createNew('value2', 'key2'),
            TestEntity::createNew('value3', 'key3'),
        ];
    }
}
