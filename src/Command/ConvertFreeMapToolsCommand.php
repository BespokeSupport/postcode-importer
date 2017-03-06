<?php

namespace BespokeSupport\OSImporter\Command;

use BespokeSupport\Location\Postcode;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\TableGateway;

class ConvertFreeMapToolsCommand extends Command
{
    const TABLE_OUTWARD = 'outcodepostcodes';
    const TABLE_POSTCODE = 'postcodelatlng';
    /**
     * @var Adapter
     */
    protected $databaseAdapter;
    /**
     * @var TableGateway
     */
    protected $tableSourceOut;
    /**
     * @var TableGateway
     */
    protected $tableSourcePostcode;

    public function configure()
    {
        $this->setName('postcode:convert:free');
        $this->setDescription('FreeMapTools.com: Convert');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Starting Convert</info>');

        $question = $this->getHelper('question');

        $databaseUser = trim($question->ask($input, $output, new Question('<info>Database User : </info>')));
        $databasePass = trim($question->ask($input, $output, new Question('<info>Database Pass : </info>')));
        $databaseName = trim($question->ask($input, $output, new Question('<info>Database Name : </info>')));

        $this->databaseAdapter = new Adapter([
            'driver' => 'Pdo_Mysql',
            'dbname' => $databaseName,
            'username' => $databaseUser,
            'password' => $databasePass,
        ]);

        $this->tableSourceOut = new TableGateway(self::TABLE_OUTWARD, $this->databaseAdapter);
        $this->tableSourcePostcode = new TableGateway(self::TABLE_POSTCODE, $this->databaseAdapter);

        $output->writeln('<info>Copying Postcode DB : This query takes over 1 minute</info>');
        $copySql = <<<SQL
INSERT INTO postcodes (postcode,latitude,longitude)
SELECT 
replace(postcode, ' ', '') as postcode,
latitude,
longitude
FROM postcodelatlng
SQL;
        $this->databaseAdapter->query($copySql);

        $output->writeln('<info>Extracting Outward Element of Postcode : This query takes over 3 minutes</info>');
        $updateOutSql = <<<SQL
UPDATE postcodes SET postcode_outward = SUBSTRING(postcode, 1, CHAR_LENGTH(postcode) - 3)
SQL;
        $this->databaseAdapter->query($updateOutSql);

        $output->writeln('<info>Extracting Area Element of Postcode : This query takes over 3 minutes</info>');
        $updateAreaSql = <<<SQL
UPDATE postcodes p INNER JOIN postcode_outwards o ON o.postcode_outward = p.postcode_outward SET p.postcode_area = o.postcode_area
SQL;
        $this->databaseAdapter->query($updateAreaSql);
    }

    protected function copyOutward()
    {
        $tableArea = new TableGateway('postcode_areas', $this->databaseAdapter);
        $tableOut = new TableGateway('postcode_outwards', $this->databaseAdapter);

        $allOuts = $this->tableSourceOut->select()->toArray();
        $areas = [];
        foreach ($allOuts as $out) {
            $postcode = new Postcode($out['outcode']);

            if (!in_array($postcode->getPostcodeArea(), $areas)) {
                $areas[] = $postcode->getPostcodeArea();
                $tableArea->insert([
                    'postcode_area' => $postcode->getPostcodeArea()
                ]);
            }

            $sortingPart = str_replace($postcode->getPostcodeArea(), '', $out['outcode']);

            $tableOut->insert([
                'postcode_outward' => $postcode->getPostcodeOutward(),
                'postcode_area' => $postcode->getPostcodeArea(),
                'outward_part' => $sortingPart,
                'latitude' => $out['lat'],
                'longitude' => $out['lng'],
            ]);
        }
    }
}
