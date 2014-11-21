<?php

namespace Neoxygen\Neogen\Console;

use Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Filesystem\Filesystem,
    Symfony\Component\Yaml\Yaml;
use Neoxygen\NeoClient\ClientBuilder,
    Neoxygen\NeoClient\Formatter\ResponseFormatter,
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
                InputOption::VALUE_REQUIRED,
                'If the generation queries should be exported to a file rather than loaded in the database?',
                null
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
            $schema = Yaml::parse($fixtures_file);
            $gen = new Neogen();
            $graph = $gen->generateGraphFromFile($fixtures_file);
            $converter = new StandardCypherConverter();
            $converter->convert($graph);

            $statements = $converter->getStatements();

            if ($exportFile = $input->getOption('export')) {
                $exportFilePath = getcwd().'/'.$exportFile;
                $fs = new Filesystem();
                if ($fs->exists($exportFilePath)) {
                    $fs->copy($exportFilePath, $exportFilePath.'.backup');
                }
                $txt = '';
                foreach ($statements as $statement) {
                    $txt .= $statement."\n";
                }
                $fs->dumpFile($exportFilePath, $txt);
                $output->writeln('<info>Exporting the queries to '.$exportFile.'</info>');
                exit();
            }

            $client = ClientBuilder::create()
                ->addConnection('default', $schema['connection']['scheme'], $schema['connection']['host'], $schema['connection']['port'])
                ->setAutoFormatResponse(true)
                ->build();

            try {
                echo 'cool';
                $response = $client->ping();
            } catch (\Neoxygen\NeoClient\Exception\HttpException $e) {
                $output->writeln('<error>Connection Unavailable</error>');
                $output->writeln('<error>'.$e->getMessage().'</error>');
                exit();
            }

            foreach ($statements as $statement) {
                $client->sendCypherQuery($statement);
            }
        }

        $end = microtime(true);
        $diff = $end - $start;

        $output->writeln('<info>Graph generation done in '.$diff.' seconds</info>');
    }
}
