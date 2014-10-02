<?php

namespace Neoxygen\Neogen\Console;

use Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Filesystem\Filesystem;
use Neoxygen\Neogen\Schema\Parser,
    Neoxygen\Neogen\Schema\Processor;

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
                InputOption::VALUE_REQUIRED,
                'If the generation queries should be exported to a file rather than loaded in the database?',
                'neogen.export.cql'
            )
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = microtime(true);
        $output->writeln('<info>Locating fixtures file</info>');
        $fixtures_file = getcwd().'/neogen.yml';
        if (!file_exists($fixtures_file)) {
            $output->writeln('<error>No fixtures file found</error>');
        } else {
            $parser = new Parser();
            $processor = new Processor();
            $schema = $parser->parseSchema($fixtures_file);

            $client = new \Neoxygen\NeoClient\Client();
            $client->addConnection('default', $schema['connection']['scheme'], $schema['connection']['host'], $schema['connection']['port']);
            $client->build();

            $processor->process($schema);

            $constraints = $processor->getConstraints();
            $queries = $processor->getQueries();

            if ($exportFile = $input->getOption('export')) {
                $exportFilePath = getcwd().'/'.$exportFile;
                $fs = new Filesystem();
                if ($fs->exists($exportFilePath)) {
                    $fs->copy($exportFilePath, $exportFilePath.'.backup');
                }
                $txt = '';
                foreach ($constraints as $constraint) {
                    $txt .= $constraint."\n";
                }
                foreach ($queries as $q) {
                    $txt .= $q."\n";
                }
                $fs->dumpFile($exportFilePath, $txt);
                $output->writeln('<info>Exporting the queries to '.$exportFile.'</info>');
                exit();
            }

            foreach ($constraints as $constraint) {
                $client->sendCypherQuery($constraint);
            }

            $max = 50;
            $i = 1;
            $q = '';
            foreach ($queries as $query) {
                $q .= $query."\n";
                if ($i >= $max) {
                    $i = 0;
                    $response = $client->sendCypherQuery($q);
                    $q = '';
                }
                $i++;
            }
            if ($q !== '') {
                $response = $client->sendCypherQuery($q);
            }
        }

        $end = microtime(true);
        $diff = $end - $start;

        $output->writeln('<info>Graph generation done in '.$diff.' seconds</info>');
    }
}