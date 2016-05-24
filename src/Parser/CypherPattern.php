<?php

namespace GraphAware\Neogen\Parser;

use GraphAware\Neogen\Parser\Definition\NodeDefinition;
use GraphAware\Neogen\Parser\Definition\PropertyDefinition;
use Neoxygen\Neogen\Exception\CypherPatternException;
use GraphAware\Neogen\Exception\ParseException;
use GraphAware\Neogen\Helper\ArrayUtils;
use Symfony\Component\Yaml\Yaml,
    Symfony\Component\Yaml\Exception\ParseException as YamlParseException;

class CypherPattern implements ParserInterface
{
    /**
     *
     */
    const NODE_PATTERN = '/(?:^(?:\\()([_\w\d]*)([:#\w\d]+)*(\s?{[-,:~\'\"{}\[\]!\?\s\w\d]+})?(\s?\*\d+)?(?:\s*\))$)/';

    /**
     *
     */
    const EDGE_PATTERN = '/(<?>?-\[)(?::)([_\w\d]+)(\s?{[-,:~\'\"{}\[\]\s\w\d]+})?(\s?\*[\w\d+]\.\.[\w\d])(\]-<?>?)/';

    /**
     *
     */
    const SPLIT_PATTERN = "/((?:<?->?)(?:\\[[^<^>.]*\\*[a-z0-9]+\\.\\.[a-z0-9]+\\])(?:<?->?))/";

    /**
     *
     */
    const PROPERTY_KEY_VALIDATION_PATTERN = "/^[!\\?]?[a-z]+_?[a-z0-9]*$/";

    /**
     *
     */
    const INGOING_RELATIONSHIP = 'IN';

    /**
     *
     */
    const OUTGOING_RELATIONSHIP = 'OUT';

    /**
     * @var array
     */
    private $nodes;

    /**
     * @var array
     */
    private $edges;

    /**
     * @var array
     */
    private $labels;

    /**
     * @var array
     */
    private $identifiers;

    /**
     * @var array
     */
    private $nodeInfoMap;

    /**
     * @param  string                 $cypherPattern
     * @return array                  The converted Cypher => array schema
     * @throws CypherPatternException When parse exception
     */
    public function parse($cypherPattern)
    {
        $this->nodes = [];
        $this->edges = [];
        $this->labels = [];
        $this->identifiers = [];
        $this->nodeInfoMap = [];
        $lines = explode("\n", $this->preFormatPattern($cypherPattern));

        foreach ($lines as $line) {
            $parts = $this->parseLine($line);
            foreach ($parts as $key => $part) {
                if (preg_match(self::NODE_PATTERN, $part, $output)) {
                    $nodeInfo = $this->getNodePatternInfo($output, $part);
                    $this->processNode($nodeInfo, $part);
                } elseif (preg_match(self::EDGE_PATTERN, $part, $output)) {
                    $edgeInfo = $this->getEdgePatternInfo($output);
                    $this->processEdge($edgeInfo, $key, $parts);
                } else {
                    throw new CypherPatternException(sprintf('The part "%s" could not be parsed, check it for type errors.', $part));
                }
            }
        }

        return $this->getSchema();
    }

    /**
     * @param $pattern
     * @return mixed
     */
    public function preFormatPattern($pattern)
    {
        $lines = explode("\n", $pattern);
        $paste = '';
        foreach ($lines as $line) {
            $l = trim($line);
            if (false === strpos($l, '//')) {
                $paste .= $l;
            }
        }
        $formatInLines = str_replace(')(',")\n(", $paste);

        return $formatInLines;
    }

    /**
     * @param $cypherPattern
     * @return array
     */
    public function splitLineBreaks($cypherPattern)
    {
        $lines = explode("\n", $cypherPattern);
        $parsedLines = [];
        foreach ($lines as $line) {
            if (false === strpos($line, '//')) {
                $parsedLines[] = trim(htmlspecialchars_decode($line));
            }
        }

        return $parsedLines;
    }

    /**
     * @param $cypherLineText
     * @return array
     */
    public function parseLine($cypherLineText)
    {
        $parts = preg_split(self::SPLIT_PATTERN, $cypherLineText, null, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);

        return $parts;
    }

    public function matchPattern($pattern)
    {
        if (preg_match(self::NODE_PATTERN, $pattern, $output)) {
            return $this->getNodePatternDefintion($output, $pattern);
        } elseif (preg_match(self::EDGE_PATTERN, $pattern, $output)) {
            //
        }

        throw new ParseException(sprintf('Unable to parse part "%s"', $pattern));

    }

