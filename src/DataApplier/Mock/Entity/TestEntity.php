<?php

namespace DataApplier\Mock\Data;

use DataApplier\Annotation\DataApplierIdentifier;
use DataApplier\Entity\DataApplicableEntityInterface;
use DataApplier\Entity\DataApplicableEntityTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class TestEntity implements DataApplicableEntityInterface
{
    use DataApplicableEntityTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue()
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=250, nullable=false)
     * @DataApplierIdentifier()
     */
    private $value;

    /**
     * @var string
     * @ORM\Column(type="string", length=250, nullable=false)
     */
    private $key;

    public static function createNew($value, $key)
    {
        $self = new self;
        $self->setKey($key);
        $self->setValue($value);

        return $self;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }
}
