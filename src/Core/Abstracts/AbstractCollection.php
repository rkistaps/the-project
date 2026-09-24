<?php

declare(strict_types=1);

namespace TheProject\Core\Abstracts;

use Tightenco\Collect\Support\Collection;

abstract class AbstractCollection
{
    protected Collection $collection;

    // Final, so methods can safely return `new static(...)`
    final public function __construct(array $data = [])
    {
        $this->collection = collect($data);
    }

    public function getCollection(): Collection
    {
        return $this->collection;
    }

    /**
     * firstWhere('status', 'active') or firstWhere('age', '>', 18)
     */
    public function firstWhere(string $key, mixed $operator = null, mixed $value = null): mixed
    {
        // Forward only the arguments given: the collection tells the two forms apart by counting them
        return $this->collection->firstWhere(...func_get_args());
    }

    public function first(?callable $callback = null, mixed $default = null): mixed
    {
        return $this->collection->first($callback, $default);
    }

    /**
     * where('status', 'active') or where('age', '>', 18)
     *
     * @return static A new collection with the matching items
     */
    public function where(string $key, mixed $operator = null, mixed $value = null): AbstractCollection
    {
        // Forward only the arguments given: the collection tells the two forms apart by counting them
        return new static($this->collection->where(...func_get_args())->all());
    }

    /**
     * One random item, or a Collection of $number items. Null when the collection is empty.
     */
    public function random(?int $number = null): mixed
    {
        if (!$this->collection->count()) {
            return null;
        }

        return $this->collection->random($number);
    }

    /**
     * @return static
     */
    public function sort(?callable $callback = null): AbstractCollection
    {
        $this->collection = $this->collection->sort($callback);

        return $this;
    }

    /**
     * @param callable|string $callback A callback returning the sort value, or a property name
     * @return static
     */
    public function sortBy(callable|string $callback, int $options = SORT_REGULAR, bool $descending = false): AbstractCollection
    {
        $this->collection = $this->collection->sortBy($callback, $options, $descending);

        return $this;
    }

    public function pluck(string $value, ?string $key = null): Collection
    {
        return $this->collection->pluck($value, $key);
    }

    /**
     * @return static A new collection with the items the callback accepts (without one, the truthy items)
     */
    public function filter(?callable $callback = null): AbstractCollection
    {
        return new static($this->collection->filter($callback)->all());
    }

    /**
     * @return static A new collection with that part of the items
     */
    public function slice(int $offset, ?int $length = null): AbstractCollection
    {
        return new static($this->collection->slice($offset, $length)->all());
    }

    /**
     * @return static
     */
    public function transform(callable $callable): AbstractCollection
    {
        $this->collection->transform($callable);

        return $this;
    }

    public function count(): int
    {
        return $this->collection->count();
    }

    /**
     * @return static
     */
    public function merge(AbstractCollection $items): AbstractCollection
    {
        $this->collection = $this->collection->merge($items->getCollection());

        return $this;
    }

    public function mapFieldById(string $field): array
    {
        return $this
            ->getCollection()
            ->keyBy('id')
            ->map(fn(AbstractModel $model) => $model->$field)
            ->all();
    }
}
