<?php

require __DIR__.'/vendor/autoload.php';

use Oesteve\Command\Upload;
use Symfony\Component\Console\Application;
use Oesteve\Command\Remove;

$application = new Application("azure-files", "0.0.1");

$application->add(new Remove());
$application->add(new Upload());


$application->run();
