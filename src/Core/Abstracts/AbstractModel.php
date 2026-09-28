<?php

declare(strict_types=1);

namespace TheProject\Core\Abstracts;

/**
 * A database row as an object. Models are plain data: repositories load and save them through
 * a ModelDataHydratorInterface, so a model never needs the container.
 */
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
}
