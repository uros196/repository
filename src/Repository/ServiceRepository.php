<?php

namespace Repository;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Traits\ForwardsCalls;
use Repository\Helpers\DocBlockParser;

abstract class ServiceRepository
{
    use ForwardsCalls,
        Container\Paginator,
        Container\CacheRoutine,
        Container\QueryBuilder;

    /**
     * Holds name of actial model.
     *
     * @var string $model
     */
    protected $model;

    /**
     * Holds instance of actual model.
     *
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
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    protected function get($columns = ['*'])
    {
        $query_closure = function () use ($columns) {
            return $this->getBuilder()->get($columns);
        };

        return $this->run($query_closure, __FUNCTION__);
    }

    /**
     * Execute raw query.
     *
     * @param string $query
     * @return array|string
     */
    protected function executeRaw(string $query)
    {
        if (!$this->isBuilderRequired()) {
            $query_closure = function () use ($query) {
                return DB::select($query);
            };

            if ($this->idCacheActivated()) {
                $key = $this->generateCacheKey($query);
                return $this->buildCacheWithKey($key, $query_closure);
            }

            return $query_closure();
        }

        return $query;
    }

    /**
     * Final method.
     *
     * @param \Closure $callback
     * @param string $cache_tags
     * @return \Illuminate\Database\Eloquent\Builder|mixed
     */
    private function run(\Closure $callback, $cache_tags = '')
    {
        if (!$this->isBuilderRequired()) {
            if ($this->idCacheActivated()) {
                $cache_key = $this->generateCacheKey(null, $cache_tags);
                return $this->buildCacheWithKey($cache_key, $callback);
            }

            // just execute a query without all benefits.
            return $callback();
        }

        return $this->returnQueryBuilder();
    }

    /**
     * Take a method return type.
     *
     * @param $object
     * @param $method
     * @return mixed
     *
     * @throws \ReflectionException
     */
    private function getMethodReturnType($object, $method)
    {
        $reflection = new \ReflectionClass($object);
        $doc = $reflection->getMethod($method)->getDocComment();

        $parser = new DocBlockParser($doc);
        $return_statement = $parser->return();

        return $return_statement != '$this' ?: $object;
    }

    /**
     * Check that the method at the end of the chain is called (if the actual query is running).
     *
     * @param string $method
     * @return bool
     *
     * @throws \ReflectionException
     */
    private function isFinalMethod(string $method): bool
    {
        try {
            $type = $this->getMethodReturnType($this->getBuilder(), $method);
        } catch (\Exception $exception) {
            $type = $this->getMethodReturnType($this->getBuilder()->getQuery(), $method);
        }

        return !($type instanceof \Illuminate\Database\Eloquent\Builder ||
                 $type instanceof \Illuminate\Database\Query\Builder);
    }

    /**
     * Handle dynamic method calls into Eloquent or Query builder.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     *
     * @throws \ReflectionException
     */
    public function __call($method, $parameters)
    {
        $query_closure = function () use ($method, $parameters) {
            return $this->forwardCallTo($this->getBuilder(), $method, $parameters);
        };

        // check if is end point method like a get(), max()... or any other
        // then execute query
        if ($this->isFinalMethod($method)) {
            return $this->run($query_closure, $method);
        }

        // in other case just build a query
        return $this->buildQuery($query_closure);
    }
}
