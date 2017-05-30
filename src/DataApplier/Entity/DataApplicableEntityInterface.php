<?php

namespace DataApplier\Entity;

use DateTime;

interface DataApplicableEntityInterface
{
    public function getId();

    /** @param int $id */
    public function setId($id);

    public function hasDataApplierEntityIdentifier();

    public function getDataApplierEntityIdentifier();

    /** @param string $dataApplierEntityIdentifier */
    public function setDataApplierEntityIdentifier($dataApplierEntityIdentifier);

    public function hasDataApplierEntityIdentifierUpdatedAt();

    public function getDataApplierEntityIdentifierUpdatedAt();

    public function setDataApplierEntityIdentifierUpdatedAt(DateTime $dataApplierEntityIdentifierUpdatedAt);
}
