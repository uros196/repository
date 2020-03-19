<?php
/**
 * Created by PhpStorm.
 * User: User-x
 * Date: 3/19/2020
 * Time: 12:21 AM
 */

namespace Repository\Facades;

use Illuminate\Support\Facades\Facade;

class RepositoryFacade extends Facade
{
    /**
     * Handle dynamic, static calls to the object.
     *
     * @param  string  $method
     * @param  array  $args
     * @return mixed
     *
     * @throws \RuntimeException
     */
    public static function __callStatic($method, $args)
    {
        $instance = static::useFreshInstance();

        if (! $instance) {
            throw new \RuntimeException('A facade root has not been set.');
        }

        return $instance->$method(...$args);
    }

    /**
     * Resolve a new instance for the facade
     *
     * @return mixed
     */
    protected static function useFreshInstance()
    {
        static::clearResolvedInstance(static::getFacadeAccessor());

        return static::getFacadeRoot();
    }
}
