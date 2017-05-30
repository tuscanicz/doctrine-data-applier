<?php

namespace DataApplier\Command;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\Common\Collections\Expr\Value;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use Exception;
use DataApplier\Entity\Backup\DoctrineEntityBacker;
use DataApplier\Entity\DataApplicableEntityInterface;
use DataApplier\Entity\Identity\DataApplierIdentifierCalculator;

class DataApplierProcessorFacade
{
    const LENGTH_IN_DOTS = 20;
    const FPS = 21;

    private $entityManager;
    private $dataApplierIdentifierCalculator;
    private $doctrineEntityBacker;

    public function __construct(
        EntityManagerInterface $entityManager,
        DataApplierIdentifierCalculator $dataApplierIdentifierCalculator,
        DoctrineEntityBacker $doctrineEntityBacker
    ) {
        $this->entityManager = $entityManager;
        $this->dataApplierIdentifierCalculator = $dataApplierIdentifierCalculator;
        $this->doctrineEntityBacker = $doctrineEntityBacker;
    }

    /**
     * @param DataApplicableEntityInterface[] $mergedDoctrineEntities
     * @param bool $showProgress
     */
    public function flushChanges(array $mergedDoctrineEntities, $showProgress)
    {
        $unitOfWork = $this->entityManager->getUnitOfWork();
        $insertions = $unitOfWork->getScheduledEntityInsertions();
        $updates = $unitOfWork->getScheduledEntityUpdates();
        $totalOperationsCount = count($insertions) + count($updates);

        $i = 0;
        foreach ($insertions as $entity) {
            $this->resolveEntityRelations($entity, $mergedDoctrineEntities);
            $this->drawProgressBar($showProgress, ceil(100 * ($i / $totalOperationsCount)));
            $i++;
        }

        foreach ($updates as $entity) {
            $this->resolveEntityRelations($entity, $mergedDoctrineEntities);
            $this->drawProgressBar($showProgress, ceil(100 * ($i / $totalOperationsCount)));
            $i++;
        }
        $this->drawProgressBar($showProgress, ceil(100 * ($i / $totalOperationsCount)));

        $this->entityManager->flush();
    }

    public function mergeOrPersistAndReturnPersistedItems(array $doctrineEntities)
    {
        $mergedDoctrineEntities = [];
        foreach ($doctrineEntities as $doctrineEntity) {
            if ($doctrineEntity instanceof DataApplicableEntityInterface) {
                $mergedDoctrineEntities[] = $this->mergeOrPersistAndReturnPersistedItem($doctrineEntity);
            }
        }

        return $mergedDoctrineEntities;
    }

    public function mergeOrPersistAndReturnPersistedItem($doctrineEntity)
    {
        if ($doctrineEntity instanceof DataApplicableEntityInterface) {
            if ($doctrineEntity->getId() === null) {
                $this->entityManager->persist($doctrineEntity);
                $mergedDoctrineEntity = $doctrineEntity;
            } else {
                $mergedDoctrineEntity = $this->entityManager->merge($doctrineEntity);
            }

            return $mergedDoctrineEntity;
        }

        throw new Exception('Cannot persist doctrine entity without '.DataApplicableEntityInterface::class);
    }

    public function deleteAndReturnDeletedItems(array $processedItems)
    {
        $deletedItems = [];
        if (count($processedItems) > 0) {
            foreach ($processedItems as $doctrineEntityClassName => $doctrineEntityIdentifiers) {
                $itemsToBeDeleted = $this->getItemsToBeDeleted($doctrineEntityClassName, $doctrineEntityIdentifiers);
                foreach ($itemsToBeDeleted as $itemToBeDeleted) {
                    if ($itemToBeDeleted instanceof DataApplicableEntityInterface) {
                        $deletedItems[] = $itemToBeDeleted->getDataApplierEntityIdentifier();
                        $this->doctrineEntityBacker->backupEntity($itemToBeDeleted);
                        $this->entityManager->remove($itemToBeDeleted);
                    } else {
                        throw new Exception(
                            'Unrecognized item for deletion: ' . get_class($itemToBeDeleted)
                        );
                    }
                }
            }
        }

        return $deletedItems;
    }

    /**
     * @param string $doctrineEntityClassName
     * @param string $doctrineEntityIdentifier
     * @return null|DataApplicableEntityInterface
     */
    public function getManagedEntityByDoctrineEntity($doctrineEntityClassName, $doctrineEntityIdentifier)
    {
        $repository = $this->entityManager->getRepository($doctrineEntityClassName);

        return $repository->findOneBy(['dataApplierEntityIdentifier' => $doctrineEntityIdentifier]);
    }

