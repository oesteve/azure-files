<?php

namespace Oesteve\Command;


use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Upload extends BaseCommand
{

    protected static $defaultName = 'uploadd';

    protected function configure()
    {
        parent::configure();

        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Action to move from FS to Blobs container')
            ->addArgument('source', InputArgument::REQUIRED, 'The full source path of file to upload')
            ->addArgument('destination', InputArgument::REQUIRED, 'The destination blob name')
            ;
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        parent::execute($input, $output);

        $source = $this->getSource();
        $prefix = trim($this->getDestination(),'/');

        if(is_file($source)){
            $blobName = sprintf('%s/%s',$prefix, basename($source));
            $this->uploadBlob($source, $blobName);
        }else {
            // Is dir ...
            $files = $this->getDirContents($source, $files);

            foreach ($files as $file){
                $blobName = sprintf('%s/%s',$prefix,str_replace($this->getSource(), '', $file));
                $this->uploadBlob($file, $blobName);
            }
        }
    }


    /**
     * @throws \Exception
     */
    private function getSource()
    {
        $path = $this->input->getArgument("source");

        if(!file_exists($path)){
            throw new \Exception(sprintf("The path %s don't exist", $path));
        }

        return $path;
    }

    private function getDestination()
    {
        return $this->input->getArgument("destination");
    }

    protected function uploadBlob(string $pathFile, string $blob)
    {
        $blobClient = $this->getClient();
        $content = fopen($pathFile, "r");


        //Upload blob
        if($this->isDrayRun()){
            $this->output->writeln(sprintf("DryMode: %s will be upload to %s", $pathFile, $blob));
            return false;
        }else{
            $blobClient->createBlockBlob($this->getContainer(), $blob, $content);
        }

        // And remove ?
        if($this->removeSource()){

        }

    }

    /**
     * @param $dir
     * @param array $results
     * @param bool $recursive
     * @return array
     * @throws \Exception
     */
    private function getDirContents($dir, &$results = array()){
        $files = scandir($dir);

        foreach($files as $key => $value){
            $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
            if(!is_dir($path)) {
                $results[] = $path;
            } else if($value != "." && $value != ".." && ($this->getSource() === $dir)) {
                $this->getDirContents($path,$results);
            }
        }

        return $results;
    }

    private function removeSource()
    {
    }

}
