<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Automatic Queue Threshold
    |--------------------------------------------------------------------------
    |
    | When a source contains more records than this amount,
    | DocumentBuilder automatically moves execution to queues.
    |
    */


    'storage' => [

        'disk' => 'local',

        'root' => 'document-builder',

    ],


    'queue' => [

        'connection' => null,
        'queue' => null,
        'enabled' => true,
        'threshold' => 500,



    ],


    'drivers' => [

        'pdf' => [
            'engine' => 'mpdf',
            'extension' => 'pdf',
        ],


        'xlsx' => [
            'engine' => 'phpspreadsheet',
            'extension' => 'xlsx',
        ],


        'image' => [
            'engine' => 'intervention',
            'extensions' => [
                'jpg',
                'jpeg',
                'png',
                'webp',
                'gif',
                'avif',
                'bmp',
                'tiff',
            ],
            'extension' => 'png',
        ],

    ],
    'image' => [

        /*
    |--------------------------------------------------------------------------
    | Preferred image driver
    |--------------------------------------------------------------------------
    |
    | Available:
    |
    | imagick
    | gd
    |
    */

        'driver' => env(
            'DOCUMENT_IMAGE_DRIVER',
            'imagick'
        ),

    ],
];
