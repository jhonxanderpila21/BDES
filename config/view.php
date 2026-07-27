<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default View Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the default engine used to render views.
    |
    */

    'default' => env('VIEW_DRIVER', 'php'),

    /*
    |--------------------------------------------------------------------------
    | View Compiled Path
    |--------------------------------------------------------------------------
    |
    | This directory is used to store compiled views.
    |
    */

    'compiled' => env('VIEW_COMPILED_PATH', storage_path('framework/views')),

    /*
    |--------------------------------------------------------------------------
    | View Paths
    |--------------------------------------------------------------------------
    |
    | Paths where Laravel will look for views.
    |
    */

    'paths' => [
        resource_path('views'),
    ],

];

