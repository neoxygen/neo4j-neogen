<?php

namespace Neoxygen\Neogen\Schema;

class Property
{

    /**
     * @var string The property name
     */
    protected $name;

    /**
     * @var string the property faker provider name to use
     */
    protected $provider;

    /**
     * @var null|array The property faker provider arguments (optional)
     */
    protected $arguments;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = (string) $name;
    }

    /**
     * Gets the property key name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets the property faker provider to use
     *
     * @return string
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * Sets the property faker provider to use
     *
     * @param string $provider  The property faker provider
     * @param array  $arguments The property faker provider arguments (optional)
     */
    public function setProvider($provider, array $arguments = array())
    {
        if (null === $provider || '' === $provider) {
            throw new \InvalidArgumentException('A property faker provider name can not be empty');
        }

        $this->provider = $provider;

        if (!empty($arguments)) {
            $this->arguments = $arguments;
        }
    }

    /**
     * Returns the property faker provider arguments
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Checks whether or not the property faker provider has specified arguments
     *
     * @return bool
     */
    public function hasArguments()
    {
        if (null === $this->arguments) {
            return false;
        }

        return true;
    }
}
