<?php

namespace Neoxygen\Neogen\Console;

use Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Yaml\Dumper;

class InitCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('init')
            ->setDescription('Generate a sample "neogen.yml" file')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $defaultSchema = [
            'connection' => [
                'scheme' => 'http',
                'host' => 'localhost',
                'port' => 7474
            ],
            'nodes' => null,
            'relationships' => null
        ];

        $initFile = getcwd().'/neogen.yml';
        if (!file_exists($initFile)) {
            $dumper = new Dumper();
            $yaml = $dumper->dump($defaultSchema, 2);
            file_put_contents($initFile, $yaml);
            //Yaml::dump($defaultSchema, $initFile);
            $output->writeln('<info>Graph Schema file created in "'.$initFile.'" modify it for your needs.</info>');
        } else {
            $output->writeln('<error>The file "neoxygen.yml" already exist, delete it or modify it</error>');
        }

    }
}
