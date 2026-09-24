<?php

declare(strict_types=1);

namespace TheProject\Core\Abstracts;

use TheProject\Core\Helpers\StringHelper;
use TheProject\Core\Interfaces\ModelDataHydratorInterface;

use function getContainer;

abstract class AbstractModel
{
    public int $id;

    public function getPrimaryKey(): array
    {
        return ['id'];
    }

    public function isNew(): bool
    {
        return !isset($this->id) || !$this->id;
    }

    public function __set($name, $value)
    {
        $hydrator = getContainer()->get(ModelDataHydratorInterface::class);

        $nameCandidate = StringHelper::toCamelCase($name);
        if (property_exists($this, $nameCandidate)) {
            $hydrator->hydrateProperty($this, $nameCandidate, $value);
        }
    }

    public function __toString()
    {
        $hydrator = getContainer()->get(ModelDataHydratorInterface::class);

        return print_r($hydrator->extract($this), true);
    }
}