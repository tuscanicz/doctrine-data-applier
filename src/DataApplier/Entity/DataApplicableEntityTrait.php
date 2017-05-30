<?php

namespace DataApplier\Entity;

use DateTime;

trait DataApplicableEntityTrait
{
    /**
     * @ORM\Column(name="DATA_APPLIER_ID", type="string", length=100, nullable=true, unique=true)
     * @var string
     */
    protected $dataApplierEntityIdentifier;

    /**
     * @ORM\Column(name="DATA_APPLIER_ID_UPDATE", type="datetime", nullable=true)
     * @var Datetime
     */
    protected $dataApplierEntityIdentifierUpdatedAt;

    abstract public function getId();

    abstract public function setId();

    public function hasDataApplierEntityIdentifier()
    {
        return $this->dataApplierEntityIdentifier !== null;
    }

    public function getDataApplierEntityIdentifier()
    {
        return $this->dataApplierEntityIdentifier;
    }

    /**
     * @param string $dataApplierEntityIdentifier
     */
    public function setDataApplierEntityIdentifier($dataApplierEntityIdentifier)
    {
        $this->dataApplierEntityIdentifier = $dataApplierEntityIdentifier;
    }

    public function hasDataApplierEntityIdentifierUpdatedAt()
    {
        return $this->dataApplierEntityIdentifierUpdatedAt !== null;
    }

    public function getDataApplierEntityIdentifierUpdatedAt()
    {
        return $this->dataApplierEntityIdentifierUpdatedAt;
    }

    public function setDataApplierEntityIdentifierUpdatedAt(DateTime $dataApplierEntityIdentifierUpdatedAt)
    {
        $this->dataApplierEntityIdentifierUpdatedAt = $dataApplierEntityIdentifierUpdatedAt;
    }

    public function __toString()
    {
        return get_class($this).'#'.$this->getDataApplierEntityIdentifier();
    }
}
