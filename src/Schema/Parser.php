<?php

namespace Neoxygen\Neogen\Schema;

use Symfony\Component\Yaml\Yaml;

class Parser
{

    public function parseSchema($schemaFilePath)
    {
        return Yaml::parse($schemaFilePath);
    }
}