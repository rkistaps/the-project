<?php

declare(strict_types=1);

namespace TheProject\Core\Services;

use ReflectionNamedType;
use ReflectionProperty;
use TheProject\Core\Abstracts\AbstractModel;
use TheProject\Core\Helpers\StringHelper;
use TheProject\Core\Interfaces\ModelDataHydratorInterface;

class ModelDataHydratorService implements ModelDataHydratorInterface
{
    public function hydrate(AbstractModel $model, array $data): AbstractModel
    {
        foreach (get_class_vars(get_class($model)) as $property => $default) {
            $arrayProperty = StringHelper::toSnakeCase($property);
            if (!array_key_exists($arrayProperty, $data)) {
                continue;
            }

            $this->hydrateProperty($model, $property, $data[$arrayProperty]);
        }

        return $model;
    }

    public function hydrateProperty(AbstractModel $model, string $property, mixed $value): AbstractModel
    {
        $type = (new ReflectionProperty($model, $property))->getType();

        // Database drivers return numbers as strings. Untyped and union-typed properties get the value as it is.
        if ($type instanceof ReflectionNamedType && $value !== null) {
            $value = match ($type->getName()) {
                'int' => (int) $value,
                'float' => (float) $value,
                'bool' => (bool) $value,
                default => $value,
            };
        }

        $model->{$property} = $value;

        return $model;
    }

    public function extract(AbstractModel $model): array
    {
        $objectVars = get_object_vars($model);

        $data = [];
        foreach ($objectVars as $propertyName => $value) {
            $data[StringHelper::toSnakeCase($propertyName)] = $value;
        }

        return $data;
    }
}
