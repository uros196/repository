<?php

namespace Repository\Container;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

trait CacheRoutine
{
    protected $cache_store_engine;

    protected $is_caching;

    /**
     * @param bool $is_caching
     * @return \Repository\ServiceRepository|static
     */
    public function useCache(bool $is_caching = true)
    {
        $this->is_caching = $is_caching;
        return $this;
    }

    /**
     * @param null|string $store_engine
     * @return \Repository\ServiceRepository|static
     */
    public function store(?string $store_engine = null)
    {
        if (empty($store_engine)) {
            // TODO: procitati iz konfiguracije
            $store_engine = 'redis';
        }

        $this->cache_store_engine = $store_engine;
        return $this;
    }

    /**
     * @param \Closure|string $callback
     * @return mixed
     */
    public function cacheBuild($callback)
    {
        $key = $this->buildCacheKey($callback);

        if ($callback instanceof \Closure) {
            return Cache::remember($key, 1000, $callback);
        }
    }

    /**
     * @param \Closure|string $query
     * @return string
     */
    protected function buildCacheKey($query)
    {
        $app_key = config('app.key');

        if ($query instanceof \Closure) {

        }

        return Hash::make($query . $app_key);
    }
}
