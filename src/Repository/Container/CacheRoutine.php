<?php

namespace Repository\Container;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

trait CacheRoutine
{
    /**
     * @var string $cache_store_engine
     */
    protected $cache_store_engine;

    /**
     * @var bool $is_caching
     */
    protected $is_caching;

    /**
     * @var string $remember_type
     */
    protected $remember_type = 'remember';

    /**
     * @var \DateTimeInterface|\DateInterval|int $remember_time
     */
    protected $duration;



    /**
     * Set cache engine in fly.
     *
     * @param null|string $store_engine
     * @return $this
     */
    public function store(?string $store_engine = null)
    {
        if (!empty($store_engine)) {
            $this->cache_store_engine = $store_engine;
        }
        $this->is_caching = true;

        return $this;
    }

    /**
     * Remeber results from database.
     *
     * @param \DateTimeInterface|\DateInterval|int $duration set
     * @return $this
     */
    public function remember($duration = null)
    {
        if (!is_null($duration)) {
            $this->duration = $duration;
        }
        $this->remember_type = 'remember';
        $this->is_caching = true;

        return $this;
    }

    /**
     * Remember forever results from database.
     *
     * @return $this
     */
    public function rememberForever()
    {
        $this->remember_type = 'rememberForever';
        $this->is_caching = true;

        return $this;
    }

    /**
     * Use this function inside your service container when you need dynamic cache handler.
     *
     * @param bool $cache_state
     * @return $this
     */
    public function useCache(bool $cache_state)
    {
        $this->is_caching = $cache_state;
        return $this;
    }

    /**
     * Take results from cache or database with auto generated key.
     *
     * @param \Closure|string $callback
     * @return mixed
     */
    public function buildCache($callback)
    {
        return $this->execute($this->generateCacheKey($callback), $callback);
    }

    /**
     * Take results from cache or database with passed key.
     *
     * @param string $key
     * @param \Closure|string $callback
     * @return mixed
     */
    public function buildCacheWithKey(string $key, $callback)
    {
        return $this->execute($key, $callback);
    }

    /**
     * Execute all type of queries.
     *
     * @param string $key
     * @param \Closure|string $callback
     * @return array|mixed
     */
    protected function execute(string $key, $callback)
    {
        if ($callback instanceof \Closure) {
            return $this->executeClosureQuery($key, $callback);
        }

        return $this->executeStringQuery($key, $callback);
    }

    /**
     * Executing closure type query.
     *
     * @param string $key
     * @param \Closure $callback
     * @return mixed
     */
    protected function executeClosureQuery(string $key, \Closure $callback)
    {
        if ($this->isCaching()) {
            return $this->cacheContainer($key, $callback);
        }

        // if not using cache, just execute query
        return $callback();
    }

    /**
     * Executing string type query.
     *
     * @param string $key
     * @param string $query
     * @return array
     */
    protected function executeStringQuery(string $key, string $query)
    {
        if ($this->isCaching()) {
            return $this->cacheContainer($key, function () use ($query) {
                return DB::select($query);
            });
        }

        // if not using cache, just execute query
        return DB::select($query);
    }

    /**
     * Take cached results, since forever or pre-defined period.
     *
     * @param string $key
     * @param \Closure $callback
     * @return mixed
     */
    protected function cacheContainer(string $key, \Closure $callback)
    {
        $engine   = $this->getCacheEngine();
        $duration = $this->getDuration();
        $function = $this->getRememberType();

        switch ($function) {
            case 'remember':
                $arguments = [$key, $duration, $callback];
                break;

            case 'rememberForever':
                $arguments = [$key, $callback];
                break;

            default:
                $arguments = [];
        }

        return Cache::store($engine)->{$function}(...$arguments);
    }

    /**
     * Generate cache key based on query.
     *
     * @param \Closure|string $query
     * @return string
     */
    protected function generateCacheKey($query)
    {
        $app_key = config('app.key');

        if ($query instanceof \Closure) {
            $query = $query->toSql();
        }

        return "_".class_basename($this->getModel())."_".Hash::make($query . $app_key);
    }

    /**
     * Take cache duration.
     *
     * @return \DateTimeInterface|\DateInterval|int
     */
    protected function getDuration()
    {
        if (!empty($this->duration)) {
            return $this->duration;
        }

        return config('repository.cache.duration');
    }

    /**
     * Take cache engine.
     *
     * @return string
     */
    protected function getCacheEngine()
    {
        if (!empty($this->cache_store_engine)) {
            return $this->cache_store_engine;
        }

        return config('repository.cache.engine');
    }

    /**
     * Take cache remember type.
     *
     * @return string
     */
    protected function getRememberType()
    {
        return $this->remember_type;
    }

    /**
     * Take caching state, cache queries or not.
     *
     * @return bool
     */
    protected function isCaching()
    {
        if (!empty($this->is_caching)) {
            return $this->is_caching;
        }

        return config('repository.cache.default');
    }
}
