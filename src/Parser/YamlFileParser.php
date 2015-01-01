<?php

namespace Neoxygen\Neogen\Parser;

use Symfony\Component\Yaml\Yaml,
    Symfony\Component\Yaml\Exception\ParseException,
    Symfony\Component\Filesystem\Filesystem;
use Neoxygen\Neogen\Parser\ParserInterface,
    Neoxygen\Neogen\Exception\SchemaDefinitionException;

class YamlFileParser implements ParserInterface
{
    /**
     * @var Filesystem
     */
    protected $fs;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->fs = new Filesystem();
    }

    /**
     * @param $schemaFilePath The User Schema File Path
     * @return array
     * @throws SchemaDefinitionException
     */
    public function parse($schemaFilePath)
    {
        $schema = $this->getSchemaFileContent($schemaFilePath);

        return $schema;
    }

    /**
     * Returns the name of the parser
     *
     * @return string
     */
    public function getName()
    {
        return 'YamlParser';
    }

    /**
     * Returns the FileSystem component
     *
     * @return Filesystem
     */
    public function getFS()
    {
        return $this->fs;
    }

    /**
     * Get the contents of the User Schema YAML File and transforms it to php array
     *
     * @param $filePath
     * @return array
     * @throws SchemaDefinitionException
     */
    public function getSchemaFileContent($filePath)
    {
        if (!$this->fs->exists($filePath)) {
            throw new SchemaDefinitionException(sprintf('The schema file "%s" was not found', $filePath));
        }

        $content = file_get_contents($filePath);

        try {
            $schema = Yaml::parse($content);

            return $schema;
        } catch (ParseException $e) {
            throw new SchemaDefinitionException($e->getMessage());
        }
    }
}
