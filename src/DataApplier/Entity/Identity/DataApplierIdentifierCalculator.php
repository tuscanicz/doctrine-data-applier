<?php

namespace DataApplier\Entity\Identity;

use DataApplier\Annotation\DataApplierIdentifier;
use Doctrine\Common\Annotations\AnnotationReader;
use Exception;
use ReflectionClass;

class DataApplierIdentifierCalculator
{
    private $annotationReader;

    public function __construct(
        AnnotationReader $annotationReader
    ) {
        $this->annotationReader = $annotationReader;
    }

    public function calculateIdentifier($doctrineEntity)
    {
        return $this->getDoctrineEntityIdentifier(
            $doctrineEntity, new ReflectionClass($doctrineEntity)
        );
    }

    private function getDoctrineEntityIdentifier($doctrineEntity, ReflectionClass $doctrineEntityReflection)
    {
        $tableIdentifiers = [];
        $doctrineEntityProperties = $doctrineEntityReflection->getProperties();
        if (count($doctrineEntityProperties) === 0) {
            throw new Exception(
                'Could not resolve DataApplier identifiers for object with no attributes: ' . get_class($doctrineEntity)
            );
        }
        foreach ($doctrineEntityProperties as $doctrineEntityProperty) {
            $doctrineEntityPropertyAnnotations = $this->annotationReader->getPropertyAnnotations(
                $doctrineEntityProperty
            );
            if ($this->doctrineEntityHasIdentifierAnnotation($doctrineEntityPropertyAnnotations) === true) {
                $doctrineEntityProperty->setAccessible(true);
                $tableIdentifiers[] = $this->resolveIdentifier(
                    $doctrineEntityProperty->getValue($doctrineEntity)
                );
            }
        }
        if (count($tableIdentifiers) === 0) {
            throw new Exception(
                sprintf(
                    'Could not resolve DataApplier identifiers for entity: %s. Please, specify %s annotations.',
                    get_class($doctrineEntity),
                    DataApplierIdentifier::class
                )
            );
        }

        return $this->concatIdentifiers($tableIdentifiers);
    }

    private function doctrineEntityHasIdentifierAnnotation(array $tablePropertiesAnnotations)
    {
        if (count($tablePropertiesAnnotations) > 0) {
            foreach ($tablePropertiesAnnotations as $tablePropertyAnnotation) {
                if ($tablePropertyAnnotation instanceof DataApplierIdentifier) {
                    return true;
                }
            }
        }

        return false;
    }

    private function resolveIdentifier($tableIdentifier)
    {
        if (is_object($tableIdentifier)) {

            return $this->getDoctrineEntityIdentifier($tableIdentifier, new ReflectionClass($tableIdentifier));
        }

        return (string)$tableIdentifier;
    }

    private function concatIdentifiers(array $identifiers)
    {
        return implode('#', $identifiers);
    }
}
