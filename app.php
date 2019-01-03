<?php

require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Oesteve\Command\Remove;

$application = new Application();

$application->add(new Remove());


$application->run();
