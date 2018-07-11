<?php

namespace BespokeSupport\OSImporter\Command;

use BespokeSupport\Location\Postcode;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\TableGateway;

class DatabaseCommand extends Command
{
    public function configure()
    {
        $this->setName('postcode:database');
        $this->setDescription('CodePoint-Open: Extract Postcode Parts');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $question = $this->getHelper('question');

        $databaseHost = trim($question->ask($input, $output, new Question('<info>Database Host : </info>', '127.0.0.1')));
        $databaseUser = trim($question->ask($input, $output, new Question('<info>Database User : </info>')));
        $databasePass = trim($question->ask($input, $output, new Question('<info>Database Pass : </info>')));
        $databaseName = trim($question->ask($input, $output, new Question('<info>Database Name : </info>')));

        // database connection
        $databaseAdapter = new Adapter([
            'driver' => 'Pdo_Mysql',
            'dbname' => $databaseName,
            'username' => $databaseUser,
            'password' => $databasePass,
        ]);

        $tablePostcodes = new TableGateway('postcodes', $databaseAdapter);


    }
}
