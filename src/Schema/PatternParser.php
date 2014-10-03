<?php

namespace Neoxygen\Neogen\Schema;

/**
 *
 * Parses a Cypher Pattern and tries to guess the schema
 *
 * Basic schema line :
 *
 * (:Person *30*)-[*n..n*:WORKS_FOR]->(:Company)<-[:BOOKED_PROJECT]-(:Customer)
 *
 * Class PatternParser
 * @package Neoxygen\Neogen\Schema
 *
 *
 */

use Neoxygen\Neogen\Schema\Processor;

class PatternParser
{
    const INGOING_RELATIONSHIP = 'IN';

    const OUTGOING_RELATIONSHIP = 'OUT';

    const DEFAULT_RELATIONSHIP_DIRECTION = 'OUT';


    private $nodes = [];

    private $relationships = [];

    private $processor;

    public function __construct()
    {
        $this->processor = new Processor();
    }

    public function process($text)
    {
        $lines = explode("\n", $text);
        foreach ($lines as $line) {
            $this->parse($line);
        }
    }


    public function parse($pattern = null)
    {
        if (null === $pattern) {
            //$cypherPattern = '(:Person *25*)-[:KNOWS*n..n*]->(:Person)-[:WROTE*1..n*]->(:Post *100*)<-[:COMMENTED*n..n*]-(:Person)';
            $cypherPattern = '(:Kid)';
        } else {
            $cypherPattern = $pattern;
        }


        $nodePattern = '/(\\([-:,*.{}\\w\\s]*\\))/';
        $relPattern = '/((-<?>?)(\\[[:*.\\s\\w]+\\])(-<?>?))/';

            $split = preg_split($nodePattern, $cypherPattern, null, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
            foreach ($split as $key => $part) {
                if (0 === $key && !preg_match($nodePattern, $part)) {
                    throw new \InvalidArgumentException(sprintf('The pattern must start with a node part, "%s" given', $part));
                }

                if (preg_match($nodePattern, $part)) {
                    $this->processNodePart($part, $key);
                } elseif (preg_match($relPattern, $part)) {
                    $this->processRelationshipPart($key, $split);
                } else {
                    throw new \InvalidArgumentException(sprintf('Unable to parse part "%s"', $part));
                }
            }
    }

    public function processNodePart($nodePart, $key)
    {
        $labelPattern = '/(:([\\w]+))+/';
        $nodeCountPattern = '/(\\*[\\d]+\\*)/';

        // Labels matching
        preg_match($labelPattern, $nodePart, $output);
        $expl = explode(':', $output[0]);
        $labels = [];
        foreach ($expl as $label) {
            if (!empty($label)) {
                $labels[] = $label;
            }
        }

        if (isset($this->nodes[$labels[0]])) {
            return;
        }

        preg_match($nodeCountPattern, $nodePart, $countOutput);
        if (empty($countOutput)) {
            $count = 10;
        } else {
            $count = (int) str_replace('*', '', $countOutput[0]);
        }

        $node = [
            'label' => $labels[0],
            'count' => $count
        ];

        $this->nodes[$node['label']] = $node;

    }

    public function processRelationshipPart($schemaKey, $schema)
    {
        $outgoingPattern = '/^-/';
        $ingoingPattern = '/^<-/';
        $relTypePattern = '/(:[\\w]+)/';
        $cardinalityPattern = '/(\\*([\\d]+|n)\\.\\.([\\d]+|n)\\*)/';
        $relString = $schema[$schemaKey];

        if (preg_match($outgoingPattern, $schema[$schemaKey])) {
            $direction = self::OUTGOING_RELATIONSHIP;
        } elseif (preg_match($ingoingPattern, $schema[$schemaKey])) {
            $direction = self::INGOING_RELATIONSHIP;
        } else {
            $direction = self::DEFAULT_RELATIONSHIP_DIRECTION;
        }

        $stripPattern = '/[():]+/';

        switch ($direction) {
            case "OUT":
                $startl = $schema[$schemaKey-1];
                $ex = explode ('*', $startl);
                $start = trim($ex[0]);
                $startNodeLabel = preg_replace($stripPattern, '', $start);
                $endl = $schema[$schemaKey+1];
                $endx = explode('*', $endl);
                $end = trim($endx[0]);
                $endNodeLabel = preg_replace($stripPattern, '', $end);
                break;
            case "IN":
                $startl = $schema[$schemaKey+1];
                $ex = explode ('*', $startl);
                $start = trim($ex[0]);
                $startNodeLabel = preg_replace($stripPattern, '', $start);
                $endl = $schema[$schemaKey-1];
                $endx = explode('*', $endl);
                $end = trim($endx[0]);
                $endNodeLabel = preg_replace($stripPattern, '', $end);
                break;
        }

        // Guessing type of relationship
        preg_match($relTypePattern, $relString, $output);
        $type = str_replace(':', '', $output[0]);

        // Guessing cardinality
        preg_match($cardinalityPattern, $relString, $cardiOutput);
        $cardinality = $cardiOutput[0];
        $mode = trim(str_replace('*', '', $cardinality));

        $alias = 'n'.sha1(uniqid());
        $this->relationships[$alias] = [
            'start' => $startNodeLabel,
            'end' => $endNodeLabel,
            'type' => $type,
            'mode' => $mode
        ];



    }

    public function getSchema()
    {
        $schema = [
            'nodes' => $this->nodes,
            'relationships' => $this->relationships
        ];

        return $schema;
    }
}