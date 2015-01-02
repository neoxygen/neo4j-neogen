<?php

namespace Neoxygen\Neogen\Processor;

use Faker\Factory;
use Neoxygen\Neogen\Exception\SchemaDefinitionException as SchemaException,
    Neoxygen\Neogen\Processor\VertEdgeProcessor,
    Neoxygen\Neogen\Graph\Graph;
use Ikwattro\FakerExtra\Provider\Skill,
    Ikwattro\FakerExtra\Provider\PersonExtra,
    Ikwattro\FakerExtra\Provider\Hashtag;

class PropertyProcessor
{

    private $faker;

    private $graph;

    public function __construct($seed = null)
    {
        $faker = Factory::create();
        if (null !== $seed) {
            $faker->seed((int) $seed);
        }
        $faker->addProvider(new Skill($faker));
        $faker->addProvider(new PersonExtra($faker));
        $faker->addProvider(new Hashtag($faker));

        $this->faker = $faker;
    }

    public function process(VertEdgeProcessor $vertEdge)
    {
        $this->graph = new Graph();

        foreach ($vertEdge->getNodes() as $node) {
            $this->addNodeProperties($node);
        }

        foreach ($vertEdge->getEdges() as $edge) {
            $this->addEdgeProperties($edge);
        }

        return $this->graph;
    }

    public function addNodeProperties(array $vertedge)
    {
        $props = [];
        foreach ($vertedge['properties'] as $key => $property) {
            $type = $property->getProvider();
            if ($type == 'password') { $type = 'sha1'; }
            if ($type == 'randomElement' || $type == 'randomElements') {
                $value = call_user_func_array(array($this->faker, $type), array($property->getArguments()));
            } else {
                $value = call_user_func_array(array($this->faker, $type), $property->getArguments());
            }
            if ($value instanceof \DateTime) {
                $value = $value->format('Y-m-d H:i:s');
            }
            $props[$property->getName()] = $value;
        }
        $vertedge['properties'] = $props;
        $this->graph->setNode($vertedge);
    }

    public function addEdgeProperties(array $vertedge)
    {
        try {
            $props = [];
            foreach ($vertedge['properties'] as $key => $property) {
                $type = $property->getProvider();
                if ($type == 'password') { $type = 'sha1'; }
                if ($type == 'randomElement' || $type == 'randomElements') {
                    $value = call_user_func_array(array($this->faker, $type), array($property->getArguments()));
                } else {
                    $value = call_user_func_array(array($this->faker, $type), $property->getArguments());
                }
                if ($value instanceof \DateTime) {
                    $value = $value->format('Y-m-d H:i:s');
                }
                $props[$property->getName()] = $value;
            }
            $vertedge['properties'] = $props;
            $this->graph->setEdge($vertedge);
        } catch (\InvalidArgumentException $e) {
            $msg = $e->getMessage();
            preg_match('/((?:")(.*)(?:"))/', $msg, $output);
            if (isset($output[2])) {
                $msg = sprintf('The faker type "%s" is not defined', $output[2]);
            }
            throw new SchemaException($msg);
        }

    }
}
