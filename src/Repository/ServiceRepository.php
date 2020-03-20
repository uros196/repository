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
     * Execute raw query
     *
     * @param string $query
     * @return array
     */
    protected function executeRaw(string $query)
    {
        $query_closure = function () use ($query) {
            return DB::select($query);
        };

        if ($this->idCacheActivated()) {
            $key = $this->generateCacheKey($query);
            return $this->buildCacheWithKey($key, $query_closure);
        }

        return $query_closure();
    }

    /**
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

            return $callback();
        }

        return $this->returnQueryBuilder();
    }

    /**
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
     * Handle dynamic method calls into eloquent builder.
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

        try {
            $type = $this->getMethodReturnType($this->getBuilder(), $method);
        } catch (\Exception $exception) {
            $type = $this->getMethodReturnType($this->getBuilder()->getQuery(), $method);
        }

        if ($type instanceof \Illuminate\Database\Eloquent\Builder ||
            $type instanceof \Illuminate\Database\Query\Builder) {
            return $this->buildQuery($query_closure);
        }

        return $this->run($query_closure, $method);
    }
}
