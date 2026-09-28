<?php

declare(strict_types=1);

namespace TheProject\Core\Interfaces;

use TheProject\Core\Abstracts\AbstractModel;

interface ModelDataHydratorInterface
{
    /**
     * Hydrate model with data from array
     * @param AbstractModel $model
     * @param array $data
     * @return AbstractModel
     */
    public function hydrate(AbstractModel $model, array $data): AbstractModel;

    /**
     * Extract data from model to array
     * @param AbstractModel $model
     * @return array
     */
    public function extract(AbstractModel $model): array;

    /**
     * Hydrate single property
     * @param AbstractModel $model
     * @param string $property
     * @param $value
     * @return AbstractModel
     */
    public function hydrateProperty(AbstractModel $model, string $property, $value): AbstractModel;
}