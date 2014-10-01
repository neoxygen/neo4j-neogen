<?php

namespace Neoxygen\Neogen\Console;

use Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Yaml\Yaml;

class InitCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('init')
            ->setDescription('Generate fixtures based on "neogen.yml" file')
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
            'nodes' => [],
            'relationships' => []
        ];

        $initFile = getcwd().'/neogen.yml';
        if (!file_exists($initFile)) {
            Yaml::dump($defaultSchema, $initFile);
        }
        $output->writeln('<info>Graph Schema file created in "'.$initFile.'" modify it for your needs.</info>');
    }
}