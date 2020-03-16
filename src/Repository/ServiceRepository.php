<?php

namespace Repository;

use Illuminate\Database\Eloquent\Model;

abstract class ServiceRepository
{
    /**
     * @var string $model
     */
    protected $model;

    /**
     * @var Model $model
     */
    private $model_instace;

    /**
     * @var \Illuminate\Database\Eloquent\Builder $query_builder
     */
    private $query_builder;

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

    protected function getModel(): Model
    {
        return $this->model_instace;
    }

    public function finfOrFail(int $id): Model
    {
        return $this->getModel()::findOrFail($id);
    }

    protected function registerModel()
    {
        return $this->model;
    }

    /**
     * @param \Closure|string $query
     * @return void
     */
    protected function build($query): void
    {
        if ($query instanceof \Closure) {
            $query_builder = $query($this->getModel());
            $this->query_builder = $query_builder;
        }
    }

    /**
     * @param  array|string  $columns
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    protected function get($columns = ['*'])
    {
        return $this->query_builder->get($columns);
    }

    /**
     * @param $method
     * @param $parameters
     * @return mixed
     * @throws \Exception
     */
    public function __call($method, $parameters)
    {
        $default_service_container = new DefaultServiceContainer($this->getModel());
        if (method_exists($default_service_container, $method)) {
            return $default_service_container->$method(...$parameters);
        }

        throw new \Exception("The method '{$method}' do not exists.");
    }

    public static function __callStatic($method, $parameters)
    {
        return (new static)->$method(...$parameters);
    }
}
