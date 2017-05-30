<?php

namespace DataApplier\Command;

use DataApplier\Data\DataApplierInterface;
use Exception;

class DataApplierHandler
{
    /** @var DataApplierInterface[] */
    private $dataAppliers;

    public function addTaggedDataApplier(DataApplierInterface $dataApplier)
    {
        $this->dataAppliers[] = $dataApplier;
    }

    public function getApplicableDoctrineEntities()
    {
        $applicableDoctrineEntities = [];
        if (count($this->dataAppliers) > 0) {
            foreach ($this->dataAppliers as $dataApplier) {
                foreach ($dataApplier->applyData() as &$applicableDoctrineEntity) {
                    $applicableDoctrineEntities[] = $applicableDoctrineEntity;
                }
            }

            return $applicableDoctrineEntities;
        }

        throw new Exception(
            'No DataAppliers are configured, please register them as services and tag by doctrine.data_applier'
        );
    }
}
