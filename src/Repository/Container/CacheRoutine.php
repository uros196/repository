<?php

namespace Repository\Container;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

trait CacheRoutine
{
    /**
     * @var string $cache_store_engine
     */
    private $cache_store_engine;

    /**
     * @var bool $is_caching
     */
    private $is_caching;

    /**
     * @var string $remember_type
     */
    private $remember_type;

    /**
     * @var \DateTimeInterface|\DateInterval|int $remember_time
     */
    private $duration;



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
        $this->is_caching    = true;

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
        $this->is_caching    = true;

        return $this;
    }

    /**
     * Use this function inside your service container when you need dynamic cache handler.
     *
     * @param string|null $remember_type
     * @param bool $cache_state
     * @return $this
     */
    public function useCache(bool $cache_state, ?string $remember_type = null)
    {
        if ($cache_state) {
            return $this->{$remember_type ?? 'remember'}();
        }

        $this->is_caching = $cache_state;
        return $this;
    }

    /**
     * Take results from cache or database with auto generated key.
     *
     * @param \Closure $callback
     * @return mixed
     */
    protected function buildCache(\Closure $callback)
    {
        $key = $this->stringifyQuery($callback);
        return $this->execute($this->generateCacheKey($key), $callback);
    }

    /**
     * Take results from cache or database with passed key.
     *
     * @param string $key
     * @param \Closure $callback
     * @return mixed
     */
    protected function buildCacheWithKey(string $key, \Closure $callback)
    {
        return $this->execute($key, $callback);
    }

    /**
     * Take cached results, since forever or pre-defined period.
     *
     * @param string $key
     * @param \Closure $callback
     * @return array|mixed
     */
    private function execute(string $key, \Closure $callback)
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
     * @param string $key
     * @param \Illuminate\Contracts\Support\Arrayable|array|string $tags
     * @return string
     */
    protected function generateCacheKey(string $key = null, $tags = '')
    {
        $app_key = config('app.key');
        $key     = !empty($key) ?: $this->getSql();

        return "_" . class_basename($this->getModel()) . "_"
                . md5($key . $app_key)
                . $this->extractTags($tags);
    }

    /**
     * @param \Illuminate\Contracts\Support\Arrayable|array|string $tags
     * @return string
     */
    protected function extractTags($tags)
    {
        $tags = implode("_", Arr::wrap($tags));

        return !empty($tags) ? "_{$tags}_" : '';
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

        return config('db-repository.cache.duration');
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

        return config('db-repository.cache.engine');
    }

    /**
     * Take cache remember type.
     *
     * @return string
     * @throws \Exception
     */
    protected function getRememberType()
    {
        $possible_types = [
            'remember',
            'rememberForever'
        ];

        if (!in_array($this->remember_type, $possible_types)) {
            throw new \Exception("You try to use wrong type of caching.");
        }

        return $this->remember_type;
    }

    /**
     * Take caching state, cache queries or not.
     *
     * @return bool
     */
    protected function idCacheActivated()
    {
        $force_disable = (bool)config('db-repository.cache.force_disable');

        if (!empty($this->is_caching)) {
            return $this->is_caching && !$force_disable;
        }

        return (bool)config('db-repository.cache.default') && !$force_disable;
    }
}