    public function getNodePatternDefintion(array $pregMatchOutput, $patternPart)
    {
        if (!array_key_exists(1, $pregMatchOutput) || empty($pregMatchOutput[1])) {
            throw new ParseException(sprintf('A node identifier is mandatory, none given in "%s"', $patternPart));
        }

        $identifier = trim((string) $pregMatchOutput[1]);

        if (!array_key_exists(2, $pregMatchOutput) || empty($pregMatchOutput[2])) {
            throw new ParseException(sprintf('At least one label is required in the pattern, none given in "%s"', $patternPart));
        }

        $defintion = new NodeDefinition($identifier);

        $labels = ArrayUtils::cleanEmptyStrings(explode(':', trim($pregMatchOutput[2])));
        foreach ($labels as $k => $label) {
            $label = trim($label);
            $model = null;
            if (0 === strpos($label, '#')) {
                $label = substr($label, 1);
                $model = $label;
            }

            $defintion->addLabel($label);
            $defintion->addModel($model);
        }

        if (array_key_exists(3, $pregMatchOutput)) {
            $properties = Yaml::parse(trim($pregMatchOutput[3]));
            foreach ($properties as $k => $v) {
                if (!preg_match(self::PROPERTY_KEY_VALIDATION_PATTERN, $k, $out)) {
                    throw new ParseException(sprintf('The property key "%s" is not valid in part "%s"', $k, $patternPart));
                }

                if ($defintion->hasProperty($k)) {
                    throw new ParseException(sprintf('The property key "%s" can only be defined once in part "%s"', $k, $patternPart));
                }

                $defintion->addProperty($this->getPropertyDefinition($k, $v));
            }
        }

        return $defintion;
    }

    public function getPropertyDefinition($key, $generator)
    {
        $u = false;
        $i = false;
        if (0 === strpos($key, '!')) {
            $u = true;
            $key = substr($key, 1);
        } elseif (0 === strpos($key, '?')) {
            $i = true;
            $key = substr($key, 1);
        }

        return new PropertyDefinition($key, $generator, $i, $u);
    }

    public function getEdgePatternDefinition(array $pregMatchOutput, $patternPart)
    {

    }

    /**
     * @param  array                  $nodeInfo
     * @param  null                   $part
     * @throws CypherPatternException
     */
    public function processNode(array $nodeInfo, $part = null)
    {
        $identifier = $nodeInfo['identifier'];
        if (array_key_exists($identifier, $this->nodes)) {
            return;
        }
        if (empty($nodeInfo['labels']) && !array_key_exists($nodeInfo['identifier'], $this->nodes)) {
            throw new CypherPatternException(sprintf('The identifier "%s" has not been declared in "%s"', $nodeInfo['identifier'], $part));
        }

        $labels = $nodeInfo['labels'];

        $node = [
            'identifier' => $identifier,
            'labels' => $labels,
            'count' => $nodeInfo['count'],
            'properties' => $nodeInfo['properties'],
            'models' => $nodeInfo['models']
        ];
        if (null !== $nodeInfo['identifier']) {
            $this->identifiers[$nodeInfo['identifier']] = $identifier;
            if (null !== $identifier) {
                $virtualPart = '('.$nodeInfo['identifier'].')';
                $this->nodeInfoMap[$virtualPart] = $nodeInfo;
            }

            if (null !== $part && null !== $nodeInfo['identifier']) {
                $this->nodeInfoMap[trim($part)] = $nodeInfo;
            }
        }

        if ($nodeInfo['properties']) {

            try {
                $properties = Yaml::parse($node['properties']);
                if (null !== $properties) {
                    foreach ($properties as $key => $type) {
                        if (is_array($type)) {
                            $props[$key]['type'] = key($type);
                            $props[$key]['params'] = [];
                            foreach (current($type) as $k => $v) {
                                $props[$key]['params'][] = $v;
                            }
                        } else {
                            $props[$key] = $type;
                        }
                    }
                    $node['properties'] = $props;
                }

            } catch (ParseException $e) {
                throw new CypherPatternException(sprintf('Malformed inline properties near "%s"', $node['properties']));
            }
        }

        $this->nodes[$identifier] = $node;
        $this->labels[] = $labels;
    }

    /**
     * @param  array                  $edgeInfo
     * @param $key
     * @param  array                  $parts
     * @throws CypherPatternException
     */
    public function processEdge(array $edgeInfo, $key, array $parts)
    {
        $prev = trim($parts[$key-1]);
        $previous = $this->nodeInfoMap[trim($prev)];
        $prevNode = $previous['identifier'];

        $next = trim($parts[$key+1]);

        preg_match(self::NODE_PATTERN, $next, $output);
        $info = $this->getNodePatternInfo($output, $next);
        $this->processNode($info);

        $nextious = $this->nodeInfoMap[trim($next)];
        $nextNode = !empty($nextious['identifier']) ? $nextious['identifier'] : $this->identifiers[$nextious['identifier']];

        $start = 'OUT' === $edgeInfo['direction'] ? $prevNode : $nextNode;
        $end = 'OUT' === $edgeInfo['direction'] ? $nextNode : $prevNode;

        $edge = [
            'start' => $start,
            'end' => $end,
            'type' => $edgeInfo['type'],
            'mode' => $edgeInfo['cardinality'],
        ];

        try {
            $props = [];
            $properties = Yaml::parse($edgeInfo['properties']);
            if (null !== $properties) {
                foreach ($properties as $key => $type) {
                    if (is_array($type)) {
                        $props[$key]['type'] = key($type);
                        $props[$key]['params'] = [];
                        foreach (current($type) as $k => $v) {
                            $props[$key]['params'][] = $v;
                        }
                    } else {
                        $props[$key] = $type;
                    }
                }
            }
            $edge['properties'] = $props;
        } catch (ParseException $e) {
            throw new CypherPatternException(sprintf('Malformed inline properties near "%s"', $edgeInfo['properties']));
        }

        $this->edges[] = $edge;

    }

