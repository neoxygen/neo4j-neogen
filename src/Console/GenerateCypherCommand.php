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

class GenerateCypherCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('generate-cypher')
            ->setDescription('Generate fixtures based on "neogen.cql" file')
            ->addOption(
                'source',
                null,
                InputOption::VALUE_REQUIRED,
                '/neogen.cql')
            ->addOption(
                'export',
                null,
                InputOption::VALUE_REQUIRED,
                'Export generated cypher statements into file',
                null
            )
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = microtime(true);

        $output->writeln('<info>Locating fixtures file</info>');
        $fixtures_file = getcwd().'/'.$input->getOption('source');
        $fs = new Filesystem();

        if (!$fs->exists($fixtures_file)) {
            throw new \InvalidArgumentException(sprintf('The source file %s does not exist', $fixtures_file));
        }

        if ($input->getOption('export') == null) {
            $output->writeln('<error>The --export option is mandatory</error>');
            throw new \InvalidArgumentException('The --export option is mandatory');
        }

            $gen = new Neogen();
            $graph = $gen->generateGraphFromCypher(file_get_contents($fixtures_file));

            $converter = new StandardCypherConverter();
            $converter->convert($graph);
            $statements = $converter->getStatements();

            $exportFile = $input->getOption('export');
            $exportFilePath = getcwd().'/'.$exportFile;
                if ($fs->exists($exportFilePath)) {
                    $fs->copy($exportFilePath, $exportFilePath.'.backup');
                }
                $txt = '';
                foreach ($statements as $statement) {
                    $txt .= $statement."\n";
                }
                $fs->dumpFile($exportFilePath, $txt);
                $output->writeln('<info>Exporting the queries to '.$exportFile.'</info>');

        $end = microtime(true);
        $diff = $end - $start;
        $output->writeln('<info>Graph generation done in '.$diff.' seconds</info>');
    }

}
