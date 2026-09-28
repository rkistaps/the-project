<?php

declare(strict_types=1);

namespace TheProject\Core\Abstracts;

use Opis\Database\Database;
use Opis\Database\ResultSet;
use Opis\Database\SQL\BaseStatement;
use Opis\Database\SQL\Query;
use TheProject\Core\Interfaces\ModelDataHydratorInterface;

/**
 * Loads and saves one model class in one table.
 *
 * @template TModel of AbstractModel
 * @template TCollection of AbstractModelCollection
 */
abstract class AbstractModelRepository
{
    protected Database $database;
    protected ModelDataHydratorInterface $hydrator;

    public function __construct(
        Database $database,
        ModelDataHydratorInterface $hydrator,
    ) {
        $this->database = $database;
        $this->hydrator = $hydrator;
    }

    abstract protected function getTableName(): string;

    /**
     * @return class-string<TModel>
     */
    abstract protected function getModelClassName(): string;

    /**
     * @return class-string<TCollection>
     */
    abstract protected function getCollectionClassName(): string;

    /**
     * @return TModel
     */
    public function createModel(array $properties = [], bool $persistent = false): AbstractModel
    {
        $model = $this->createBlankModel();

        $this->hydrator->hydrate($model, $properties);

        if ($persistent) {
            $this->saveModel($model);
        }

        return $model;
    }

    /**
     * @return TModel
     */
    protected function createBlankModel(): AbstractModel
    {
        $modelClass = $this->getModelClassName();

        return new $modelClass();
    }

    /**
     * @return TCollection
     */
    public function findAll(array $condition = []): AbstractModelCollection
    {
        $collectionClassName = $this->getCollectionClassName();

        $rows = $this
            ->buildSelect($condition)
            ->fetchAssoc()
            ->all();

        return new $collectionClassName(array_map(fn(array $row) => $this->hydrateRow($row), $rows));
    }

    /**
     * @return TModel|null
     */
    public function findOne(array $condition = []): ?AbstractModel
    {
        $row = $this->buildSelect($condition)->fetchAssoc()->first();

        return $row ? $this->hydrateRow($row) : null;
    }

    /**
     * Turn a row (snake_case column => value) into a model, casting values to the property types
     *
     * @return TModel
     */
    protected function hydrateRow(array $row): AbstractModel
    {
        return $this->hydrator->hydrate($this->createBlankModel(), $row);
    }

    /**
     * @return TModel|null
     */
    public function findById(int $id): ?AbstractModel
    {
        return $this->findOne(['id' => $id]);
    }

    protected function buildSelect(array $conditions = []): ResultSet
    {
        return $this->buildQuery($conditions)->select();
    }

    protected function buildQuery(array $conditions = []): Query
    {
        $query = $this
            ->database
            ->from($this->getTableName());

        $this->addConditionsToStatement($query, $conditions);

        return $query;
    }

    public function delete(array $conditions = []): int
    {
        return $this->buildQuery($conditions)->delete();
    }

    public function deleteModel(AbstractModel $model): int
    {
        return $this->delete($this->buildPrimaryKeyCondition($model));
    }

    /**
     * Insert a new model, or update an existing one (only the given properties, if any)
     *
     * @param TModel $model
     * @param string[] $properties
     * @return TModel
     */
    public function saveModel(AbstractModel $model, array $properties = []): AbstractModel
    {
        return $model->isNew()
            ? $this->insertModel($model)
            : $this->updateModel($model, $properties);
    }

    /**
     * @param TModel $model
     * @param string[] $properties
     * @return TModel
     */
    protected function updateModel(AbstractModel $model, array $properties = []): AbstractModel
    {
        $modelData = $this->hydrator->extract($model);

        $query = $this
            ->database
            ->update($this->getTableName());

        $this->addConditionsToStatement(
            $query,
            $this->buildPrimaryKeyCondition($model),
        );

        $updateData = $modelData;
        if ($properties) {
            $updateData = [];
            foreach ($modelData as $key => $value) {
                if (in_array($key, $properties)) {
                    $updateData[$key] = $value;
                }
            }
        }

        $query->set($updateData);

        return $model;
    }

    /**
     * @param TModel $model
     * @return TModel
     */
    protected function insertModel(AbstractModel $model): AbstractModel
    {
        $data = $this->hydrator->extract($model);

        if ($this->insert($data)) {
            $model->id = $this->getLastInsertId();
        }

        return $model;
    }

    protected function getLastInsertId(): int
    {
        return (int) $this->database->getConnection()->getPDO()->lastInsertId();
    }

    protected function insert(array $data): bool
    {
        return $this->database->insert($data)->into($this->getTableName());
    }

    protected function addConditionsToStatement(BaseStatement $statement, array $conditions = []): void
    {
        foreach ($conditions as $key => $value) {
            if (is_array($value)) {
                $statement->andWhere($key)->in($value);
            } elseif (is_null($value)) {
                $statement->andWhere($key)->isNull();
            } else {
                $statement->andWhere($key)->is($value);
            }
        }
    }

    protected function buildPrimaryKeyCondition(AbstractModel $model): array
    {
        $condition = [];
        foreach ($model->getPrimaryKey() as $column) {
            $condition[$column] = $model->$column;
        }

        return $condition;
    }

    protected function updateAll(array $data, array $condition = []): int
    {
        $query = $this->database->update($this->getTableName());

        if ($condition) {
            $this->addConditionsToStatement($query, $condition);
        }

        return $query->set($data);
    }

    protected function deleteAll(array $condition = []): int
    {
        $query = $this->database->from($this->getTableName());

        if ($condition) {
            $this->addConditionsToStatement($query, $condition);
        }

        return $query->delete();
    }

    /**
     * @return TModel|null
     */
    public function findRandom(array $condition = []): ?AbstractModel
    {
        $row = $this
            ->buildQuery($condition)
            ->orderBy(fn($expr) => $expr->op('rand()'))
            ->select()
            ->fetchAssoc()
            ->first();

        return $row ? $this->hydrateRow($row) : null;
    }

    public function truncate(): void
    {
        $this->database->schema()->truncate($this->getTableName());
    }
}
