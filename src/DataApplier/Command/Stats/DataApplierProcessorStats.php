<?php

namespace DataApplier\Command\Stats;

class DataApplierProcessorStats
{
    private $skippedItemsIdentifiers;
    private $updatedItemsIdentifiers;
    private $insertedItemsIdentifiers;
    private $deletedItemsIdentifiers;

    /**
     * @param string[] $skippedItemsIdentifiers
     * @param string[] $updatedItemsIdentifiers
     * @param string[] $insertedItemsIdentifiers
     * @param string[] $deletedItemsIdentifiers
     */
    public function __construct(
        array $skippedItemsIdentifiers,
        array $updatedItemsIdentifiers,
        array $insertedItemsIdentifiers,
        array $deletedItemsIdentifiers
    ) {
        $this->skippedItemsIdentifiers = $skippedItemsIdentifiers;
        $this->updatedItemsIdentifiers = $updatedItemsIdentifiers;
        $this->insertedItemsIdentifiers = $insertedItemsIdentifiers;
        $this->deletedItemsIdentifiers = $deletedItemsIdentifiers;
    }

    public function getSkippedItemsIdentifiers()
    {
        return $this->skippedItemsIdentifiers;
    }

    public function getUpdatedItemsIdentifiers()
    {
        return $this->updatedItemsIdentifiers;
    }

    public function getInsertedItemsIdentifiers()
    {
        return $this->insertedItemsIdentifiers;
    }

    public function getDeletedItemsIdentifiers()
    {
        return $this->deletedItemsIdentifiers;
    }

    public function getSkippedItemsIdentifierCount()
    {
        return count($this->skippedItemsIdentifiers);
    }

    public function getUpdatedItemsIdentifierCount()
    {
        return count($this->updatedItemsIdentifiers);
    }

    public function getInsertedItemsIdentifierCount()
    {
        return count($this->insertedItemsIdentifiers);
    }

    public function getDeletedItemsIdentifierCount()
    {
        return count($this->deletedItemsIdentifiers);
    }
}
