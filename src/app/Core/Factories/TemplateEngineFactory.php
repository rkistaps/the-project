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
    public function build(ConfigInterface $config): Engine
    {
        return new Engine($config->get('templatePath') ?? '/');
    }
}
