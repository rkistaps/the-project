<?php

declare(strict_types=1);

namespace TheProject\Core\Abstracts;

use TheProject\Core\Exceptions\InvalidArgumentException;

abstract class AbstractModelCollection extends AbstractCollection
{
    /**
     * @return class-string<AbstractModel>
     */
    abstract public function getModelClassName(): string;

    /**
     * @return static
     */
    public static function collect(array $data = []): AbstractModelCollection
    {
        return new static($data);
    }

    /**
     * @return static
     */
    public function map(callable $callable): AbstractModelCollection
    {
        return static::collect($this->collection->map($callable)->all());
    }

    public function all(): array
    {
        return $this->collection->all();
    }

    /**
     * @return static
     * @throws InvalidArgumentException When the item isn't this collection's model class
     */
    public function add(AbstractModel $item): AbstractModelCollection
    {
        if (!is_a($item, $this->getModelClassName())) {
            throw new InvalidArgumentException(get_class($item) . ' is not ' . $this->getModelClassName());
        }

        $this->collection->add($item);

        return $this;
    }

    public function firstWhere(string $key, mixed $operator = null, mixed $value = null): ?AbstractModel
    {
        return parent::firstWhere(...func_get_args());
    }

    /**
     * One random model, or a collection of $number models. Null when the collection is empty.
     */
    public function random(?int $number = null): AbstractModel|AbstractModelCollection|null
    {
        $result = parent::random($number);

        return $result === null || $number === null
            ? $result
            : static::collect($result->all());
    }

    public function property(string $property): array
    {
        return $this
            ->collection
            ->map(function (AbstractModel $model) use ($property) {
                return $model->{$property};
            })
            ->all();
    }

    public function getIds(): array
    {
        return $this->property('id');
    }

    public function getById(int $id): ?AbstractModel
    {
        return $this->first(fn(AbstractModel $model) => $model->id === $id);
    }

    public function reduce(callable $callable, mixed $initial = null): mixed
    {
        return $this->collection->reduce($callable, $initial);
    }

    public function isEmpty(): bool
    {
        return $this->collection->isEmpty();
    }

    public function isNotEmpty(): bool
    {
        return $this->collection->isNotEmpty();
    }
}
