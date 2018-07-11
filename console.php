#!/usr/bin/env php
<?php

require_once('config.php');
require_once('vendor/autoload.php');

use Symfony\Component\Console\Application;

$console = new Application();

$console->add(new \BespokeSupport\OSImporter\Command\CpoDownloadCommand());
$console->add(new \BespokeSupport\OSImporter\Command\CpoPrepareCommand());
$console->add(new \BespokeSupport\OSImporter\Command\CpoImportGlobCommand());
$console->add(new \BespokeSupport\OSImporter\Command\CpoConvertCommand());
$console->add(new \BespokeSupport\OSImporter\Command\CpoOutwardAreaCommand());
$console->add(new \BespokeSupport\OSImporter\Command\OnsPrepareCommand());
$console->add(new \BespokeSupport\OSImporter\Command\ConvertFreeMapToolsCommand());

$console->run();
