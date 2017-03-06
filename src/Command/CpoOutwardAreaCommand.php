<?php

namespace BespokeSupport\OSImporter\Command;

use BespokeSupport\Location\Postcode;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\TableGateway;

class CpoOutwardAreaCommand extends Command
{
    public function configure()
    {
        $this->setName('postcode:cpo:parts');
        $this->setDescription('CodePoint-Open: Extract Postcode Parts');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Starting Postcode Parts</info>');

        $question = $this->getHelper('question');

        $databaseUser = trim($question->ask($input, $output, new Question('<info>Database User : </info>')));
        $databasePass = trim($question->ask($input, $output, new Question('<info>Database Pass : </info>')));
        $databaseName = trim($question->ask($input, $output, new Question('<info>Database Name : </info>')));

        $databaseAdapter = new Adapter([
            'driver' => 'Pdo_Mysql',
            'dbname' => $databaseName,
            'username' => $databaseUser,
            'password' => $databasePass,
        ]);

        $table = new TableGateway('postcodes', $databaseAdapter);
        $tableOutward = new TableGateway('postcode_outward', $databaseAdapter);

        $rows = $table->select();

        $connection = $databaseAdapter->getDriver()->getConnection();

        $count = 0;
        $connection->beginTransaction();
        foreach ($rows as $row) {

            $postcodeObject = new Postcode($row->postcode);

            $outward = $postcodeObject->getPostcodeOutward();
            $area = $postcodeObject->getPostcodeArea();

            $outwardRow = $tableOutward->select(['postcode_outward' => $outward])->current();

            if (!$outwardRow) {
                $output->writeln("<info>New Outward: $outward</info>");
                $tableOutward->insert(
                    [
                        'postcode_outward' => $outward,
                        'postcode_area' => $area,
                        'outward_part' => str_replace($area, '', $outward),
                        'updated' => 1
                    ]
                );
                $connection->commit();
                $connection->beginTransaction();
            }

            $table->update(
                [
                    'postcode_area' => $postcodeObject->getPostcodeArea(),
                    'postcode_outward' => $postcodeObject->getPostcodeOutward()
                ],
                [
                    'postcode' => $postcodeObject->getPostcode()
                ]
            );

            if ($count % 1000 == 0) {
                $output->writeln("\t $count");
                try {
                    $connection->commit();
                    $connection->beginTransaction();
                } catch (\Exception $e) {
                    var_dump($e->getMessage());
                    throw new \Exception();
                }

            }

            $count++;
        }

        $connection->commit();

        $output->writeln('<info>Ending Postcode Parts</info>');
    }
}
