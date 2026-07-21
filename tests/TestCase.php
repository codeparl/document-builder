<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Tests;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use Orchestra\Testbench\TestCase as Orchestra;
use SchoolPalm\AppLogger\AppLoggerServiceProvider;
use UnnovateBrains\DocumentBuilder\DocumentBuilderServiceProvider;
use Illuminate\Support\Facades\Artisan;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            DocumentBuilderServiceProvider::class,
            AppLoggerServiceProvider::class
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:' . base64_encode(random_bytes(32)));

        $app['config']->set('filesystems.default', 'local');

        $app['config']->set('queue.default', 'sync');

        $app['config']->set(
            'document-builder.storage.root',
            __DIR__ . '/storage'
        );

        // Add your package's view directory to the view locations
        View::addLocation(__DIR__ . '/../workbench/resources/views');
        Config::set(
            'filesystems.default',
            'local'
        );

        Config::set(
            'filesystems.disks.local.root',
            __DIR__ . '/../workbench/storage/app'
        );



        $app['config']->set(
            'app-logger.driver',
            'file'
        );


        Config::set(
            'filesystems.disks.app-logger',
            [
                'driver' => 'local',
                'root' => __DIR__ . '/../workbench/storage/logs',
            ]
        );


        Config::set(
            'filesystems.default',
            'app-logger'
        );


        $app['config']->set(
            'app-logger.database_connection',
            null
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(
            base_path(
                'vendor/schoolpalm/app-logger/database/migrations'
            )
        );

        Artisan::call('migrate');
    }
}
