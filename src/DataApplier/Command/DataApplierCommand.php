<?php

namespace DataApplier\Command;

use DataApplier\Command\Format\CommandFormatter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DataApplierCommand extends Command
{
    private $dataApplierCommandFacade;
    private $commandFormatter;

    public function __construct(
        DataApplierCommandFacade $dataApplierCommandFacade,
        CommandFormatter $commandFormatter
    ) {
        $this->dataApplierCommandFacade = $dataApplierCommandFacade;
        $this->commandFormatter = $commandFormatter;

        parent::__construct();
    }

    protected function configure()
    {
        parent::configure();

        $this->setName('data:apply')
            ->addOption('showProgress', 'p');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $showProgress = $input->getOption('showProgress') ? true : false;

        $this->commandFormatter->configureOutputFormats($output);

        $output->writeln(DataApplierCommandFacade::PROGRESS_MESSAGE);

        $dataApplierProcessorStats = $this->dataApplierCommandFacade->applyData($showProgress);

        if ($dataApplierProcessorStats->getSkippedItemsIdentifierCount() > 0) {
            $output->writeln(
                $this->commandFormatter->formatError(
                    'Processing finished, but there were some skipped items. Please, see the data applier logs.'
                )
            );
        } else {
            $output->writeln(
                $this->commandFormatter->formatSuccess('Finished with no errors')
            );
        }

        $output->writeln('Deleted items count: '.$dataApplierProcessorStats->getDeletedItemsIdentifierCount());
        $output->writeln('Inserted items count: '.$dataApplierProcessorStats->getInsertedItemsIdentifierCount());
        $output->writeln('Updated items count: '.$dataApplierProcessorStats->getUpdatedItemsIdentifierCount());
        $output->writeln('Skipped items count: '.$dataApplierProcessorStats->getSkippedItemsIdentifierCount());
    }
}
