<?php

namespace BespokeSupport\OSImporter\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\TableGateway;

class CpoImportGlobCommand extends Command
{
    public function configure()
    {
        $this->setName('postcode:cpo:import');
        $this->setDescription('CodePoint-Open: Import CSV');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Starting Import CSV</info>');

        $dir = dirname(__FILE__).'/../Data/CSV/';

        $files = glob($dir.'*.csv');

        $databaseAdapter = new Adapter([
            'driver' => 'Pdo_Mysql',
            'dbname' => 'locations',
            'username' => 'root'
        ]);

        $table = new TableGateway('postcodes', $databaseAdapter);

        foreach ($files as $file) {
            $path = pathinfo($file);
            $output->writeln("<info>{$path['basename']}</info>");

            $spl = new \SplFileObject($file, 'r');

            $count = 0;
            $databaseAdapter->getDriver()->getConnection()->beginTransaction();
            while (($row = $spl->fgetcsv())) {

                if (!count($row) || !$row[0]) continue;

                $table->insert([
                    'postcode' => str_replace(' ', '', $row[0]),
                    'eastings' => $row[2],
                    'northings' => $row[3]
                ]);

                if ($count % 100 == 0) {
                    $output->writeln("\t $count");
                    $databaseAdapter->getDriver()->getConnection()->commit();
                    $databaseAdapter->getDriver()->getConnection()->beginTransaction();
                }

                $count++;
            }
            $databaseAdapter->getDriver()->getConnection()->commit();
        }

        $output->writeln('<info>Import CSV End</info>');
    }
}
