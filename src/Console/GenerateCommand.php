<?php

namespace Neoxygen\Neogen\Console;

use Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Filesystem\Filesystem;
use Neoxygen\NeoClient\ClientBuilder,
    Neoxygen\Neogen\Neogen,
    Neoxygen\Neogen\Converter\StandardCypherConverter;

class GenerateCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('generate')
            ->setDescription('Generate fixtures based on "neogen.yml" file')
            ->addOption(
                'export',
                null,
                InputOption::VALUE_OPTIONAL,
                'If the generation queries should be exported to a file rather than loaded in the database?',
                null
            )
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $neogen = Neogen::create()
            ->build();

        $yamlParser = $neogen->getParserManager()->getParser('yaml');
        $filePath = getcwd() . '/neogen.yml';
        $schema = $yamlParser->parse($filePath);
        $g = $neogen->generateGraph($schema);
        return $g;

    }
}
