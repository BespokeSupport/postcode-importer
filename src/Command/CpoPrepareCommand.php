<?php

namespace BespokeSupport\OSImporter\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CpoPrepareCommand extends Command
{
    const FILE = 'codepo_gb.zip';

    public function configure()
    {
        $this->setName('postcode:cpo:prepare');
        $this->setDescription('CodePoint-Open: Prepare ZIP File');
        $this->addArgument('file', InputArgument::OPTIONAL,'File name', self::FILE);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Starting OS ZIP Extract</info>');

        $fileName = ($input->getArgument('file'))?:self::FILE;

        if (!file_exists($fileName)) {
            $output->writeln("<error>File \"{$fileName}\" does not exist</error>");
            exit;
        }

        $zip = new \ZipArchive();

        $zip->open($fileName);

        $zip->extractTo('.');

        $output->writeln('<info>Ending OS ZIP Extract</info>');
    }
}
