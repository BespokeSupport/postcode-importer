<?php

namespace BespokeSupport\OSImporter\Command;

use Buzz\Browser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CpoDownloadCommand extends Command
{
    public function configure()
    {
        $this->setName('postcode:cpo:download');
        $this->setDescription('CodePoint-Open: Download ZIP File from URL');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Starting OS ZIP Download</info>');

        $dialog = $this->getHelper('dialog');
        $url = trim($dialog->ask($output, '<info>Please enter the URL send by OS: </info>'));

        if (!$url || !($parsedUrl = parse_url($url)) || !isset($parsedUrl['path'])) {
            $output->writeln("<error>URL not given</error>");
            exit;
        }

//        $url = 'http://download.ordnancesurvey.co.uk/open/CODEPO/201502/CSV/codepo_gb.zip?sr=b&st=2015-02-18T22:07:24Z&se=2015-02-21T22:07:24Z&si=opendata_policy&sig=IS32Kyd05IFCSI8pFV4k6IT4r7g%2FFz7NeVmxkUDSOqI%3D';

        $fileName = pathinfo($parsedUrl['path']);

        $client = new Browser();

        $response = $client->get($url);

        file_put_contents($fileName['basename'], $response->getContent());

        $output->writeln('<info>Ending OS ZIP Download</info>');
    }
}
