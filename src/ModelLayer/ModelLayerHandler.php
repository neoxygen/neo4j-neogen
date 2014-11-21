<?php

namespace Neoxygen\Neogen\ModelLayer;

use Symfony\Component\Finder\Finder,
    Symfony\Component\Yaml\Yaml;

class ModelLayerHandler
{
    private $models;

    private $finder;

    private $resourcesPath;

    public function __construct($resourcesPath = null)
    {
        $this->models = [];
        $this->finder = new Finder();
        $this->resourcesPath = null !== $resourcesPath ? $resourcesPath : __DIR__.'/../Resources/models';
    }

    public function findModelResources()
    {
        $files = $this->finder->files()->name('*.yml')->in($this->resourcesPath);

        foreach ($files as $file) {
            $definitions = Yaml::parse($file);
            foreach ($definitions as $key => $definition) {
                $this->models[$key] = $definition;
            }
        }
    }

    public function getModels()
    {
        return $this->models;
    }
}
