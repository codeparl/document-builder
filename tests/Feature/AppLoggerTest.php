<?php

use SchoolPalm\AppLogger\Models\AppLog;
use SchoolPalm\AppLogger\Facades\AppLogger;


it('logs document generation', function () {

    AppLogger::info(
        'Document generation started'
    );


    expect(
        AppLog::count()
    )->toBe(1);


    expect(
        AppLog::first()->message
    )->toBe(
        'Document generation started'
    );
});
