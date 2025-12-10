<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Image Driver
    |--------------------------------------------------------------------------
    |
    | Intervention Image supports "GD Library" and "Imagick" to process images
    | internally. You may choose one of them according to your PHP
    | configuration. By default PHP's "GD Library" implementation is used.
    |
    | Supported: "gd", "imagick"
    |
    */
    
    'driver' => env('IMAGE_DRIVER', 'gd'),
    
    /*
    |--------------------------------------------------------------------------
    | Image Quality
    |--------------------------------------------------------------------------
    |
    | This option controls the default image quality of all images that are
    | processed with Intervention Image. The value must be an integer
    | between 0 and 100. Default is set to 90.
    |
    */
    
    'quality' => 90,
    
    /*
    |--------------------------------------------------------------------------
    | Image Cache
    |--------------------------------------------------------------------------
    |
    | This option determines if Intervention Image should cache images. When
    | caching is enabled, Intervention Image stores processed images in the
    | cache directory and reuses them when the same image is requested again.
    |
    */
    
    'cache' => [
        'enabled' => false,
        'path' => storage_path('framework/cache/images'),
        'lifetime' => 43200, // 12 hours in minutes
    ],
];