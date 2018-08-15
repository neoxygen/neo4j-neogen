<?php

namespace Neoxygen\Neogen\Processor;

use Faker\Factory;
use Neoxygen\Neogen\Exception\SchemaException,
    Neoxygen\Neogen\Processor\VertEdgeProcessor,
    Neoxygen\Neogen\Graph\Graph;
use Ikwattro\FakerExtra\Provider\Skill,
    Ikwattro\FakerExtra\Provider\PersonExtra,
    Ikwattro\FakerExtra\Provider\Hashtag,
    Ikwattro\FakerExtra\Provider\GeoLocation;

class PropertyProcessor
{

    private $faker;

    private $localizedFakers = [];

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
        $faker->addProvider(new GeoLocation($faker));

        $this->faker = $faker;
    }

    public function process(VertEdgeProcessor $vertEdge, Graph $graph)
    {
        $this->graph = $graph;

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
        foreach ($vertedge['properties'] as $key => $type) {
            $fakerToUse = $this->faker;
            if (strpos($key, '___') !== false) {
                $parts = explode("___", $key);
                $key = $parts[0];
                $fakerToUse = $this->getFaker($parts[1]);
            }
            var_dump($key);
            if (is_array($type)) {
                if ($type['type'] == 'password') {
                    $type['type'] = 'sha1';
                }
                if ($type['type'] == 'randomElement' || $type['type'] == 'randomElements') {
                    $value = call_user_func_array(array($fakerToUse, $type['type']), array($type['params']));
                } else {
                    var_dump($type);
                    $value = call_user_func_array(array($fakerToUse, $type['type']), $type['params']);
                }
                if ($value instanceof \DateTime) {
                    $value = $value->format('Y-m-d H:i:s');
                }
            } else {
                $ntype = $type == 'password' ? 'sha1' : $type;
                $value = $fakerToUse->$ntype;
            }
            $props[$key] = $value;
        }
        $vertedge['properties'] = $props;
        $this->graph->setNode($vertedge);
    }

    public function addEdgeProperties(array $vertedge)
    {
        try {
            $props = [];
            if (isset($vertedge['properties'])) {
                foreach ($vertedge['properties'] as $key => $type) {
                    $fakerToUse = $this->faker;
                    if (strpos($key, '___') !== false) {
                        $parts = explode("___", $key);
                        $key = $parts[0];
                        $fakerToUse = $this->getFaker($parts[1]);
                    }
                    if (is_array($type)) {
                        if ($type['type'] == 'randomElement' || $type['type'] == 'randomElements') {
                            $value = call_user_func_array(array($fakerToUse, $type['type']), array($type['params']));
                        } else {
                            $value = call_user_func_array(array($fakerToUse, $type['type']), $type['params']);
                        }
                    } else {
                        $value = $fakerToUse->$type;
                    }
                    if ($value instanceof \DateTime) {
                        $value = $value->format('Y-m-d H:i:s');
                    }
                    $props[$key] = $value;
                }
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

    private function getFaker($locale)
    {
        if ($locale === null) {
            return $this->faker;
        } else {
            if (!array_key_exists($locale, $this->localizedFakers)) {
                $this->localizedFakers[$locale] = Factory::create($locale);
            }

            return $this->localizedFakers[$locale];
        }
    }
}
