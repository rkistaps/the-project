<?php

declare(strict_types=1);

namespace TheProject\Core\Abstracts;

use TheProject\Core\Exceptions\InvalidArgumentException;

abstract class AbstractModelCollection extends AbstractCollection
{
    abstract public function getModelClassName(): string;

    /**
     * @param array $data
     * @return self
     */
    public static function collect(array $data = [])
    {
        $calledClass = get_called_class();

        return new $calledClass($data);
    }

    public function map(callable $callable): AbstractModelCollection
    {
        return self::collect($this->collection->map($callable)->all());
    }

    public function all(): array
    {
        return $this->collection->all();
    }

    public function add(AbstractModel $item): AbstractModelCollection
    {
        if (!is_a($item, $this->getModelClassName())) {
            throw new InvalidArgumentException(get_class($item) . ' is not ' . $this->getModelClassName());
        }

        $this->collection->add($item);

        return $this;
    }

    public function firstWhere($key, $operator = null, $value = null): ?AbstractModel
    {
        return parent::firstWhere($key, $operator, $value);
    }

    public function where($key, $operator = null, $value = null): AbstractModelCollection
    {
        $items = parent::where($key, $operator, $value)->all();

        return self::collect($items);
    }

    public function random($number = null)
    {
        $result = parent::random($number);

        return is_null($number)
            ? $result
            : self::collect($result);
    }

    public function filter(callable $callback = null): AbstractModelCollection
    {
        $items = parent::filter($callback)->all();

        return self::collect($items);
    }

    /**
     * @param int $offset
     * @param int|null $length
     * @return static
     */
    public function slice(int $offset, int $length = null)
    {
        $this->collection = $this->collection->slice($offset, $length);

        return $this;
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

    public function reduce(callable $callable, $initial = null)
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