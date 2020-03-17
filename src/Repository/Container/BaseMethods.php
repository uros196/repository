<?php

namespace Repository\Container;

trait BaseMethods
{

    /**
     * Add a basic where clause to the query, and return the first result.
     *
     * @param  \Closure|string|array  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     * @param  string  $boolean
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function firstWhere($column, $operator = null, $value = null, $boolean = 'and')
    {
        return $this->buildCache(function () use ($column, $operator, $value, $boolean) {
            return $this->getModel()::firstWhere($column, $operator, $value, $boolean);
        });
    }

    /**
     * Find a model by its primary key.
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|static[]|static|null
     */
    public function find($id, $columns = ['*'])
    {
        return $this->buildCache(function () use ($id, $columns) {
            return $this->getModel()::find($id, $columns);
        });
    }

    /**
     * Find multiple models by their primary keys.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array  $ids
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findMany($ids, $columns = ['*'])
    {
        return $this->buildCache(function () use ($ids, $columns) {
            return $this->getModel()::findMany($ids, $columns);
        });
    }

    /**
     * Find a model by its primary key or throw an exception.
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|static|static[]
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail($id, $columns = ['*'])
    {
        return $this->buildCache(function () use ($id, $columns) {
            return $this->getModel()::findOrFail($id, $columns);
        });
    }

    /**
     * Find a model by its primary key or return fresh model instance.
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function findOrNew($id, $columns = ['*'])
    {
        return $this->buildCache(function () use ($id, $columns) {
            return $this->getModel()::findOrNew($id, $columns);
        });
    }

    /**
     * Get the first record matching the attributes or instantiate it.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function firstOrNew(array $attributes = [], array $values = [])
    {
        return $this->buildCache(function () use ($attributes, $values) {
            return $this->getModel()::firstOrNew($attributes, $values);
        });
    }

    /**
     * Get the first record matching the attributes or create it.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function firstOrCreate(array $attributes, array $values = [])
    {
        return $this->buildCache(function () use ($attributes, $values) {
            return $this->getModel()::firstOrCreate($attributes, $values);
        });
    }

    /**
     * Execute the query and get the first result or throw an exception.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model|static
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function firstOrFail($columns = ['*'])
    {
        return $this->buildCache(function () use ($columns) {
            return $this->getModel()::firstOrFail($columns);
        });
    }

    /**
     * Execute the query and get the first result or call a callback.
     *
     * @param  \Closure|array  $columns
     * @param  \Closure|null  $callback
     * @return \Illuminate\Database\Eloquent\Model|static|mixed
     */
    public function firstOr($columns = ['*'], \Closure $callback = null)
    {
        return $this->buildCache(function () use ($columns, $callback) {
            return $this->getModel()::firstOr($columns, $callback);
        });
    }

    /**
     * Get a single column's value from the first result of a query.
     *
     * @param  string  $column
     * @return mixed
     */
    public function value($column)
    {
        return $this->buildCache(function () use ($column) {
            return $this->getModel()::value($column);
        });
    }
}
