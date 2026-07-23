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
        ],
        'xlsx' => [
            'engine' => 'phpspreadsheet',
        ],

        'csv' => [
            'engine' => 'native',
        ],

        'docx' => [
            'engine' => 'phpword',
        ],

    ],

];
