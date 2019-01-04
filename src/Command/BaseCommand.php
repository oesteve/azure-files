<?php

namespace Oesteve\Command;

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Blob\Models\Blob;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class BaseCommand extends Command
{

    /** @var InputInterface */
    protected $input;
    /** @var OutputInterface */
    protected $output;

    protected static $defaultName = 'delete';

    /** @var int  */
    private $deleted = 0;
    /** @var int  */
    private $filtered = 0;

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Action to delete bob files')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to remove files on bloc account based on some criteria')
            ->addOption('connectionString', null, InputOption::VALUE_REQUIRED, 'Azure Blobs connection string')
            ->addOption('containerName', null, InputOption::VALUE_REQUIRED, 'Azure Blob container name')
            ->addOption('dryRun', null, InputOption::VALUE_NONE, "The blobs will not be delete")
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $this->input = $input;
        $this->output = $output;
    }

    /**
     * @return BlobRestProxy
     */
    protected function getClient()
    {
        $blobClient = BlobRestProxy::createBlobService(
            $this->input->getOption("connectionString")
        );
        return $blobClient;
    }

    /**
     * @return bool|null|string|string[]
     */
    protected function getContainer()
    {
        $container = $this->input->getOption("containerName");
        return $container;
    }

    /**
     * @return bool
     */
    protected function isDrayRun()
    {
        return (bool)$this->input->getOption('dryRun');
    }

    /**
     * @return \DateTime
     * @throws \Exception
     */
    protected function getOlderThanDateTime()
    {
        $option = $this->input->getOption('olderThan');


        if(!$option){
            throw new \Exception(sprintf('olderThan options is required'));
        }

        $olderThanDateTime = new \DateTime($option);
        return $olderThanDateTime;
    }


    private function printResume()
    {
        $this->output->writeln(sprintf("%d files was filtered, %d deleted.", $this->filtered, $this->deleted));
    }

}
