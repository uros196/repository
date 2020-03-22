<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cache Queries
    |--------------------------------------------------------------------------
    |
    |
    */

    'cache' => [
        // use or not caching by default
        'default' => false,

        // cache duration 1000 sec
        // acceptable types: DateTimeInterface, DateInterval orint
        'duration' => 1000,

        // use cache engine
        'engine' => config('cache.default')

        // force disable cache for all repositories globally
        // 'force_disable' => true
    ]
];
