<?php

namespace Oesteve\Command;

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Blob\Models\Blob;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class Remove extends Command
{
    protected static $defaultName = 'delete';

    /** @var InputInterface */
    private $input;
    /** @var OutputInterface */
    private $output;

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
            ->addOption('olderThan', null, InputOption::VALUE_REQUIRED, 'Files older than this date will be deleted')
            ->addOption('dryRun', null, InputOption::VALUE_NONE, "The blobs will not be delete")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $this->input = $input;
        $this->output = $output;

        $blobClient = $this->getClient();

        $listBlobsOptions = new ListBlobsOptions();

        do{
            $container = $this->getContainer();

            $result = $blobClient->listBlobs($container
                , $listBlobsOptions);

            foreach ($result->getBlobs() as $blob)
            {

                $this->filtered++;
                try {
                    $olderThanDateTime = $this->getOltherThanDateTime();
                } catch (\Exception $e) {
                    $this->output->writeln($e->getMessage());
                    return 1;
                }
                if($blob->getProperties()->getLastModified() < $olderThanDateTime){
                    $this->delete($blob);
                }
            }

            $listBlobsOptions->setContinuationToken($result->getContinuationToken());
        } while($result->getContinuationToken());

        $this->printResume();
    }


    private function delete(Blob $blob)
    {
        $blobClient = $this->getClient();
        $this->deleted++;

        if($this->isDrayRun()){
            $this->output->writeln(sprintf("DryMode: %s will be deleted", $blob->getName()));
            return;
        }

        $blobClient->deleteBlob($this->getContainer(), $blob->getName());
        $this->output->writeln(sprintf("Blob: %s was been deleted", $blob->getName()));
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
    private function isDrayRun()
    {
        return (bool)$this->input->getOption('dryRun');
    }

    /**
     * @return \DateTime
     * @throws \Exception
     */
    protected function getOltherThanDateTime()
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
