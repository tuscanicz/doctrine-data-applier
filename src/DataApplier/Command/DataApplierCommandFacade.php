<?php

namespace DataApplier\Command;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Exception;
use Monolog\Logger;

class DataApplierCommandFacade
{
    const PROGRESS_MESSAGE = 'Applying data';

    private $logger;
    private $dataApplierHandler;
    private $dataApplierProcessor;

    public function __construct(
        Logger $logger,
        DataApplierHandler $dataApplierHandler,
        DataApplierProcessor $dataApplierProcessor
    ) {
        $this->logger = $logger;
        $this->dataApplierHandler = $dataApplierHandler;
        $this->dataApplierProcessor = $dataApplierProcessor;
    }

    /**
     * @param bool $showProgress
     * @return Stats\DataApplierProcessorStats
     */
    public function applyData($showProgress)
    {
        $this->logger->addDebug('Applying new data');
        $doctrineEntities = $this->dataApplierHandler->getApplicableDoctrineEntities();
        $this->logger->addDebug(
            sprintf(
                'Processing %d doctrine entities',
                count($doctrineEntities)
            )
        );
        try {
            $dataApplierProcessorStats = $this->dataApplierProcessor->applyData($doctrineEntities, $showProgress);
        } catch (Exception $e) {
            if ($e instanceof DBALException || $e instanceof ORMException || $e instanceof ORMInvalidArgumentException) {
                $this->logger->addError(
                    sprintf(
                        '%s with message: %s, trace: %s',
                        get_class($e),
                        $e->getMessage(),
                        $e->getTraceAsString()
                    )
                );
            } else {
                $this->logger->addCritical(
                    sprintf(
                        '%s with message: %s, trace: %s',
                        get_class($e),
                        $e->getMessage(),
                        $e->getTraceAsString()
                    )
                );
            }
            throw $e;
        }
        $this->logger->addDebug(
            'New data application finished',
            [
                'deletedItems' => $dataApplierProcessorStats->getDeletedItemsIdentifiers(),
                'updatedItems' => $dataApplierProcessorStats->getUpdatedItemsIdentifiers(),
                'InsertedItems' => $dataApplierProcessorStats->getInsertedItemsIdentifiers(),
                'skippedItems' => $dataApplierProcessorStats->getSkippedItemsIdentifiers(),
            ]
        );

        return $dataApplierProcessorStats;
    }
}
