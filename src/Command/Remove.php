<?php

namespace Oesteve\Command;

use MicrosoftAzure\Storage\Blob\Models\Blob;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class Remove extends BaseCommand
{

    protected static $defaultName = 'delete';

    /** @var int  */
    private $deleted = 0;
    /** @var int  */
    private $filtered = 0;

    protected function configure()
    {
        parent::configure();

        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Action to delete bob files')
            ->addOption('olderThan', null, InputOption::VALUE_REQUIRED, 'Files older than this date will be deleted')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

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
                    $olderThanDateTime = $this->getOlderThanDateTime();
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
