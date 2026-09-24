<?php

declare(strict_types=1);

namespace TheProject\Core\Abstracts;

use Opis\Database\Database;
use Opis\Database\ResultSet;
use Opis\Database\SQL\BaseStatement;
use Opis\Database\SQL\Query;
use TheProject\Core\Interfaces\ModelDataHydratorInterface;

abstract class AbstractModelRepository
{
    protected Database $database;
    protected ModelDataHydratorInterface $hydrator;

    public function __construct(
        Database $database,
        ModelDataHydratorInterface $hydrator
    ) {
        $this->database = $database;
        $this->hydrator = $hydrator;
    }

    abstract protected function getTableName(): string;

    abstract protected function getModelClassName(): string;

    abstract protected function getCollectionClassName(): string;

    public function createModel(array $properties = [], bool $persistent = false): AbstractModel
    {
        $model = $this->createBlankModel();

        $this->hydrator->hydrate($model, $properties);

        if ($persistent) {
            $this->saveModel($model);
        }

        return $model;
    }

    protected function createBlankModel(): AbstractModel
    {
        $modelClass = $this->getModelClassName();

        return new $modelClass;
    }

    public function findAll(array $condition = []): AbstractModelCollection
    {
        $collectionClassName = $this->getCollectionClassName();

        $models = $this
            ->buildSelect($condition)
            ->fetchClass($this->getModelClassName())
            ->all();

        return new $collectionClassName($models);
    }

    public function findOne(array $condition = []): ?AbstractModel
    {
        $result = $this->buildSelect($condition)->fetchClass($this->getModelClassName())->first();

        return $result ?: null;
    }

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
     * @param AbstractModel $model
     * @param array $properties
     * @return AbstractModel
     */
    public function saveModel(AbstractModel $model, array $properties = []): AbstractModel
    {
        return $model->isNew()
            ? $this->insertModel($model)
            : $this->updateModel($model, $properties);
    }

    protected function updateModel(AbstractModel $model, array $properties = []): AbstractModel
    {
        $modelData = $this->hydrator->extract($model);

        $query = $this
            ->database
            ->update($this->getTableName());

        $this->addConditionsToStatement(
            $query,
            $this->buildPrimaryKeyCondition($model)
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
        return (int)$this->database->getConnection()->getPDO()->lastInsertId();
    }

    protected function insert(array $data): bool
    {
        return $this->database->insert($data)->into($this->getTableName());
    }

    protected function addConditionsToStatement(BaseStatement $statement, array $conditions = [])
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

    public function findRandom(array $condition = []): ?AbstractModel
    {
        return $this
            ->buildQuery($condition)
            ->orderBy(fn($expr) => $expr->op('rand()'))
            ->select()
            ->fetchClass($this->getModelClassName())
            ->first();
    }

    public function truncate()
    {
        $this->database->schema()->truncate($this->getTableName());
    }
}