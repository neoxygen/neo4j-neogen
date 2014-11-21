<?php

namespace Neoxygen\Neogen\Parser;

use Symfony\Component\Yaml\Yaml;
use Neoxygen\Neogen\Schema\GraphSchemaDefinition;

class YamlFile
{
    public function parseSchema($schemaFilePath)
    {
        $schema = Yaml::parse($schemaFilePath);
        $def = new GraphSchemaDefinition();
        foreach ($schema['nodes'] as $key => $node) {
          $node['labels'] = [$node['label']];
          $node['identifier'] = $key;
          if (!isset($node['models'])) {
            $node['models'] = [];
          }
          $schema['nodes'][$key] = $node;
        }
        $def->setNodes($schema['nodes']);
        $def->setEdges($schema['relationships']);

        return $def;
    }
}
