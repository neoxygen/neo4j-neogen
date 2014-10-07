<?php

namespace Neoxygen\Neogen\Parser;

use Symfony\Component\Yaml\Yaml;

class YamlFile
{
    public function parseSchema($schemaFilePath)
    {
        return Yaml::parse($schemaFilePath);
    }
}