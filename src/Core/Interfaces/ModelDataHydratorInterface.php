<?php

declare(strict_types=1);

namespace TheProject\Core\Interfaces;

use TheProject\Core\Abstracts\AbstractModel;

interface ModelDataHydratorInterface
{
    /**
     * Set the model's properties from an array with snake_case keys, such as a database row
     *
     * @template T of AbstractModel
     * @param T $model
     * @return T The same model
     */
    public function hydrate(AbstractModel $model, array $data): AbstractModel;

    /**
     * The model's properties as an array with snake_case keys
     */
    public function extract(AbstractModel $model): array;

    /**
     * Set one property, cast to its declared type
     *
     * @template T of AbstractModel
     * @param T $model
     * @return T The same model
     */
    public function hydrateProperty(AbstractModel $model, string $property, mixed $value): AbstractModel;
}
