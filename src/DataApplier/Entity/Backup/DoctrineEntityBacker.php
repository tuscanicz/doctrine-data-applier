<?php

namespace DataApplier\Entity\Backup;

use DataApplier\Entity\DataApplicableEntityInterface;

class DoctrineEntityBacker
{
    private $logDir;

    public function __construct(
        $logDir
    ) {
        $this->logDir = $logDir;
    }

    public function backupEntity($doctrineEntity)
    {
        if ($doctrineEntity instanceof DataApplicableEntityInterface) {
            $className = str_replace('\\', '', get_class($doctrineEntity));

            return file_put_contents(
                $this->logDir.DIRECTORY_SEPARATOR.'backed-entity-'.$className.'-'.$doctrineEntity->getId(),
                serialize($doctrineEntity)
            );
        }

        throw new BackupFailedException(
            sprintf(
                'Could not backup DoctrineEntity of class %s before deletion',
                get_class($doctrineEntity)
            )
        );
    }
}
