<?php

namespace DataApplier\Command;

use DateTime;
use DataApplier\Command\Stats\DataApplierProcessorStats;
use DataApplier\Entity\DataApplicableEntityInterface;
use DataApplier\Entity\Identity\DataApplierIdentifierCalculator;

class DataApplierProcessor
{
    private $dataApplierIdentifierCalculator;
    private $dataApplierProcessorFacade;

    public function __construct(
        DataApplierIdentifierCalculator $dataApplierIdentifierCalculator,
        DataApplierProcessorFacade $dataApplierProcessorFacade
    ) {
        $this->dataApplierIdentifierCalculator = $dataApplierIdentifierCalculator;
        $this->dataApplierProcessorFacade = $dataApplierProcessorFacade;
    }

    /**
     * @param array $doctrineEntities
     * @param bool $showProgress
     * @return DataApplierProcessorStats
     */
    public function applyData(array $doctrineEntities, $showProgress)
    {
        $skippedItems = [];
        $updatedItems = [];
        $insertedItems = [];
        $processedItems = [];
        foreach ($doctrineEntities as $doctrineEntity) {
            $doctrineEntityClassName = get_class($doctrineEntity);
            $doctrineEntityIdentifier = $this->dataApplierIdentifierCalculator->calculateIdentifier($doctrineEntity);
            if ($doctrineEntity instanceof DataApplicableEntityInterface) {
                $doctrineEntity->setDataApplierEntityIdentifier($doctrineEntityIdentifier);
                $doctrineEntity->setDataApplierEntityIdentifierUpdatedAt(new DateTime());

                $managedEntity = $this->dataApplierProcessorFacade->getManagedEntityByDoctrineEntity(
                    $doctrineEntityClassName,
                    $doctrineEntityIdentifier
                );

                if (array_key_exists($doctrineEntityClassName, $processedItems) === false) {
                    $processedItems[$doctrineEntityClassName] = [];
                }
                $processedItems[$doctrineEntityClassName][] = $doctrineEntityIdentifier;

                if ($managedEntity !== null) {
                    if ($managedEntity instanceof DataApplicableEntityInterface) {
                        $doctrineEntity->setId($managedEntity->getId());
                    } else {
                        $skippedItems[] = $doctrineEntityIdentifier;
                    }
                    $updatedItems[] = $doctrineEntityIdentifier;
                } else {
                    $insertedItems[] = $doctrineEntityIdentifier;
                }
            } else {
                $skippedItems[] = $doctrineEntityIdentifier;
            }
        }
        $deletedItems = $this->dataApplierProcessorFacade->deleteAndReturnDeletedItems($processedItems);

        $mergedDoctrineEntities = $this->dataApplierProcessorFacade->mergeOrPersistAndReturnPersistedItems($doctrineEntities);
        $this->dataApplierProcessorFacade->flushChanges($mergedDoctrineEntities, $showProgress);

        return new DataApplierProcessorStats(
            $skippedItems,
            $updatedItems,
            $insertedItems,
            $deletedItems
        );
    }
}
