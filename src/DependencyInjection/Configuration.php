<?php

/**
 * This file is part of the "-[:NEOGEN]->" NeoClient package
 *
 * (c) Neoxygen.io <http://neoxygen.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Neoxygen\Neogen\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration implements ConfigurationInterface
{

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('neogen');

        $rootNode->children()
            ->scalarNode('test')->defaultValue('cool')->end()
            ->arrayNode('faker')
            ->addDefaultsIfNotSet()
                ->children()
                ->scalarNode('class')->defaultValue('Neoxygen\Neogen\FakerProvider\Faker')->end()
                ->arrayNode('providers')
                    ->prototype('array')
                    ->children()->end()
                    ->end()
                ->end() // end providers
                ->arrayNode('extensions')
                    ->prototype('array')->end()
            ->end()
            ->end() // end faker
            ->end();

        return $treeBuilder;
    }
}
