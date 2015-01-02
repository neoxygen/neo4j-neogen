<?php

namespace spec\Neoxygen\Neogen\Schema;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class GraphSchemaBuilderSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Neoxygen\Neogen\Schema\GraphSchemaBuilder');
    }

    function it_should_create_node_elements()
    {
        $this->buildNode('person', $this->getNodeArray())->shouldHaveType('Neoxygen\Neogen\Schema\Node');
    }

    function it_should_create_property_elements()
    {
        $this->buildNodeProperty('first_name', $this->getNodeArray()['properties']['first_name'])->shouldBeAnInstanceOf('Neoxygen\Neogen\Schema\Property');
    }

    function it_should_create_relationships()
    {
        $this->buildRelationship($this->getRelArray())->shouldHaveType('Neoxygen\Neogen\Schema\Relationship');
    }

    private function getNodeArray()
    {
        return array(
            'labels' => 'Person',
            'count' => 10,
            'properties' => array(
                'first_name' => 'firstName',
                'last_name' => 'lastName',
                'birth_date' => array(
                    'type' => 'dateTimeBetween',
                    'params' => array(
                        '-65 years',
                        '-18 years'
                    )
                )
            )
        );
    }

    private function getRelArray()
    {
        return array(
            'type' => 'RELATES_TO',
            'start' => 'person',
            'end' => 'company',
            'mode' => 'n..1',
            'properties' => array(
                'since' => 'dateTime'
            )
        );
    }


}
