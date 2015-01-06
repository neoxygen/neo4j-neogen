<?php

namespace spec\Neoxygen\Neogen\Processor;

use Neoxygen\Neogen\Schema\Relationship;
use Neoxygen\Neogen\Util\ObjectCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Neoxygen\Neogen\FakerProvider\Faker;

class GraphProcessorSpec extends ObjectBehavior
{
    function let()
    {
        $faker = new Faker();
        $this->beConstructedWith($faker);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Neoxygen\Neogen\Processor\GraphProcessor');
    }

    function it_should_define_percentage_of_target_nodes(ObjectCollection $collection, Relationship $relationship)
    {
        $collection->count()->willReturn(100);
        $relationship->hasPercentage()->willReturn(false);
        $relationship->getEndNode()->willReturn('person');
        $this->getTargetNodesCount($relationship, $collection)->shouldBe(60);

        $collection->count()->willReturn(1);
        $relationship->hasPercentage()->willReturn(false);
        $relationship->getEndNode()->willReturn('person');
        $this->getTargetNodesCount($relationship, $collection)->shouldBe(1);

        $collection->count()->willReturn(1000);
        $relationship->hasPercentage()->willReturn(false);
        $this->getTargetNodesCount($relationship, $collection)->shouldBe(200);

        $collection->count()->willReturn(100);
        $relationship->hasPercentage()->willReturn(true);
        $relationship->getPercentage()->willReturn(65);
        $this->getTargetNodesCount($relationship, $collection)->shouldBe(65);

        $collection->count()->willReturn(1000);
        $relationship->hasPercentage()->willReturn(true);
        $relationship->getPercentage()->willReturn(45);
        $this->getTargetNodesCount($relationship, $collection)->shouldBe(450);
    }

    function it_should_calculate_the_approx_end_connected_nodes_depending_on_counts()
    {
        $this->calculateApproxTargetNodes(1,1)->shouldReturn(1);
        $this->calculateApproxTargetNodes(10,11)->shouldReturn(1);
        $this->calculateApproxTargetNodes(1,5)->shouldReturn(5);
        $this->calculateApproxTargetNodes(2,5)->shouldReturn(2);
        $this->calculateApproxTargetNodes(3,6)->shouldReturn(2);
        $this->calculateApproxTargetNodes(6,3)->shouldReturn(1);
        $this->calculateApproxTargetNodes(30,11)->shouldReturn(1);
        $this->calculateApproxTargetNodes(30,10)->shouldReturn(1);
    }
}
