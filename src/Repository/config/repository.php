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
        'duration' => 1000,

        // use cache engine
        'engine' => config('cache.default')
    ]
];
