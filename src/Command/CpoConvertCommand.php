<?php

namespace BespokeSupport\OSImporter\Command;

use PHPCoord\OSRef;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\TableGateway;

class CpoConvertCommand extends Command
{
    public function configure()
    {
        $this->setName('postcode:convert');
        $this->setDescription('CodePoint-Open: Convert');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Starting Convert</info>');

        $databaseAdapter = new Adapter([
            'driver' => 'Pdo_Mysql',
            'dbname' => 'information',
            'username' => 'root'
        ]);

        $table = new TableGateway('postcodes', $databaseAdapter);

        $rows = $table->select();

        $count = 1;
        $databaseAdapter->getDriver()->getConnection()->beginTransaction();
        foreach ($rows as $row) {

            $gb = new OSRef($row->eastings, $row->northings);
            $latLng = $gb->toLatLng();
            $latLng->OSGB36ToWGS84();


            if ($latLng && $latLng->lat) {
                $table->update([
                    'latitude' => $latLng->lat,
                    'longitude' => $latLng->lng
                ],[
                    'postcode' => $row->postcode
                ]);
            }

            if ($count % 5000 == 0) {
                $output->writeln("\t $count");
                $databaseAdapter->getDriver()->getConnection()->commit();
                $databaseAdapter->getDriver()->getConnection()->beginTransaction();
            }

            $count++;
        }

        $databaseAdapter->getDriver()->getConnection()->commit();

        $output->writeln('<info>Convert End</info>');
    }
}
