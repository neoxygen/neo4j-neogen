<?php

namespace Neoxygen\Neogen\FakerProvider;

use Faker\Factory,
    Faker\Provider\Base as BaseProvider;

class Faker
{
    protected $faker;

    protected $arrayzedProviders = [];

    public function __construct()
    {
        $this->faker = Factory::create();
        $this->arrayzedProviders[] = 'randomElement';
        $this->arrayzedProviders[] = 'randomElements';
    }

    public function generate($provider, $args = [], $seed = null, $unique = false, $arrayze = false)
    {
        if (in_array($provider, $this->arrayzedProviders)) {
            $arrayze = true;
        }

        if (null !== $seed) {
            $this->faker->seed((int) $seed);
        }

        if ($unique) {
            return $this->getUniqueValue($provider, $args, $arrayze);
        }

        return $this->getValue($provider, $args, $arrayze);
    }

    public function registerProvider(BaseProvider $provider)
    {
        return $this->faker->addProvider($provider);
    }

    public function registerProviderExtension(ProviderExtensionInterface $extension)
    {
        $classes = $extension->getProviders();
        $arrP = $extension->getArrayzedProviders();
        foreach ($classes as $class) {
            $this->registerProvider(new $class());
        }
        foreach ($arrP as $arr) {
            $this->arrayzedProviders[] = $arr;
        }
    }

    private function getUniqueValue($provider, array $args, $arrayzeArgs = false)
    {
        if ($arrayzeArgs) {
            $value = call_user_func_array(array($this->faker->unique, $provider), array($args));
        } else {
            $value = call_user_func_array(array($this->faker->unique, $provider), $args);
        }

        return $value;
    }

    private function getValue($provider, array $args, $arrayzeArgs = false)
    {
        if ($arrayzeArgs) {
            $value = call_user_func_array(array($this->faker, $provider), array($args));
        } else {
            $value = call_user_func_array(array($this->faker, $provider), $args);
        }

        return $value;
    }
}