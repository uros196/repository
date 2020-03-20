<?php

namespace Repository\Container;

use Psy\Exception\TypeErrorException;

trait QueryBuilder
{
    /**
     * Store entire builder.
     *
     * @var \Illuminate\Database\Eloquent\Builder
     */
    private $query_builder;

    /**
     * @var bool $is_builder_required
     */
    private $is_builder_required;

    /**
     * Force repository to return a query builder.
     *
     * @param bool $require
     * @return $this
     */
    public function requireBuilder(bool $require = true)
    {
        $this->is_builder_required = $require;

        return $this;
    }

    /**
     * Get a query builder for the model's table.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getBuilder()
    {
        if (empty($this->query_builder)) {
            $this->query_builder = $this->getModel()->newQuery();
        }

        return $this->query_builder;
    }

    /**
     * Take raw SQL query from actual builder.
     *
     * @return string
     */
    protected function getSql(): string
    {
        return $this->getBuilder()->toSql();
    }

    /**
     * Use query builder to build your own query.
     *
     * @param \Closure $callback
     * @return $this
     */
    protected function buildQuery(\Closure $callback)
    {
        $this->query_builder = $callback($this->getBuilder());

        if (!$this->query_builder instanceof \Illuminate\Database\Eloquent\Builder) {
            $this->thorwTypeErrorException();
        }

        return $this;
    }

    /**
     * Throw an exception if the user try to execute query while creating it.
     *
     * @return void
     * @throws TypeErrorException
     */
    protected function thorwTypeErrorException()
    {
        throw new TypeErrorException("You can not execute query while creating it.");
    }

    /**
     * Transform query into a string.
     *
     * @param $query
     * @return string
     */
    protected function stringifyQuery($query)
    {
        return serialize($query);
    }

    /**
     * Check if user force repository to return a query builder.
     *
     * @return bool
     */
    protected function isBuilderRequired(): bool
    {
        if (empty($this->is_builder_required)) {
            $this->is_builder_required = false;
        }

        return $this->is_builder_required;
    }

    /**
     * Return query builder to end user.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function returnQueryBuilder()
    {
        return $this->getBuilder();
    }
}
