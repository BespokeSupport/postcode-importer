<?php

namespace BespokeSupport\OSImporter\Command;

use BespokeSupport\Location\Postcode;
use Buzz\Browser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\TableGateway;

class OnsPrepareCommand extends Command
{
    public function configure()
    {
        $this->setName('postcode:ons:prepare');
        $this->setDescription('ONS: Download ZIP File from URL');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $onsFile = POSTCODE_TMP_DIR.'/'.POSTCODE_ONS_FILE;

        if (!file_exists($onsFile) || POSTCODE_ONS_FILE_SHA1 != sha1_file($onsFile)) {
            $output->writeln('<info>Starting ONS ZIP Download</info>');
            $client = new Browser();
            $response = $client->get(POSTCODE_ONS_URL.POSTCODE_ONS_FILE);
            file_put_contents(POSTCODE_TMP_DIR.'/'.POSTCODE_ONS_FILE, $response->getContent());
            $output->writeln('<info>Ending ONS ZIP Download</info>');
        }

        if (!file_exists(str_replace('_multi_csv.zip', '_UK_metadata.xml',$onsFile))){
            $output->writeln("<info>Extracting $onsFile</info>");
            $zip = new \ZipArchive();
            $zip->open($onsFile);
            $zip->extractTo('.');
        }

        // database connection
        $databaseAdapter = new Adapter([
            'driver' => 'Pdo_Mysql',
            'dbname' => 'information',
            'username' => 'root'
        ]);
        $tablePostcodes = new TableGateway('postcodes', $databaseAdapter);
        $tablePostcodeAreas = new TableGateway('postcode_areas', $databaseAdapter);
        $tablePostcodeOutwards = new TableGateway('postcode_outwards', $databaseAdapter);
        $connection = $databaseAdapter->getDriver()->getConnection();

        // cached external
        $cachedPostcodeAreas = array();
        $cachedPostcodeOutwards = array();

        // TODO: All or just BT
        $files = glob(POSTCODE_TMP_DIR.'/Data/'.POSTCODE_ONS_PREFIX.'*');
//        $files  = array(POSTCODE_TMP_DIR.'/Data/'.POSTCODE_ONS_PREFIX.'_UK_BT.csv');

        foreach ($files as $file) {

            $connection->beginTransaction();

            $output->writeln("<info>$file</info>");

            $fileHandler = @fopen($file, 'r');

            if (!$fileHandler) {
                $output->writeln("<error>Could not open $onsFile</error>");
                continue;
            }

            fgetcsv($fileHandler);
            $count = 1;
            $error = 0;

            while (($row = fgetcsv($fileHandler))) {

                if (!$row || !$row[0]) continue;

                if (!$row[9] || !$row[10]) {
                    $error++;
                    continue;
                }

                $postcode = new Postcode($row[0]);

                if (!$postcode->getPostcode()) {
                    $output->writeln("<error>{$row[0]} invalid</error>");
                    continue;
                }

                if (!in_array($postcode->getPostcodeArea(), $cachedPostcodeAreas)) {
//                    $tablePostcodeAreas->insert(array(
//                        'postcode_area' => $postcode->getPostcodeArea(),
//                    ));
                    $cachedPostcodeAreas[] = $postcode->getPostcodeArea();
                }

                if (!in_array($postcode->getPostcodeOutward(), $cachedPostcodeOutwards)) {
                    $outwardPart = str_replace($postcode->getPostcodeOutward(), $postcode->getPostcodeArea(),'');
//                    $tablePostcodeOutwards->insert(array(
//                        'postcode_area' => $postcode->getPostcodeArea(),
//                        'postcode_outward' => $postcode->getPostcodeOutward(),
//                        'outward_part' => $outwardPart
//                    ));
                    $cachedPostcodeOutwards[] = $postcode->getPostcodeOutward();
                }

                $tablePostcodes->insert(array(
                    'postcode' => $postcode->getPostcode(),
//                'postcode_area' => $postcode->getPostcodeArea(),
//                'postcode_outward' => $postcode->getPostcodeOutward(),
                    'eastings' => $row[9],
                    'northings' => $row[10]
                ));

                if ($count % 5000 == 0) {
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

            $output->writeln("<error>$error errors</error>");
        }
    }
}
