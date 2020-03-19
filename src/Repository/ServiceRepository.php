<?php

namespace Repository;

use Illuminate\Database\Eloquent\Model;

abstract class ServiceRepository
{
    use Container\Paginator,
        Container\CacheRoutine,
        Container\BaseMethods,
        Container\QueryBuilder;

    /**
     * @var string $model
     */
    protected $model;

    /**
     * @var Model $model
     */
    private $model_instace;

    /**
     * Repository constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $class_name = $this->registerModel();
        $model_instance = new $class_name();
        if ($model_instance instanceof Model) {
            $this->model_instace = $model_instance;
        } else {
            throw new \Exception("The '". get_class($model_instance) ."' class you try to initialize is not a Model");
        }
    }

    /**
     * Return instance of actual model.
     *
     * @return Model
     */
    protected function getModel(): Model
    {
        return $this->model_instace;
    }

    /**
     * Get name of actial model.
     *
     * @return string
     * @throws \Exception
     */
    protected function registerModel()
    {
        if (empty($this->model)) {
            throw new \Exception("You must setup model class");
        }

        if (!is_string($this->model)) {
            throw new \Exception("Model name must be a string.");
        }

        return $this->model;
    }

    /**
     * Execute the query as a "select" statement.
     *
     * @param  array|string  $columns
     * @return mixed
     */
    protected function get($columns = ['*'])
    {
        if (!$this->isBuilderRequired()) {
            $cache_key = $this->generateCacheKey();

            return $this->buildCacheWithKey($cache_key, function () use ($columns) {
                return $this->getBuilder()->get($columns);
            });
        } else {
            return $this->returnQueryBuilder();
        }
    }

    /**
     * Retrieve the "count" result of the query.
     *
     * @param  string  $columns
     * @return int
     */
    protected function count($columns = '*')
    {
        return $this->aggregate(__FUNCTION__, $columns);
    }

    /**
     * Retrieve the minimum value of a given column.
     *
     * @param  string  $column
     * @return mixed
     */
    protected function min($column)
    {
        return $this->aggregate(__FUNCTION__, $column);
    }

    /**
     * Retrieve the maximum value of a given column.
     *
     * @param  string  $column
     * @return mixed
     */
    protected function max($column)
    {
        return $this->aggregate(__FUNCTION__, $column);
    }

    /**
     * Retrieve the sum of the values of a given column.
     *
     * @param  string  $column
     * @return mixed
     */
    protected function sum($column)
    {
        return $this->aggregate(__FUNCTION__, $column);
    }

    /**
     * Retrieve the average of the values of a given column.
     *
     * @param  string  $column
     * @return mixed
     */
    protected function avg($column)
    {
        return $this->aggregate(__FUNCTION__, $column);
    }

    /**
     * Execute raw query
     *
     * @param string $query
     * @return array
     */
    protected function executeRaw(string $query)
    {
        return $this->buildCache($query);
    }

    /**
     * Execute an aggregate function on the database.
     *
     * @param  string  $function
     * @param  string  $columns
     * @return mixed
     */
    private function aggregate(string $function, $columns)
    {
        if (!$this->isBuilderRequired()) {
            $cache_key = $this->generateCacheKey($this->getSql(), [$function, 'aggregate']);

            return $this->buildCacheWithKey($cache_key, function () use ($function, $columns) {
                return $this->getBuilder()->{$function}($columns);
            });
        } else {
            return $this->returnQueryBuilder();
        }
    }
}
