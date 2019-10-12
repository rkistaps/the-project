<?php

namespace TheProject\Factories;

use League\Plates\Engine;
use Psr\Container\ContainerInterface;
use TheApp\Interfaces\ConfigInterface;

/**
 * Class TemplateEngineFactory
 * @package TheProject\Factories
 */
class TemplateEngineFactory
{
    /** @var ContainerInterface */
    private $container;

    /**
     * TemplateEngineFactory constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param ConfigInterface $config
     * @return Engine
     */
    public function build(ConfigInterface $config)
    {
        $engine = new Engine($config->get('templatePath') ?? '/');

        return $engine;
    }
}
