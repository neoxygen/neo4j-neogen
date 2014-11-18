<?php

namespace Neoxygen\Neogen\Console;

use Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Yaml\Dumper;

class InitCypherCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('init-cypher')
            ->setDescription('Generate a sample "neogen.cql" file')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $defaultQuery = "// Example : \n" . 
            "(person:Person {firstname: firstName, lastname: lastName } *20)-[:KNOWS *n..n]->(person) \n" . 
            "(person)-[:HAS *n..n]->(skill:Skill {name: progLanguage} *15) \n" . 
            "(company:Company {name: company, desc: catchPhrase} *10)-[:LOOKS_FOR_COMPETENCE *n..n]->(skill) \n" . 
            "(company)-[:LOCATED_IN *n..1]->(country:Country {name: country} *25) \n" . 
            "(person)-[:LIVES_IN *n..1]->(country) \n";

        $initFile = getcwd().'/neogen.cql';
        if (!file_exists($initFile)) {
            file_put_contents($initFile, $defaultQuery);
            $output->writeln('<info>Graph Schema file created in "'.$initFile.'" modify it for your needs.</info>');
        } else {
            $output->writeln('<error>The file "neogen.cql" already exist, delete it or modify it</error>');
        }
    }
}