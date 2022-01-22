<?php

namespace TheProject\Core\Factories;

use League\Plates\Engine;
use Psr\Container\ContainerInterface;
use TheApp\Interfaces\ConfigInterface;

/**
 * Class TemplateEngineFactory
 * @package TheProject\Factories
 */
class TemplateEngineFactory
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function build(ConfigInterface $config): Engine
    {
        $engine = new Engine($config->get('templatePath') ?? '/');

        return $engine;
    }
}
