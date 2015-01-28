<?php

namespace Neoxygen\Neogen\Console;

use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Neoxygen\NeoClient\Client;
use Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Filesystem\Filesystem,
    Symfony\Component\Yaml\Yaml;
use Neoxygen\NeoClient\ClientBuilder,
    Neoxygen\NeoClient\Exception\HttpException,
    Neoxygen\Neogen\Neogen,
    Neoxygen\Neogen\Converter\StandardCypherConverter,
    Neoxygen\Neogen\Converter\CypherStatementsConverter;

class GenerateCypherCommand extends Command
{
    protected $fs;

    protected $source;

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->fs = new Filesystem();
    }
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
                InputOption::VALUE_OPTIONAL,
                'Export generated cypher statements into file',
                null
            )
            ->addOption(
                'export-db',
                null,
                InputOption::VALUE_OPTIONAL,
                'Database connection settings',
                null)
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = microtime(true);

        $output->writeln('<info>Locating fixtures file</info>');
        $fixtures_file = getcwd().'/'.$input->getOption('source');

        if (!$this->fs->exists($fixtures_file)) {
            throw new \InvalidArgumentException(sprintf('The source file %s does not exist', $fixtures_file));
        }

        $this->source = file_get_contents($fixtures_file);

        if ($input->getOption('export') == null && $input->getOption('export-db') == null) {
            $output->writeln('<error>The --export or --export-db option is mandatory</error>');
            throw new \InvalidArgumentException('The --export or --export-db option is mandatory');
        }

        if ($input->getOption('export') != null && $input->getOption('export-db') != null) {
            $output->writeln('<error>You can only use one of "export" OR "export-db"');
            throw new \InvalidArgumentException('There can be only one of export or export-db');
        }

        if ($input->getOption('export') != null) {
            $exportFile = $input->getOption('export');
            $this->exportToFile($exportFile, $this->generateGraph(), $output);
        } elseif ($input->getOption('export-db') != null) {
            $output->writeln('<info>Starting the Import in the database');
            $client = $this->getDBConnection($input->getOption('export-db'));
            $this->exportToDB($this->generateGraph(), $output, $client);
        }

        $end = microtime(true);
        $diff = $end - $start;
        $output->writeln('<info>Graph generation done in '.$diff.' seconds</info>');
    }

    private function generateGraph()
    {
        $gen = new Neogen();
        $graph = $gen->generateGraphFromCypher($this->source);

        return $graph;
    }

    private function exportToFile($file, $graph, OutputInterface $output)
    {
        $converter = new StandardCypherConverter();
        $converter->convert($graph);
        $statements = $converter->getStatements();
        $txt = '';
        foreach ($statements as $statement) {
            $txt .= $statement."\n";
        }
        $exportFilePath = getcwd().'/'.$file;
        if ($this->fs->exists($exportFilePath)) {
            $this->fs->copy($exportFilePath, $exportFilePath.'.backup');
        }
        $this->fs->dumpFile($exportFilePath, $txt);
        $output->writeln('<info>Exporting the queries to '.$exportFilePath.'</info>');
    }

    private function exportToDB($graph, OutputInterface $output, Client $client)
    {
        $converter = new CypherStatementsConverter();
        $converter->convert($graph);

        try {
            $constraints = 0;
            foreach ($converter->getConstraintStatements() as $statement) {
                $client->sendCypherQuery($statement['statement']);
                $constraints++;
            }
            $output->writeln('<info>Created '.$constraints.' constraint(s)</info>');
            $nodes = 0;
            foreach ($converter->getNodeStatements() as $ns) {
                $client->sendCypherQuery($ns['statement'], $ns['parameters']);
                $nodes += count($ns['parameters']['props']);
            }
            $output->writeln('<info>Created '.$nodes.' node(s)</info>');
            $edges = 0;
            foreach ($converter->getEdgeStatements() as $es) {
                $chunks = array_chunk($es['parameters']['pairs'], 50000);
                foreach ($chunks as $chunk) {
                    $client->sendCypherQuery($es['statement'], ['pairs' => $chunk]);
                    $edges += count($chunk);
                    echo 'Total relationships created : '.$edges.PHP_EOL;
                }
            }
            $output->writeln('<info>Created '.$edges.' relationship(s)</info>');
        } catch (HttpException $e) {
            $output->writeln('<error>Unable to connect to the database'.PHP_EOL.$e->getMessage().PHP_EOL.'</error>');
        }
    }

    private function getDBConnection($dbConnection)
    {
        $defaults = [
            'scheme' => 'http',
            'host' => 'localhost',
            'port' => 7474,
            'user' => null,
            'pass' => null
        ];
        $conn = array_merge($defaults, parse_url($dbConnection));
        if ($conn['user'] === null && $conn['pass'] === null) {
            $auth = false;
        } else {
            $auth = true;
        }
        if (!array_key_exists('scheme', $conn) || !array_key_exists('host', $conn) || !array_key_exists('port', $conn)) {
            throw new InvalidArgumentException('The connection settings does not seem to be correct');
        }
        var_dump($auth);

        $client = ClientBuilder::create()
            ->addConnection('default', $conn['scheme'], $conn['host'], $conn['port'], $auth, $conn['user'], $conn['pass'])
            ->setDefaultTimeout(10000000)
            ->build();

        return $client;
    }

}