    public function getItemsToBeDeleted($doctrineEntityClassName, array $doctrineEntityIdentifiers)
    {
        $repository = $this->entityManager->getRepository($doctrineEntityClassName);
        $criteria = Criteria::create();
        $criteria->where(
            Criteria::expr()->notIn('dataApplierEntityIdentifier', $doctrineEntityIdentifiers)
        );
        $criteria->andWhere(
            new Comparison('dataApplierEntityIdentifier', Comparison::NEQ, new Value(null))
        );

        return $repository->matching($criteria);
    }

    /**
     * @param DataApplicableEntityInterface[] $mergedDoctrineEntities
     * @param string $entityIdentifier
     * @return DataApplicableEntityInterface
     */
    private function getMergedDoctrineEntityByIdentifier(array $mergedDoctrineEntities, $entityIdentifier)
    {
        foreach ($mergedDoctrineEntities as $doctrineEntity) {
            if ($doctrineEntity->getDataApplierEntityIdentifier() === $entityIdentifier) {

                return $doctrineEntity;
            }
        }

        throw new Exception('Could not find merged doctrine entity by identifier: '.$entityIdentifier);
    }

    /**
     * @todo: present resolver by associated entity type (collection, array, etc...) in order to split this mess
     * @param $entity
     * @param DataApplicableEntityInterface[] $mergedDoctrineEntities
     */
    private function resolveEntityRelations($entity, array $mergedDoctrineEntities)
    {
        $classMetaData = $this->entityManager->getClassMetadata(get_class($entity));
        if (count($classMetaData->getAssociationNames()) > 0) {
            $resolvedRelatedEntities = [];
            foreach ($classMetaData->getAssociationNames() as $associationName) {
                $associatedEntity = $classMetaData->getFieldValue($entity, $associationName);
                if ($associatedEntity !== null) {
                    if ($associatedEntity instanceof Collection) {
                        $resolvedRelatedEntities = [];
                        foreach ($associatedEntity->toArray() as $item) {
                            $resolvedRelatedEntities[] = $this->resolveEntityRelatedEntity($item, $mergedDoctrineEntities);
                        }
                        $associatedEntity->clear();
                        foreach ($resolvedRelatedEntities as $resolvedRelatedEntity) {
                            $associatedEntity->add($resolvedRelatedEntity);
                        }
                    } else if (is_array($associatedEntity) === true) {
                        $resolvedRelatedEntities = [];
                        foreach ($associatedEntity as $item) {
                            $resolvedRelatedEntities[] = $this->resolveEntityRelatedEntity($item, $mergedDoctrineEntities);
                        }
                        $associatedEntity = $resolvedRelatedEntities;
                    } else {
                        $associatedEntity = $this->resolveEntityRelatedEntity($associatedEntity, $mergedDoctrineEntities);
                        $resolvedRelatedEntities = [$associatedEntity];
                    }
                    $classMetaData->setFieldValue($entity, $associationName, $associatedEntity);
                }
            }
            foreach ($resolvedRelatedEntities as $resolvedRelatedEntity) {
                if ($resolvedRelatedEntity instanceof DataApplicableEntityInterface) {
                    $this->resolveEntityRelations($resolvedRelatedEntity, $mergedDoctrineEntities);
                }
            }
        }
    }

    private function resolveEntityRelatedEntity($associatedEntity, array $mergedDoctrineEntities)
    {
        $associatedEntityState = $this->entityManager->getUnitOfWork()->getEntityState(
            $associatedEntity
        );
        if ($associatedEntity instanceof DataApplicableEntityInterface) {
            if ($associatedEntityState !== UnitOfWork::STATE_MANAGED) {
                $mergedAssociatedEntity = $this->getMergedDoctrineEntityByIdentifier(
                    $mergedDoctrineEntities,
                    $this->dataApplierIdentifierCalculator->calculateIdentifier($associatedEntity)
                );
                $associatedEntity->setId($mergedAssociatedEntity->getId());
                $managedAssociatedEntityProxy = $this->entityManager->merge($associatedEntity);
                $managedAssociatedEntity = $this->entityManager->find(get_class($associatedEntity), $managedAssociatedEntityProxy->getId());

                return $managedAssociatedEntity;
            }
        } else {
            if ($associatedEntityState !== UnitOfWork::STATE_MANAGED) {
                throw new Exception(
                    sprintf(
                        'Cannot resolve entity %s relation on entity without %s interface',
                        get_class($associatedEntity),
                        DataApplicableEntityInterface::class
                    )
                );
            }
        }

        return $associatedEntity;
    }

    private function drawProgressBar($showProgress, $percentageDone)
    {
        if ($showProgress === true) {
            $progress = ceil(($percentageDone / 100) * self::LENGTH_IN_DOTS);
            system('clear');
            echo DataApplierCommandFacade::PROGRESS_MESSAGE . "\n";
            for ($i = 0; $i < $progress; $i++) {
                echo '.';
            }
            echo ' (' . $percentageDone . '% done)';
            echo "\n";
            usleep(1000000 / self::FPS);
        }
    }
}