    /**
     * @param $identifier
     * @return bool
     */
    public function hasIdentifier($identifier)
    {
        return array_key_exists($identifier, $this->identifiers);
    }

    /**
     * @param  array                  $nodePattern
     * @param $part
     * @return array
     * @throws CypherPatternException
     */
    public function getNodePatternInfo(array $nodePattern, $part)
    {
        if (empty($nodePattern[3])) {
            throw new CypherPatternException(sprintf('An identifier must be defined for nodes in "%s"', $part));
        }

        $labels = explode(':', trim($nodePattern['4']));
        array_shift($labels);
        $models = [];
        $lbls = [];
        foreach ($labels as $lbl) {
            $pos = strpos($lbl, '#');
            if ($pos !== false && 0 === $pos) {
                $sanitized = str_replace('#', '', $lbl);
                $models[] = $sanitized;
                $lbls[] = $sanitized;
            } else {
                $lbls[] = $lbl;
            }
        }
        $defaultInfo = [
            'identifier' => $this->nullString($nodePattern[3]),
            'labels' => $lbls,
            'properties' => $this->nullString($nodePattern[5]),
            'count' => $this->nullString($nodePattern[6]),
            'models' => $models
        ];
        if (empty($defaultInfo['count']) || '' == $defaultInfo['count']) {
            $defaultInfo['count'] = 1;
        }

        $this->nodeInfoMap[trim($part)] = $defaultInfo;

        return $defaultInfo;
    }

    /**
     * @param  array                  $edgePattern
     * @return array
     * @throws CypherPatternException
     */
    public function getEdgePatternInfo(array $edgePattern)
    {
        $arrowStart = $edgePattern[1];
        $arrowEnd = $edgePattern[5];
        $direction = $this->detectEdgeDirection($arrowStart, $arrowEnd);
        if (null === $direction) {
            $edgePart = &$edgePattern;
            unset($edgePart[0]);
            $patt = implode('', $edgePart);
            throw new CypherPatternException(sprintf('The direction of the relationship must be defined, near "%s".', $patt));
        }
        $type = $this->nullString($edgePattern[2]);
        if (null === $type) {
            throw new CypherPatternException(sprintf('The type of the relationship must be defined, near "%s"', $edgePattern));
        }
        $edge = [
            'type' => $type,
            'direction' => $direction,
            'cardinality' => $this->checkCardinality($edgePattern),
            'properties' => $this->nullString($edgePattern[3])
        ];

        return $edge;

    }

    /**
     * @return string
     */
    public function getNodePattern()
    {
        return self::NODE_PATTERN;
    }

    /**
     * @return string
     */
    public function getEdgePattern()
    {
        return self::EDGE_PATTERN;
    }

    /**
     * @param $pattern
     * @return mixed
     * @throws CypherPatternException
     */
    public function checkCardinality($pattern)
    {
        $allowedCardinalities = ['1..n', 'n..n', '1..1', 'n..1'];
        $cardinality = str_replace('*', '', trim($pattern[4]));
        if (!in_array($cardinality, $allowedCardinalities)) {
            throw new CypherPatternException(sprintf('The cardinality "%s" is not allowed', $cardinality));
        }

        return $cardinality;
    }

    /**
     * @return array
     */
    public function getSchema()
    {
        return array(
            'nodes' => $this->nodes,
            'relationships' => $this->edges
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'cypher';
    }

    /**
     * @param $start
     * @param $end
     * @return null|string
     */
    private function detectEdgeDirection($start, $end)
    {
        if ($start === '-[' && $end === ']->') {
            return self::OUTGOING_RELATIONSHIP;
        } elseif ($start === '<-[' && $end === ']-') {
            return self::INGOING_RELATIONSHIP;
        }

        return null;
    }

    /**
     * @param $string
     * @return mixed|null
     */
    private function nullString($string)
    {
        if (trim($string) === '') {
            return null;
        }

        return str_replace('*', '', trim($string));
    }
}
